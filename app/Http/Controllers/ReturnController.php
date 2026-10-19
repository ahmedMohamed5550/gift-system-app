<?php

namespace App\Http\Controllers;

use App\Http\Requests\CancelReturnRequest;
use App\Http\Requests\StoreReturnRequest;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\Shift;
use App\Models\User;
use App\Services\ReturnService;
use App\Services\ShiftService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ReturnController extends Controller
{
    public function __construct(
        protected ReturnService $returnService,
        protected ShiftService $shiftService
    ) {}

    /**
     * Display a list of sales returns with search, filters, and KPIs.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $query = SaleReturn::query()->with(['sale', 'user', 'shift', 'items.product']);

        // Non-owner employees only see their own returns
        if (! $user->isOwner()) {
            $query->where('user_id', $user->id);
        }

        // Search by return number, original invoice number, or reason
        if ($search = trim($request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('return_number', 'like', "%{$search}%")
                  ->orWhere('reason', 'like', "%{$search}%")
                  ->orWhereHas('sale', fn($sq) => $sq->where('invoice_number', 'like', "%{$search}%"));
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($refundMethod = $request->input('refund_method')) {
            $query->where('refund_method', $refundMethod);
        }

        if ($shiftId = $request->input('shift_id')) {
            $query->where('shift_id', $shiftId);
        }

        // Filter by responsible cashier (only owners can filter by other cashiers)
        if ($user->isOwner() && $userId = $request->input('user_id')) {
            $query->where('user_id', $userId);
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $returns = $query->latest('id')->paginate(15)->withQueryString();

        // Metrics for KPI cards (scoped to user if employee, store-wide if owner)
        $metrics = $this->returnService->getReturnMetrics($user);

        $shifts = $user->isOwner()
            ? Shift::latest('id')->limit(30)->get(['id', 'opened_at', 'user_id'])
            : Shift::where('user_id', $user->id)->latest('id')->limit(30)->get(['id', 'opened_at', 'user_id']);

        $users = $user->isOwner()
            ? User::orderBy('name')->get(['id', 'name'])
            : collect([$user]);

        return view('returns.index', compact('returns', 'metrics', 'shifts', 'users'));
    }

    /**
     * Show form to search invoice and prepare a return.
     */
    public function create(Request $request): View
    {
        $activeShift = $this->shiftService->getActiveShift(Auth::user());
        $sale = null;
        $searchQuery = trim($request->input('invoice', $request->input('search', '')));

        if (!empty($searchQuery)) {
            try {
                $sale = $this->returnService->getInvoiceForReturn($searchQuery);
            } catch (ValidationException $e) {
                session()->flash('warning', $e->validator->errors()->first());
            }
        }

        return view('returns.create', compact('activeShift', 'sale', 'searchQuery'));
    }

    /**
     * AJAX endpoint to search invoice and retrieve remaining returnable quantities.
     */
    public function searchInvoice(Request $request): JsonResponse
    {
        $search = trim($request->input('search', ''));

        if (empty($search)) {
            return response()->json([
                'success' => false,
                'message' => 'يرجى إدخال رقم الفاتورة للبحث.',
            ], 422);
        }

        try {
            $sale = $this->returnService->getInvoiceForReturn($search);

            $itemsData = $sale->items->map(function ($item) {
                return [
                    'sale_item_id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product?->name ?? 'منتج غير معروف',
                    'product_code' => '#' . $item->product_id,
                    'unit_price' => (float) $item->unit_price,
                    'original_quantity' => (float) $item->quantity,
                    'returned_quantity' => (float) $item->returned_quantity_calculated,
                    'returnable_quantity' => (float) $item->returnable_quantity_calculated,
                ];
            });

            return response()->json([
                'success' => true,
                'sale' => [
                    'id' => $sale->id,
                    'invoice_number' => $sale->invoice_number,
                    'created_at' => $sale->created_at->format('Y-m-d g:i A'),
                    'cashier' => $sale->user?->name ?? '-',
                    'payment_method' => $sale->payment_method,
                    'payment_method_label' => $sale->payment_method_label,
                    'total' => (float) $sale->total,
                    'total_returnable_items' => (float) $sale->total_returnable_items,
                    'items' => $itemsData,
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->validator->errors()->first(),
            ], 422);
        }
    }

    /**
     * Store a newly created return in storage.
     */
    public function store(StoreReturnRequest $request): RedirectResponse
    {
        $saleReturn = $this->returnService->createReturn(
            data: $request->validated(),
            user: Auth::user()
        );

        return redirect()
            ->route('returns.show', $saleReturn)
            ->with('success', "تم إنشاء إشعار المرتجع رقم #{$saleReturn->return_number} بقيمة {$saleReturn->total_amount} جنيه مصري وإعادة الأصناف للمخزون بنجاح.");
    }

    /**
     * Display return details, items, and audit log.
     */
    public function show(SaleReturn $return): View
    {
        abort_if(! Auth::user()->isOwner() && $return->user_id !== Auth::id(), 403, 'عذراً، لا تملك الصلاحية للوصول إلى هذا المرتجع.');

        $return->load([
            'sale.user',
            'user',
            'shift.user',
            'cancelledByUser',
            'items.product.subcategory',
            'items.saleItem',
            'activityLogs.user',
        ]);

        return view('returns.show', compact('return'));
    }

    /**
     * Printable return receipt view.
     */
    public function print(SaleReturn $return): View
    {
        abort_if(! Auth::user()->isOwner() && $return->user_id !== Auth::id(), 403, 'عذراً، لا تملك الصلاحية لطباعة هذا المرتجع.');

        $return->load([
            'sale.user',
            'user',
            'shift.user',
            'items.product',
        ]);

        return view('returns.print', compact('return'));
    }

    /**
     * Safely cancel an approved return with inventory reversal.
     */
    public function cancel(CancelReturnRequest $request, SaleReturn $return): RedirectResponse
    {
        abort_if(! Auth::user()->isOwner() && $return->user_id !== Auth::id(), 403, 'عذراً، لا تملك الصلاحية لإلغاء هذا المرتجع.');

        $this->returnService->cancelReturn(
            return: $return,
            user: Auth::user(),
            reason: $request->input('reason')
        );

        return redirect()
            ->route('returns.show', $return)
            ->with('success', "تم إلغاء إشعار المرتجع رقم #{$return->return_number} وخصم الأصناف من المخزون بنجاح.");
    }
}

