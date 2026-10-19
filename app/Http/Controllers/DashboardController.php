<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    /**
     * Display the POS Dashboard with real-time analytics, KPIs, and charts.
     */
    public function index(Request $request): View
    {
        $period = $request->input('period', 'today');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $dateRange = $this->reportService->parseDateRange($period, $dateFrom, $dateTo);
        $dashboardData = $this->reportService->getDashboardData($dateRange, Auth::user());

        return view('index', [
            'data' => $dashboardData,
            'dateRange' => $dateRange,
            'currentPeriod' => $dateRange['period'],
        ]);
    }
}

