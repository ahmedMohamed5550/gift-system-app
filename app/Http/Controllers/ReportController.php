<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    /**
     * Reports Overview Hub.
     */
    public function index(Request $request): View
    {
        $period = $request->input('period', 'this_month');
        $dateRange = $this->reportService->parseDateRange($period, $request->input('date_from'), $request->input('date_to'));
        $dashboardData = $this->reportService->getDashboardData($dateRange);

        return view('reports.index', [
            'data' => $dashboardData,
            'dateRange' => $dateRange,
            'period' => $dateRange['period'],
        ]);
    }

    /**
     * Sales Report with date filters, breakdown, and paginated transactions.
     */
    public function sales(Request $request): View
    {
        $period = $request->input('period', 'this_month');
        $dateRange = $this->reportService->parseDateRange($period, $request->input('date_from'), $request->input('date_to'));

        $filters = [
            'payment_method' => $request->input('payment_method'),
            'user_id' => $request->input('user_id'),
            'shift_id' => $request->input('shift_id'),
            'status' => $request->input('status', 'completed'),
        ];

        $reportData = $this->reportService->getSalesReport($dateRange, $filters);

        return view('reports.sales', array_merge($reportData, [
            'dateRange' => $dateRange,
            'period' => $dateRange['period'],
            'filters' => $filters,
        ]));
    }

    /**
     * Sales & Refunds by Payment Method Report.
     */
    public function paymentMethods(Request $request): View
    {
        $period = $request->input('period', 'this_month');
        $dateRange = $this->reportService->parseDateRange($period, $request->input('date_from'), $request->input('date_to'));

        $reportData = $this->reportService->getPaymentMethodsReport($dateRange);

        return view('reports.payment-methods', array_merge($reportData, [
            'dateRange' => $dateRange,
            'period' => $dateRange['period'],
        ]));
    }

    /**
     * Product Sales Report (quantities, revenue, cost, profit).
     */
    public function products(Request $request): View
    {
        $period = $request->input('period', 'this_month');
        $dateRange = $this->reportService->parseDateRange($period, $request->input('date_from'), $request->input('date_to'));

        $filters = [
            'category_id' => $request->input('category_id'),
            'subcategory_id' => $request->input('subcategory_id'),
            'search' => $request->input('search'),
        ];

        $reportData = $this->reportService->getProductSalesReport($dateRange, $filters);

        return view('reports.products', array_merge($reportData, [
            'dateRange' => $dateRange,
            'period' => $dateRange['period'],
            'filters' => $filters,
        ]));
    }

    /**
     * Employee / Cashier Sales Performance Report.
     */
    public function employees(Request $request): View
    {
        $period = $request->input('period', 'this_month');
        $dateRange = $this->reportService->parseDateRange($period, $request->input('date_from'), $request->input('date_to'));

        $reportData = $this->reportService->getEmployeeSalesReport($dateRange);

        return view('reports.employees', array_merge($reportData, [
            'dateRange' => $dateRange,
            'period' => $dateRange['period'],
        ]));
    }

    /**
     * Shift & Cash Reconciliation Report.
     */
    public function shifts(Request $request): View
    {
        $period = $request->input('period', 'this_month');
        $dateRange = $this->reportService->parseDateRange($period, $request->input('date_from'), $request->input('date_to'));

        $filters = [
            'user_id' => $request->input('user_id'),
            'status' => $request->input('status'),
        ];

        $reportData = $this->reportService->getShiftReport($dateRange, $filters);

        return view('reports.shifts', array_merge($reportData, [
            'dateRange' => $dateRange,
            'period' => $dateRange['period'],
            'filters' => $filters,
        ]));
    }

    /**
     * Expense Report by Category and Period.
     */
    public function expenses(Request $request): View
    {
        $period = $request->input('period', 'this_month');
        $dateRange = $this->reportService->parseDateRange($period, $request->input('date_from'), $request->input('date_to'));

        $filters = [
            'category' => $request->input('category'),
            'payment_method' => $request->input('payment_method'),
            'user_id' => $request->input('user_id'),
            'status' => $request->input('status', 'active'),
        ];

        $reportData = $this->reportService->getExpenseReport($dateRange, $filters);

        return view('reports.expenses', array_merge($reportData, [
            'dateRange' => $dateRange,
            'period' => $dateRange['period'],
            'filters' => $filters,
        ]));
    }

    /**
     * Inventory & Stock Movements Report.
     */
    public function inventory(Request $request): View
    {
        $period = $request->input('period', 'this_month');
        $dateRange = $this->reportService->parseDateRange($period, $request->input('date_from'), $request->input('date_to'));

        $filters = [
            'type' => $request->input('type'),
            'reference_type' => $request->input('reference_type'),
            'product_id' => $request->input('product_id'),
            'user_id' => $request->input('user_id'),
        ];

        $reportData = $this->reportService->getInventoryReport($dateRange, $filters);

        return view('reports.inventory', array_merge($reportData, [
            'dateRange' => $dateRange,
            'period' => $dateRange['period'],
            'filters' => $filters,
        ]));
    }

    /**
     * Low-Stock & Out-of-Stock Report.
     */
    public function lowStock(Request $request): View
    {
        $filters = [
            'status' => $request->input('status'),
            'category_id' => $request->input('category_id'),
            'subcategory_id' => $request->input('subcategory_id'),
            'search' => $request->input('search'),
        ];

        $reportData = $this->reportService->getLowStockReport($filters);

        return view('reports.low-stock', array_merge($reportData, [
            'filters' => $filters,
        ]));
    }

    /**
     * Comprehensive Profit & Loss Report.
     */
    public function profit(Request $request): View
    {
        $period = $request->input('period', 'this_month');
        $dateRange = $this->reportService->parseDateRange($period, $request->input('date_from'), $request->input('date_to'));

        $reportData = $this->reportService->getProfitReport($dateRange);

        return view('reports.profit', array_merge($reportData, [
            'dateRange' => $dateRange,
            'period' => $dateRange['period'],
        ]));
    }
}

