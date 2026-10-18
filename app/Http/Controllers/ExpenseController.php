<?php

namespace App\Http\Controllers;

use App\Http\Requests\CancelExpenseRequest;
use App\Http\Requests\ExpenseRequest;
use App\Models\Expense;
use App\Models\Shift;
use App\Models\User;
use App\Services\ExpenseService;
use App\Services\ShiftService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    protected ExpenseService $expenseService;
    protected ShiftService $shiftService;

    public function __construct(ExpenseService $expenseService, ShiftService $shiftService)
    {
        $this->expenseService = $expenseService;
        $this->shiftService = $shiftService;
    }

    /**
     * Display list of all business expenses with status and payment method filters.
     */
    public function index(Request $request): View
    {
        $query = Expense::with(['user', 'shift', 'cancelledBy']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('category', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($paymentMethod = $request->input('payment_method')) {
            $query->where('payment_method', $paymentMethod);
        }

        if ($shiftId = $request->input('shift_id')) {
            $query->where('shift_id', $shiftId);
        }

        if ($userId = $request->input('user_id')) {
            $query->where('user_id', $userId);
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $expenses = $query->latest('id')->paginate(15)->withQueryString();

        $stats = [
            'total_active' => (float) (Expense::active()->sum('amount') ?? 0),
            'cash_active' => (float) (Expense::active()->cash()->sum('amount') ?? 0),
            'non_cash_active' => (float) (Expense::active()->where('payment_method', '!=', 'cash')->sum('amount') ?? 0),
            'cancelled_total' => (float) (Expense::cancelled()->sum('amount') ?? 0),
            'active_count' => Expense::active()->count(),
            'cancelled_count' => Expense::cancelled()->count(),
        ];

        $categories = $this->expenseService->getCategories();
        $users = User::orderBy('name')->get(['id', 'name']);
        $activeShift = $this->shiftService->getActiveShift(Auth::user());

        return view('expenses.index', compact('expenses', 'stats', 'categories', 'users', 'activeShift'));
    }

    /**
     * Show the expense creation form.
     */
    public function create(): View
    {
        $categories = $this->expenseService->getCategories();
        $activeShift = $this->shiftService->getActiveShift(Auth::user());
        $openShifts = Shift::where('status', 'open')->with('user')->get();

        return view('expenses.create', compact('categories', 'activeShift', 'openShifts'));
    }

    /**
     * Store a newly created expense.
     */
    public function store(ExpenseRequest $request): RedirectResponse
    {
        $shiftId = $request->filled('shift_id') ? (int) $request->input('shift_id') : null;

        $this->expenseService->recordExpense(
            userId: Auth::id(),
            category: $request->input('category'),
            amount: (float) $request->input('amount'),
            paymentMethod: $request->input('payment_method'),
            description: $request->input('description'),
            shiftId: $shiftId
        );

        return redirect()->route('expenses.index')
                         ->with('success', 'تم تسجيل المصروف بنجاح وإدراجه في السجلات المالية.');
    }

    /**
     * Display details of a single expense.
     */
    public function show(Expense $expense): View
    {
        $expense->load(['user', 'shift.user', 'cancelledBy']);

        return view('expenses.show', compact('expense'));
    }

    /**
     * Show the expense edit form.
     */
    public function edit(Expense $expense): View|RedirectResponse
    {
        if ($expense->is_cancelled) {
            return redirect()->route('expenses.index')
                             ->with('error', 'لا يمكن تعديل مصروف تم إلغاؤه مسبقاً.');
        }

        $categories = $this->expenseService->getCategories();
        $openShifts = Shift::where('status', 'open')->with('user')->get();

        return view('expenses.edit', compact('expense', 'categories', 'openShifts'));
    }

    /**
     * Update an active expense.
     */
    public function update(ExpenseRequest $request, Expense $expense): RedirectResponse
    {
        $this->expenseService->updateExpense($expense, [
            'category' => $request->input('category'),
            'amount' => (float) $request->input('amount'),
            'payment_method' => $request->input('payment_method'),
            'shift_id' => $request->input('shift_id'),
            'description' => $request->input('description'),
        ]);

        return redirect()->route('expenses.index')
                         ->with('success', 'تم تحديث بيانات المصروف بنجاح.');
    }

    /**
     * Cancel an expense while keeping its financial audit trail.
     */
    public function cancel(CancelExpenseRequest $request, Expense $expense): RedirectResponse
    {
        $this->expenseService->cancelExpense(
            expense: $expense,
            reason: $request->input('reason'),
            cancelledByUserId: Auth::id()
        );

        return redirect()->route('expenses.index')
                         ->with('success', 'تم إلغاء المصروف وتوثيق سبب الإلغاء في السجل المالي بنجاح.');
    }
}

