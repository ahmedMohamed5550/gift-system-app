<?php

namespace App\Http\Controllers;

use App\Http\Requests\CloseShiftRequest;
use App\Http\Requests\OpenShiftRequest;
use App\Models\Shift;
use App\Models\User;
use App\Services\ShiftService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ShiftController extends Controller
{
    protected ShiftService $shiftService;

    public function __construct(ShiftService $shiftService)
    {
        $this->shiftService = $shiftService;
    }

    /**
     * Display list of all shifts and financial reconciliation records.
     */
    public function index(Request $request): View
    {
        $query = Shift::with('user');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($userId = $request->input('user_id')) {
            $query->where('user_id', $userId);
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('opened_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('opened_at', '<=', $dateTo);
        }

        $shifts = $query->latest('opened_at')->paginate(15)->withQueryString();

        $stats = [
            'total_shifts' => Shift::count(),
            'open_shifts' => Shift::where('status', 'open')->count(),
            'closed_shifts' => Shift::where('status', 'closed')->count(),
            'total_cash_collected' => (float) (Shift::where('status', 'closed')->sum('closing_cash') ?? 0),
            'total_difference' => (float) (Shift::where('status', 'closed')->sum('difference') ?? 0),
        ];

        $users = User::orderBy('name')->get(['id', 'name']);
        $activeShift = $this->shiftService->getActiveShift(Auth::user());

        return view('shifts.index', compact('shifts', 'stats', 'users', 'activeShift'));
    }

    /**
     * Display the current active shift for the authenticated user, or the shift opening prompt.
     */
    public function current(): View
    {
        $shift = $this->shiftService->getActiveShift(Auth::user());

        if (!$shift) {
            return view('shifts.current', [
                'shift' => null,
                'summary' => null,
            ]);
        }

        $shift->load([
            'sales' => fn($q) => $q->latest()->limit(10),
            'returns' => fn($q) => $q->latest()->limit(10),
            'expenses' => fn($q) => $q->latest()->limit(10),
        ]);

        $summary = $this->shiftService->getShiftFinancialSummary($shift);

        return view('shifts.current', compact('shift', 'summary'));
    }

    /**
     * Display complete details and cash reconciliation for a specific shift.
     */
    public function show(Shift $shift): View
    {
        $shift->load([
            'user',
            'sales' => fn($q) => $q->latest(),
            'returns' => fn($q) => $q->latest(),
            'expenses' => fn($q) => $q->latest(),
        ]);

        $summary = $this->shiftService->getShiftFinancialSummary($shift);

        return view('shifts.show', compact('shift', 'summary'));
    }

    /**
     * Open a new shift for the authenticated cashier.
     */
    public function open(OpenShiftRequest $request): RedirectResponse
    {
        $shift = $this->shiftService->openShift(
            user: Auth::user(),
            openingCash: (float) $request->input('opening_cash'),
            notes: $request->input('notes')
        );

        return redirect()->route('shifts.current')
                         ->with('success', 'تم فتح وردية العمل رقم #' . $shift->id . ' بنجاح وتسجيل العهدة النقدية.');
    }

    /**
     * Close an active shift and perform cash reconciliation.
     */
    public function close(CloseShiftRequest $request, Shift $shift): RedirectResponse
    {
        $closedShift = $this->shiftService->closeShift(
            shift: $shift,
            actualClosingCash: (float) $request->input('closing_cash'),
            notes: $request->input('notes')
        );

        $diffMsg = '';
        if ($closedShift->difference > 0) {
            $diffMsg = ' (يوجد فائض بالدرج: +' . number_format($closedShift->difference, 2) . ' ج.م)';
        } elseif ($closedShift->difference < 0) {
            $diffMsg = ' (يوجد عجز بالدرج: ' . number_format($closedShift->difference, 2) . ' ج.م)';
        } else {
            $diffMsg = ' (الدرج مطابق تماماً)';
        }

        return redirect()->route('shifts.show', $closedShift)
                         ->with('success', 'تم إغلاق الوردية رقم #' . $closedShift->id . ' وتسوية الخزينة بنجاح' . $diffMsg . '.');
    }

    /**
     * Printable thermal / A4 receipt report for shift closure.
     */
    public function print(Shift $shift): View
    {
        $summary = $this->shiftService->getShiftFinancialSummary($shift);

        return view('shifts.print', compact('shift', 'summary'));
    }
}

