<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Shift;
use App\Models\User;
use App\Services\PosService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SaleController extends Controller
{
    public function __construct(
        protected PosService $posService
    ) {}

    /**
     * Display the sales invoices list with filters and financial KPIs.
     */
    public function index(Request $request): View
    {
        $query = Sale::query()->with(['user', 'shift', 'items.product']);

        // Search by invoice number or customer/notes
        if ($search = trim($request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($uq) => $uq->where('name', 'like', "%{$search}%"));
            });
        }

        // Filter by payment method
        if ($paymentMethod = $request->input('payment_method')) {
            $query->where('payment_method', $paymentMethod);
        }

        // Filter by status
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Filter by shift
        if ($shiftId = $request->input('shift_id')) {
            $query->where('shift_id', $shiftId);
        }

        // Filter by cashier / user
        if ($userId = $request->input('user_id')) {
            $query->where('user_id', $userId);
        }

        // Date range filters
        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        // Paginated result
        $sales = $query->latest('id')->paginate(15)->withQueryString();

        // Financial KPIs (based on filtered/all completed sales)
        $stats = [
            'total_sales_amount' => (float) Sale::where('status', 'completed')->sum('total'),
            'completed_count' => Sale::where('status', 'completed')->count(),
            'cash_sales_amount' => (float) Sale::where('status', 'completed')->where('payment_method', 'cash')->sum('total'),
            'electronic_sales_amount' => (float) Sale::where('status', 'completed')->whereIn('payment_method', ['vodafone_cash', 'instapay'])->sum('total'),
            'cancelled_count' => Sale::where('status', 'cancelled')->count(),
            'cancelled_amount' => (float) Sale::where('status', 'cancelled')->sum('total'),
        ];

        $shifts = Shift::latest('id')->limit(30)->get(['id', 'opened_at', 'user_id']);
        $users = User::orderBy('name')->get(['id', 'name']);

        return view('sales.index', compact('sales', 'stats', 'shifts', 'users'));
    }

    /**
     * Display invoice details with line items and audit log.
     */
    public function show(Sale $sale): View
    {
        $sale->load([
            'user',
            'shift.user',
            'items.product.subcategory',
            'activityLogs.user',
        ]);

        return view('sales.show', compact('sale'));
    }

    /**
     * Display printable receipt view (clean layout for thermal / A4 printing).
     */
    public function print(Sale $sale): View
    {
        $sale->load([
            'user',
            'shift.user',
            'items.product',
        ]);

        return view('sales.print', compact('sale'));
    }

    /**
     * Safely cancel a sale and reverse stock back into inventory.
     */
    public function cancel(Request $request, Sale $sale): RedirectResponse
    {
        $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $this->posService->cancelSale(
            sale: $sale,
            user: Auth::user(),
            reason: $request->input('reason')
        );

        return redirect()
            ->route('sales.show', $sale)
            ->with('success', "تم إلغاء الفاتورة رقم #{$sale->invoice_number} وإعادة المنتجات إلى المخزون بنجاح.");
    }
}

