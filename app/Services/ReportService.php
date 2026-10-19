<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Expense;
use App\Models\Product;
use App\Models\ReturnItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use App\Models\Shift;
use App\Models\StockMovement;
use App\Models\Subcategory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function __construct(
        protected ShiftService $shiftService
    ) {}

    /**
     * Parse and standardize date range filters.
     * Supports period presets (today, yesterday, this_week, this_month, last_month, this_year, custom).
     */
    public function parseDateRange(?string $period = 'this_month', ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $now = Carbon::now();

        // If custom dates are explicitly provided without preset period
        if ($dateFrom || $dateTo) {
            $period = 'custom';
        }

        switch ($period) {
            case 'today':
                $start = $now->copy()->startOfDay();
                $end = $now->copy()->endOfDay();
                $label = 'اليوم (' . $start->format('Y-m-d') . ')';
                break;

            case 'yesterday':
                $start = $now->copy()->subDay()->startOfDay();
                $end = $now->copy()->subDay()->endOfDay();
                $label = 'أمس (' . $start->format('Y-m-d') . ')';
                break;

            case 'this_week':
                $start = $now->copy()->startOfWeek(Carbon::SATURDAY); // Week starts Saturday in Egypt
                $end = $now->copy()->endOfWeek(Carbon::FRIDAY)->endOfDay();
                $label = 'الأسبوع الحالي (' . $start->format('Y-m-d') . ' إلى ' . $end->format('Y-m-d') . ')';
                break;

            case 'last_month':
                $start = $now->copy()->subMonth()->startOfMonth();
                $end = $now->copy()->subMonth()->endOfMonth()->endOfDay();
                $label = 'الشهر الماضي (' . $start->format('Y-m-d') . ' إلى ' . $end->format('Y-m-d') . ')';
                break;

            case 'this_year':
                $start = $now->copy()->startOfYear();
                $end = $now->copy()->endOfYear()->endOfDay();
                $label = 'العام الحالي (' . $start->format('Y') . ')';
                break;

            case 'custom':
                try {
                    $start = $dateFrom ? Carbon::parse($dateFrom)->startOfDay() : $now->copy()->startOfMonth();
                } catch (\Exception $e) {
                    $start = $now->copy()->startOfMonth();
                }

                try {
                    $end = $dateTo ? Carbon::parse($dateTo)->endOfDay() : $now->copy()->endOfDay();
                } catch (\Exception $e) {
                    $end = $now->copy()->endOfDay();
                }

                // If user entered start date later than end date, swap them
                if ($start->gt($end)) {
                    $temp = $start;
                    $start = $end->copy()->startOfDay();
                    $end = $temp->copy()->endOfDay();
                }

                $label = 'فترة مخصصة (' . $start->format('Y-m-d') . ' إلى ' . $end->format('Y-m-d') . ')';
                break;

            case 'this_month':
            default:
                $period = 'this_month';
                $start = $now->copy()->startOfMonth();
                $end = $now->copy()->endOfMonth()->endOfDay();
                $label = 'الشهر الحالي (' . $start->format('Y-m-d') . ' إلى ' . $end->format('Y-m-d') . ')';
                break;
        }

        return [
            'period' => $period,
            'start' => $start,
            'end' => $end,
            'date_from' => $start->format('Y-m-d'),
            'date_to' => $end->format('Y-m-d'),
            'label' => $label,
        ];
    }

    /**
     * Get aggregated Dashboard data for today and the selected period.
     */
    public function getDashboardData(array $dateRange, ?User $user = null): array
    {
        $todayStart = Carbon::today()->startOfDay();
        $todayEnd = Carbon::today()->endOfDay();

        // 1. Today Quick Metrics
        $todaySales = Sale::where('status', 'completed')
            ->whereBetween('created_at', [$todayStart, $todayEnd])
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(total), 0) as gross, COALESCE(SUM(discount), 0) as discounts')
            ->first();

        $todayReturns = SaleReturn::where('status', 'approved')
            ->whereBetween('created_at', [$todayStart, $todayEnd])
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(total_amount), 0) as total')
            ->first();

        $todayExpenses = Expense::where('status', 'active')
            ->whereBetween('created_at', [$todayStart, $todayEnd])
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(amount), 0) as total')
            ->first();

        $todayNetSales = (float) $todaySales->gross - (float) $todayReturns->total;

        // 2. Selected Period Financial Totals (Filtered)
        $start = $dateRange['start'];
        $end = $dateRange['end'];

        $salesStats = Sale::where('status', 'completed')
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('
                COUNT(*) as count,
                COALESCE(SUM(subtotal), 0) as gross_subtotal,
                COALESCE(SUM(discount), 0) as total_discount,
                COALESCE(SUM(total), 0) as gross_sales,
                COALESCE(SUM(CASE WHEN payment_method = "cash" THEN total ELSE 0 END), 0) as cash_sales,
                COALESCE(SUM(CASE WHEN payment_method = "vodafone_cash" THEN total ELSE 0 END), 0) as vodafone_cash_sales,
                COALESCE(SUM(CASE WHEN payment_method = "instapay" THEN total ELSE 0 END), 0) as instapay_sales
            ')
            ->first();

        $returnsStats = SaleReturn::where('status', 'approved')
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('
                COUNT(*) as count,
                COALESCE(SUM(total_amount), 0) as total_returns,
                COALESCE(SUM(CASE WHEN refund_method = "cash" THEN total_amount ELSE 0 END), 0) as cash_returns,
                COALESCE(SUM(CASE WHEN refund_method = "vodafone_cash" THEN total_amount ELSE 0 END), 0) as vodafone_cash_returns,
                COALESCE(SUM(CASE WHEN refund_method = "instapay" THEN total_amount ELSE 0 END), 0) as instapay_returns
            ')
            ->first();

        $expensesStats = Expense::where('status', 'active')
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('
                COUNT(*) as count,
                COALESCE(SUM(amount), 0) as total_expenses,
                COALESCE(SUM(CASE WHEN payment_method = "cash" THEN amount ELSE 0 END), 0) as cash_expenses,
                COALESCE(SUM(CASE WHEN payment_method != "cash" THEN amount ELSE 0 END), 0) as non_cash_expenses
            ')
            ->first();

        // COGS and Gross Profit calculation using snapshot unit_cost
        $grossCogs = (float) (DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.status', 'completed')
            ->whereBetween('sales.created_at', [$start, $end])
            ->selectRaw('COALESCE(SUM(sale_items.quantity * sale_items.unit_cost), 0) as total')
            ->value('total') ?? 0);

        $returnedCogs = (float) (DB::table('return_items')
            ->join('returns', 'return_items.return_id', '=', 'returns.id')
            ->join('sale_items', 'return_items.sale_item_id', '=', 'sale_items.id')
            ->where('returns.status', 'approved')
            ->whereBetween('returns.created_at', [$start, $end])
            ->selectRaw('COALESCE(SUM(return_items.quantity * sale_items.unit_cost), 0) as total')
            ->value('total') ?? 0);

        $netCogs = max(0, round($grossCogs - $returnedCogs, 2));
        $grossSales = (float) $salesStats->gross_sales;
        $totalReturns = (float) $returnsStats->total_returns;
        $netSales = round($grossSales - $totalReturns, 2);
        $totalExpenses = (float) $expensesStats->total_expenses;

        $grossProfit = round($netSales - $netCogs, 2);
        $netProfit = round($grossProfit - $totalExpenses, 2);

        $grossProfitMargin = $netSales > 0 ? round(($grossProfit / $netSales) * 100, 1) : 0;
        $netProfitMargin = $netSales > 0 ? round(($netProfit / $netSales) * 100, 1) : 0;

        // Cash flow in physical drawer vs electronic methods
        $cashSales = (float) $salesStats->cash_sales;
        $cashReturns = (float) $returnsStats->cash_returns;
        $cashExpenses = (float) $expensesStats->cash_expenses;
        $netCashDrawerFlow = round($cashSales - $cashReturns - $cashExpenses, 2);

        $electronicSales = round((float) $salesStats->vodafone_cash_sales + (float) $salesStats->instapay_sales, 2);
        $electronicReturns = round((float) $returnsStats->vodafone_cash_returns + (float) $returnsStats->instapay_returns, 2);
        $electronicExpenses = (float) $expensesStats->non_cash_expenses;
        $netElectronicFlow = round($electronicSales - $electronicReturns - $electronicExpenses, 2);

        // 3. Low stock and Out of Stock products
        $lowStockQuery = Product::where('is_active', true)
            ->whereColumn('stock_quantity', '<=', 'minimum_stock');

        $lowStockCount = $lowStockQuery->count();
        $lowStockList = (clone $lowStockQuery)
            ->with(['subcategory.category'])
            ->orderBy('stock_quantity', 'asc')
            ->limit(6)
            ->get();

        // 4. Best Selling Products in period (accounting for returns)
        $bestSellingProducts = $this->getTopSellingProducts($start, $end, 6);

        // 5. Current Shift Status
        $activeShift = null;
        if ($user) {
            $activeShift = $this->shiftService->getActiveShift($user);
        }
        if (!$activeShift) {
            $activeShift = Shift::where('status', 'open')->latest('id')->first();
        }

        // 6. Sales Chart Trend (Daily or Hourly depending on range)
        $chartData = $this->getSalesChartData($start, $end);

        // 7. Recent Invoices
        $recentSales = Sale::with(['user'])
            ->latest('id')
            ->limit(6)
            ->get();

        return [
            'date_range' => $dateRange,
            'today' => [
                'sales_count' => (int) $todaySales->count,
                'gross_sales' => (float) $todaySales->gross,
                'discounts' => (float) $todaySales->discounts,
                'returns_count' => (int) $todayReturns->count,
                'returns_total' => (float) $todayReturns->total,
                'net_sales' => $todayNetSales,
                'expenses_total' => (float) $todayExpenses->total,
            ],
            'period' => [
                'invoices_count' => (int) $salesStats->count,
                'gross_subtotal' => (float) $salesStats->gross_subtotal,
                'discounts_total' => (float) $salesStats->total_discount,
                'gross_sales' => $grossSales,
                'returns_count' => (int) $returnsStats->count,
                'returns_total' => $totalReturns,
                'net_sales' => $netSales,
                'average_invoice_value' => $salesStats->count > 0 ? round($netSales / $salesStats->count, 2) : 0,

                // Payment Methods
                'cash_sales' => $cashSales,
                'vodafone_cash_sales' => (float) $salesStats->vodafone_cash_sales,
                'instapay_sales' => (float) $salesStats->instapay_sales,
                'electronic_sales' => $electronicSales,

                // Refunds Breakdown
                'cash_returns' => $cashReturns,
                'vodafone_cash_returns' => (float) $returnsStats->vodafone_cash_returns,
                'instapay_returns' => (float) $returnsStats->instapay_returns,
                'electronic_returns' => $electronicReturns,

                // Cash Reconciliation Flow
                'net_cash_drawer_flow' => $netCashDrawerFlow,
                'net_electronic_flow' => $netElectronicFlow,

                // Expenses
                'expenses_count' => (int) $expensesStats->count,
                'expenses_total' => $totalExpenses,
                'cash_expenses' => $cashExpenses,
                'non_cash_expenses' => $electronicExpenses,

                // COGS & Profits
                'gross_cogs' => $grossCogs,
                'returned_cogs' => $returnedCogs,
                'net_cogs' => $netCogs,
                'gross_profit' => $grossProfit,
                'gross_profit_margin' => $grossProfitMargin,
                'net_profit' => $netProfit,
                'net_profit_margin' => $netProfitMargin,
            ],
            'low_stock_count' => $lowStockCount,
            'low_stock_list' => $lowStockList,
            'best_selling_products' => $bestSellingProducts,
            'active_shift' => $activeShift,
            'recent_sales' => $recentSales,
            'chart_data' => $chartData,
        ];
    }

    /**
     * Get Sales Report with date filters, summary KPIs, breakdown, and paginated transactions.
     */
    public function getSalesReport(array $dateRange, array $filters = [], int $perPage = 15): array
    {
        $start = $dateRange['start'];
        $end = $dateRange['end'];

        $baseQuery = Sale::query()
            ->whereBetween('created_at', [$start, $end]);

        // Filter by status (default: completed, or all/cancelled)
        if (!empty($filters['status'])) {
            $baseQuery->where('status', $filters['status']);
        } else {
            $baseQuery->where('status', 'completed');
        }

        if (!empty($filters['payment_method'])) {
            $baseQuery->where('payment_method', $filters['payment_method']);
        }

        if (!empty($filters['user_id'])) {
            $baseQuery->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['shift_id'])) {
            $baseQuery->where('shift_id', $filters['shift_id']);
        }

        // Summary Aggregations
        $summary = (clone $baseQuery)->select(DB::raw('
            COUNT(*) as total_invoices,
            COALESCE(SUM(subtotal), 0) as total_subtotal,
            COALESCE(SUM(discount), 0) as total_discount,
            COALESCE(SUM(total), 0) as total_sales,
            COALESCE(SUM(CASE WHEN payment_method = "cash" THEN total ELSE 0 END), 0) as cash_sales,
            COALESCE(SUM(CASE WHEN payment_method = "vodafone_cash" THEN total ELSE 0 END), 0) as vodafone_cash_sales,
            COALESCE(SUM(CASE WHEN payment_method = "instapay" THEN total ELSE 0 END), 0) as instapay_sales
        '))->first();

        // Approved Returns in the same date range & filters
        $returnsQuery = SaleReturn::where('status', 'approved')->whereBetween('created_at', [$start, $end]);
        if (!empty($filters['user_id'])) {
            $returnsQuery->where('user_id', $filters['user_id']);
        }
        if (!empty($filters['shift_id'])) {
            $returnsQuery->where('shift_id', $filters['shift_id']);
        }

        $returnsSummary = (clone $returnsQuery)->select(DB::raw('
            COUNT(*) as returns_count,
            COALESCE(SUM(total_amount), 0) as returns_total,
            COALESCE(SUM(CASE WHEN refund_method = "cash" THEN total_amount ELSE 0 END), 0) as cash_returns,
            COALESCE(SUM(CASE WHEN refund_method != "cash" THEN total_amount ELSE 0 END), 0) as electronic_returns
        '))->first();

        $grossSales = (float) $summary->total_sales;
        $totalReturns = (float) $returnsSummary->returns_total;
        $netSales = round($grossSales - $totalReturns, 2);

        $sales = (clone $baseQuery)
            ->with(['user', 'shift'])
            ->withCount('items')
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();

        return [
            'date_range' => $dateRange,
            'summary' => [
                'total_invoices' => (int) $summary->total_invoices,
                'total_subtotal' => (float) $summary->total_subtotal,
                'total_discount' => (float) $summary->total_discount,
                'gross_sales' => $grossSales,
                'returns_count' => (int) $returnsSummary->returns_count,
                'returns_total' => $totalReturns,
                'net_sales' => $netSales,
                'cash_sales' => (float) $summary->cash_sales,
                'vodafone_cash_sales' => (float) $summary->vodafone_cash_sales,
                'instapay_sales' => (float) $summary->instapay_sales,
                'cash_returns' => (float) $returnsSummary->cash_returns,
                'electronic_returns' => (float) $returnsSummary->electronic_returns,
                'average_invoice' => $summary->total_invoices > 0 ? round($netSales / $summary->total_invoices, 2) : 0,
            ],
            'sales' => $sales,
            'users' => User::orderBy('name')->get(['id', 'name']),
            'shifts' => Shift::latest('id')->limit(30)->get(['id', 'opened_at', 'user_id']),
        ];
    }

    /**
     * Get Sales by Payment Method Report.
     */
    public function getPaymentMethodsReport(array $dateRange): array
    {
        $start = $dateRange['start'];
        $end = $dateRange['end'];

        $methods = ['cash', 'vodafone_cash', 'instapay'];
        $result = [];
        $totalGross = 0.0;
        $totalRefunds = 0.0;
        $totalNet = 0.0;

        foreach ($methods as $method) {
            $salesRow = Sale::where('status', 'completed')
                ->where('payment_method', $method)
                ->whereBetween('created_at', [$start, $end])
                ->selectRaw('COUNT(*) as count, COALESCE(SUM(total), 0) as amount')
                ->first();

            $returnsRow = SaleReturn::where('status', 'approved')
                ->where('refund_method', $method)
                ->whereBetween('created_at', [$start, $end])
                ->selectRaw('COUNT(*) as count, COALESCE(SUM(total_amount), 0) as amount')
                ->first();

            $gross = (float) $salesRow->amount;
            $refund = (float) $returnsRow->amount;
            $net = round($gross - $refund, 2);

            $totalGross += $gross;
            $totalRefunds += $refund;
            $totalNet += $net;

            $result[$method] = [
                'method' => $method,
                'label' => match ($method) {
                    'cash' => 'نقداً (كاش)',
                    'vodafone_cash' => 'فودافون كاش',
                    'instapay' => 'انستا باي (InstaPay)',
                },
                'is_cash' => $method === 'cash',
                'sales_count' => (int) $salesRow->count,
                'gross_amount' => $gross,
                'refunds_count' => (int) $returnsRow->count,
                'refunds_amount' => $refund,
                'net_amount' => $net,
                'percentage' => 0.0,
            ];
        }

        // Calculate percentages
        foreach ($result as $method => $data) {
            $result[$method]['percentage'] = $totalNet > 0 ? round(($data['net_amount'] / $totalNet) * 100, 1) : 0;
        }

        // Physical Cash vs Electronic Wallets/Bank
        $physicalCash = $result['cash'];
        $electronic = [
            'gross_amount' => $result['vodafone_cash']['gross_amount'] + $result['instapay']['gross_amount'],
            'refunds_amount' => $result['vodafone_cash']['refunds_amount'] + $result['instapay']['refunds_amount'],
            'net_amount' => $result['vodafone_cash']['net_amount'] + $result['instapay']['net_amount'],
            'sales_count' => $result['vodafone_cash']['sales_count'] + $result['instapay']['sales_count'],
            'percentage' => $totalNet > 0 ? round((($result['vodafone_cash']['net_amount'] + $result['instapay']['net_amount']) / $totalNet) * 100, 1) : 0,
        ];

        return [
            'date_range' => $dateRange,
            'methods' => $result,
            'totals' => [
                'gross' => $totalGross,
                'refunds' => $totalRefunds,
                'net' => $totalNet,
            ],
            'physical_cash' => $physicalCash,
            'electronic' => $electronic,
        ];
    }

    /**
     * Get Product Sales Report.
     */
    public function getProductSalesReport(array $dateRange, array $filters = [], int $perPage = 15): array
    {
        $start = $dateRange['start'];
        $end = $dateRange['end'];

        // Subquery for sales quantities & revenue
        $salesSub = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.status', 'completed')
            ->whereBetween('sales.created_at', [$start, $end])
            ->select(
                'sale_items.product_id',
                DB::raw('SUM(sale_items.quantity) as qty_sold'),
                DB::raw('SUM(sale_items.total) as gross_revenue'),
                DB::raw('SUM(sale_items.quantity * sale_items.unit_cost) as gross_cost')
            )
            ->groupBy('sale_items.product_id');

        // Subquery for returns quantities & refunds
        $returnsSub = DB::table('return_items')
            ->join('returns', 'return_items.return_id', '=', 'returns.id')
            ->join('sale_items', 'return_items.sale_item_id', '=', 'sale_items.id')
            ->where('returns.status', 'approved')
            ->whereBetween('returns.created_at', [$start, $end])
            ->select(
                'return_items.product_id',
                DB::raw('SUM(return_items.quantity) as qty_returned'),
                DB::raw('SUM(return_items.total) as refunded_revenue'),
                DB::raw('SUM(return_items.quantity * sale_items.unit_cost) as returned_cost')
            )
            ->groupBy('return_items.product_id');

        $baseQuery = Product::query()
            ->joinSub($salesSub, 'sales_agg', 'products.id', '=', 'sales_agg.product_id')
            ->leftJoinSub($returnsSub, 'returns_agg', 'products.id', '=', 'returns_agg.product_id')
            ->leftJoin('subcategories', 'products.subcategory_id', '=', 'subcategories.id')
            ->leftJoin('categories', 'subcategories.category_id', '=', 'categories.id');

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $baseQuery->where('products.name', 'like', "%{$search}%");
        }

        if (!empty($filters['category_id'])) {
            $baseQuery->where('categories.id', $filters['category_id']);
        }

        if (!empty($filters['subcategory_id'])) {
            $baseQuery->where('subcategories.id', $filters['subcategory_id']);
        }

        // Summary metrics
        $summary = (clone $baseQuery)->select(DB::raw('
            COUNT(DISTINCT products.id) as total_products,
            COALESCE(SUM(sales_agg.qty_sold), 0) as total_qty_sold,
            COALESCE(SUM(returns_agg.qty_returned), 0) as total_qty_returned,
            COALESCE(SUM(sales_agg.qty_sold - COALESCE(returns_agg.qty_returned, 0)), 0) as total_net_qty,
            COALESCE(SUM(sales_agg.gross_revenue - COALESCE(returns_agg.refunded_revenue, 0)), 0) as total_net_revenue,
            COALESCE(SUM(sales_agg.gross_cost - COALESCE(returns_agg.returned_cost, 0)), 0) as total_net_cost,
            COALESCE(SUM((sales_agg.gross_revenue - COALESCE(returns_agg.refunded_revenue, 0)) - (sales_agg.gross_cost - COALESCE(returns_agg.returned_cost, 0))), 0) as total_gross_profit
        '))->first();

        $products = (clone $baseQuery)->select([
            'products.id',
            'products.name',
            'products.stock_quantity',
            'products.purchase_price',
            'products.selling_price',
            'subcategories.name as subcategory_name',
            'categories.name as category_name',
            DB::raw('COALESCE(sales_agg.qty_sold, 0) as qty_sold'),
            DB::raw('COALESCE(returns_agg.qty_returned, 0) as qty_returned'),
            DB::raw('(COALESCE(sales_agg.qty_sold, 0) - COALESCE(returns_agg.qty_returned, 0)) as net_qty'),
            DB::raw('COALESCE(sales_agg.gross_revenue, 0) as gross_revenue'),
            DB::raw('COALESCE(returns_agg.refunded_revenue, 0) as refunded_revenue'),
            DB::raw('(COALESCE(sales_agg.gross_revenue, 0) - COALESCE(returns_agg.refunded_revenue, 0)) as net_revenue'),
            DB::raw('COALESCE(sales_agg.gross_cost, 0) as gross_cost'),
            DB::raw('COALESCE(returns_agg.returned_cost, 0) as returned_cost'),
            DB::raw('(COALESCE(sales_agg.gross_cost, 0) - COALESCE(returns_agg.returned_cost, 0)) as net_cost'),
            DB::raw('((COALESCE(sales_agg.gross_revenue, 0) - COALESCE(returns_agg.refunded_revenue, 0)) - (COALESCE(sales_agg.gross_cost, 0) - COALESCE(returns_agg.returned_cost, 0))) as gross_profit'),
        ])->orderBy('net_qty', 'desc')->paginate($perPage)->withQueryString();

        return [
            'date_range' => $dateRange,
            'summary' => [
                'total_products' => (int) ($summary->total_products ?? 0),
                'total_qty_sold' => (float) ($summary->total_qty_sold ?? 0),
                'total_qty_returned' => (float) ($summary->total_qty_returned ?? 0),
                'total_net_qty' => (float) ($summary->total_net_qty ?? 0),
                'total_net_revenue' => (float) ($summary->total_net_revenue ?? 0),
                'total_net_cost' => (float) ($summary->total_net_cost ?? 0),
                'total_gross_profit' => (float) ($summary->total_gross_profit ?? 0),
                'profit_margin' => ($summary && $summary->total_net_revenue > 0)
                    ? round(($summary->total_gross_profit / $summary->total_net_revenue) * 100, 1)
                    : 0,
            ],
            'products' => $products,
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'subcategories' => Subcategory::orderBy('name')->get(['id', 'name', 'category_id']),
        ];
    }

    /**
     * Get Employee / Cashier Sales Report.
     */
    public function getEmployeeSalesReport(array $dateRange): array
    {
        $start = $dateRange['start'];
        $end = $dateRange['end'];

        $salesSub = DB::table('sales')
            ->where('status', 'completed')
            ->whereBetween('created_at', [$start, $end])
            ->select(
                'user_id',
                DB::raw('COUNT(*) as sales_count'),
                DB::raw('COALESCE(SUM(subtotal), 0) as gross_subtotal'),
                DB::raw('COALESCE(SUM(discount), 0) as total_discounts'),
                DB::raw('COALESCE(SUM(total), 0) as gross_sales'),
                DB::raw('COALESCE(SUM(CASE WHEN payment_method = "cash" THEN total ELSE 0 END), 0) as cash_sales'),
                DB::raw('COALESCE(SUM(CASE WHEN payment_method != "cash" THEN total ELSE 0 END), 0) as electronic_sales')
            )
            ->groupBy('user_id');

        $returnsSub = DB::table('returns')
            ->where('status', 'approved')
            ->whereBetween('created_at', [$start, $end])
            ->select(
                'user_id',
                DB::raw('COUNT(*) as returns_count'),
                DB::raw('COALESCE(SUM(total_amount), 0) as returns_total'),
                DB::raw('COALESCE(SUM(CASE WHEN refund_method = "cash" THEN total_amount ELSE 0 END), 0) as cash_returns'),
                DB::raw('COALESCE(SUM(CASE WHEN refund_method != "cash" THEN total_amount ELSE 0 END), 0) as electronic_returns')
            )
            ->groupBy('user_id');

        $shiftsSub = DB::table('shifts')
            ->whereBetween('opened_at', [$start, $end])
            ->select('user_id', DB::raw('COUNT(*) as shifts_count'))
            ->groupBy('user_id');

        $employees = User::query()
            ->leftJoinSub($salesSub, 's', 'users.id', '=', 's.user_id')
            ->leftJoinSub($returnsSub, 'r', 'users.id', '=', 'r.user_id')
            ->leftJoinSub($shiftsSub, 'sh', 'users.id', '=', 'sh.user_id')
            ->select([
                'users.id',
                'users.name',
                'users.username',
                'users.role',
                DB::raw('COALESCE(s.sales_count, 0) as sales_count'),
                DB::raw('COALESCE(s.gross_subtotal, 0) as gross_subtotal'),
                DB::raw('COALESCE(s.total_discounts, 0) as total_discounts'),
                DB::raw('COALESCE(s.gross_sales, 0) as gross_sales'),
                DB::raw('COALESCE(r.returns_count, 0) as returns_count'),
                DB::raw('COALESCE(r.returns_total, 0) as returns_total'),
                DB::raw('(COALESCE(s.gross_sales, 0) - COALESCE(r.returns_total, 0)) as net_sales'),
                DB::raw('COALESCE(s.cash_sales, 0) as cash_sales'),
                DB::raw('COALESCE(s.electronic_sales, 0) as electronic_sales'),
                DB::raw('COALESCE(r.cash_returns, 0) as cash_returns'),
                DB::raw('COALESCE(r.electronic_returns, 0) as electronic_returns'),
                DB::raw('(COALESCE(s.cash_sales, 0) - COALESCE(r.cash_returns, 0)) as net_cash'),
                DB::raw('COALESCE(sh.shifts_count, 0) as shifts_count'),
            ])
            ->orderBy('net_sales', 'desc')
            ->get();

        $totals = [
            'sales_count' => $employees->sum('sales_count'),
            'gross_sales' => (float) $employees->sum('gross_sales'),
            'discounts' => (float) $employees->sum('total_discounts'),
            'returns_count' => $employees->sum('returns_count'),
            'returns_total' => (float) $employees->sum('returns_total'),
            'net_sales' => (float) $employees->sum('net_sales'),
            'cash_sales' => (float) $employees->sum('cash_sales'),
            'electronic_sales' => (float) $employees->sum('electronic_sales'),
        ];

        return [
            'date_range' => $dateRange,
            'employees' => $employees,
            'totals' => $totals,
        ];
    }

    /**
     * Get Shifts and Cash Reconciliation Report.
     */
    public function getShiftReport(array $dateRange, array $filters = [], int $perPage = 15): array
    {
        $start = $dateRange['start'];
        $end = $dateRange['end'];

        $baseQuery = Shift::query()
            ->whereBetween('opened_at', [$start, $end]);

        if (!empty($filters['user_id'])) {
            $baseQuery->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['status'])) {
            $baseQuery->where('status', $filters['status']);
        }

        // Summary Aggregations
        $summary = (clone $baseQuery)->select(DB::raw('
            COUNT(*) as total_shifts,
            COALESCE(SUM(CASE WHEN status = "open" THEN 1 ELSE 0 END), 0) as open_shifts,
            COALESCE(SUM(CASE WHEN status = "closed" THEN 1 ELSE 0 END), 0) as closed_shifts,
            COALESCE(SUM(opening_cash), 0) as total_opening_cash,
            COALESCE(SUM(closing_cash), 0) as total_closing_cash,
            COALESCE(SUM(expected_cash), 0) as total_expected_cash,
            COALESCE(SUM(difference), 0) as total_difference,
            COALESCE(SUM(CASE WHEN difference > 0.001 THEN 1 ELSE 0 END), 0) as surplus_shifts_count,
            COALESCE(SUM(CASE WHEN difference < -0.001 THEN 1 ELSE 0 END), 0) as shortage_shifts_count,
            COALESCE(SUM(CASE WHEN ABS(difference) <= 0.001 AND status = "closed" THEN 1 ELSE 0 END), 0) as balanced_shifts_count
        '))->first();

        $shifts = (clone $baseQuery)->with(['user'])->latest('id')->paginate($perPage)->withQueryString();

        return [
            'date_range' => $dateRange,
            'summary' => [
                'total_shifts' => (int) $summary->total_shifts,
                'open_shifts' => (int) $summary->open_shifts,
                'closed_shifts' => (int) $summary->closed_shifts,
                'total_opening_cash' => (float) $summary->total_opening_cash,
                'total_closing_cash' => (float) $summary->total_closing_cash,
                'total_expected_cash' => (float) $summary->total_expected_cash,
                'total_difference' => (float) $summary->total_difference,
                'surplus_count' => (int) $summary->surplus_shifts_count,
                'shortage_count' => (int) $summary->shortage_shifts_count,
                'balanced_count' => (int) $summary->balanced_shifts_count,
            ],
            'shifts' => $shifts,
            'users' => User::orderBy('name')->get(['id', 'name']),
        ];
    }

    /**
     * Get Expense Report by category and period.
     */
    public function getExpenseReport(array $dateRange, array $filters = [], int $perPage = 15): array
    {
        $start = $dateRange['start'];
        $end = $dateRange['end'];

        $baseQuery = Expense::query()
            ->whereBetween('created_at', [$start, $end]);

        if (!empty($filters['status'])) {
            $baseQuery->where('status', $filters['status']);
        } else {
            $baseQuery->where('status', 'active');
        }

        if (!empty($filters['category'])) {
            $baseQuery->where('category', $filters['category']);
        }

        if (!empty($filters['payment_method'])) {
            $baseQuery->where('payment_method', $filters['payment_method']);
        }

        if (!empty($filters['user_id'])) {
            $baseQuery->where('user_id', $filters['user_id']);
        }

        // Summary metrics
        $summary = (clone $baseQuery)->select(DB::raw('
            COUNT(*) as total_count,
            COALESCE(SUM(amount), 0) as total_amount,
            COALESCE(SUM(CASE WHEN payment_method = "cash" THEN amount ELSE 0 END), 0) as cash_expenses,
            COALESCE(SUM(CASE WHEN payment_method != "cash" THEN amount ELSE 0 END), 0) as electronic_expenses
        '))->first();

        // Category breakdown for active expenses
        $categoriesBreakdown = Expense::where('status', 'active')
            ->whereBetween('created_at', [$start, $end])
            ->select(DB::raw('
                category,
                COUNT(*) as count,
                COALESCE(SUM(amount), 0) as total_amount,
                COALESCE(SUM(CASE WHEN payment_method = "cash" THEN amount ELSE 0 END), 0) as cash_amount,
                COALESCE(SUM(CASE WHEN payment_method != "cash" THEN amount ELSE 0 END), 0) as electronic_amount
            '))
            ->groupBy('category')
            ->orderBy('total_amount', 'desc')
            ->get();

        $expenses = (clone $baseQuery)->with(['user', 'shift'])->latest('id')->paginate($perPage)->withQueryString();

        return [
            'date_range' => $dateRange,
            'summary' => [
                'total_count' => (int) ($summary->total_count ?? 0),
                'total_amount' => (float) ($summary->total_amount ?? 0),
                'cash_expenses' => (float) ($summary->cash_expenses ?? 0),
                'electronic_expenses' => (float) ($summary->electronic_expenses ?? 0),
            ],
            'categories_breakdown' => $categoriesBreakdown,
            'expenses' => $expenses,
            'users' => User::orderBy('name')->get(['id', 'name']),
            'categories_list' => Expense::distinct()->pluck('category')->filter()->values(),
        ];
    }

    /**
     * Get Inventory & Stock Movement Report.
     */
    public function getInventoryReport(array $dateRange, array $filters = [], int $perPage = 20): array
    {
        $start = $dateRange['start'];
        $end = $dateRange['end'];

        $baseQuery = StockMovement::query()
            ->whereBetween('created_at', [$start, $end]);

        if (!empty($filters['type'])) {
            $baseQuery->where('type', $filters['type']);
        }

        if (!empty($filters['reference_type'])) {
            $baseQuery->where('reference_type', $filters['reference_type']);
        }

        if (!empty($filters['product_id'])) {
            $baseQuery->where('product_id', $filters['product_id']);
        }

        if (!empty($filters['user_id'])) {
            $baseQuery->where('user_id', $filters['user_id']);
        }

        // Summary Aggregations
        $summary = (clone $baseQuery)->select(DB::raw('
            COUNT(*) as total_movements,
            COALESCE(SUM(CASE WHEN type = "in" THEN quantity ELSE 0 END), 0) as total_in,
            COALESCE(SUM(CASE WHEN type = "out" THEN ABS(quantity) ELSE 0 END), 0) as total_out,
            COALESCE(SUM(quantity), 0) as net_quantity_change
        '))->first();

        // Warehouse Current Stock Valuation
        $warehouseValuation = Product::select(DB::raw('
            COUNT(*) as total_products,
            COALESCE(SUM(stock_quantity), 0) as total_units,
            COALESCE(SUM(stock_quantity * purchase_price), 0) as total_cost_value,
            COALESCE(SUM(stock_quantity * selling_price), 0) as total_retail_value
        '))->first();

        $movements = (clone $baseQuery)->with(['product.subcategory', 'user'])->latest('id')->paginate($perPage)->withQueryString();

        return [
            'date_range' => $dateRange,
            'summary' => [
                'total_movements' => (int) $summary->total_movements,
                'total_in' => (float) $summary->total_in,
                'total_out' => (float) $summary->total_out,
                'net_change' => (float) $summary->net_quantity_change,
            ],
            'warehouse' => [
                'total_products' => (int) $warehouseValuation->total_products,
                'total_units' => (float) $warehouseValuation->total_units,
                'total_cost_value' => (float) $warehouseValuation->total_cost_value,
                'total_retail_value' => (float) $warehouseValuation->total_retail_value,
                'potential_profit' => round((float) $warehouseValuation->total_retail_value - (float) $warehouseValuation->total_cost_value, 2),
            ],
            'movements' => $movements,
            'products' => Product::orderBy('name')->get(['id', 'name']),
            'users' => User::orderBy('name')->get(['id', 'name']),
        ];
    }

    /**
     * Get Low-Stock & Out-of-Stock Report.
     */
    public function getLowStockReport(array $filters = [], int $perPage = 20): array
    {
        $query = Product::query()
            ->with(['subcategory.category'])
            ->where('is_active', true)
            ->whereColumn('stock_quantity', '<=', 'minimum_stock');

        if (!empty($filters['status'])) {
            if ($filters['status'] === 'out_of_stock') {
                $query->where('stock_quantity', '<=', 0);
            } elseif ($filters['status'] === 'low_stock') {
                $query->where('stock_quantity', '>', 0);
            }
        }

        if (!empty($filters['category_id'])) {
            $query->whereHas('subcategory', fn($q) => $q->where('category_id', $filters['category_id']));
        }

        if (!empty($filters['subcategory_id'])) {
            $query->where('subcategory_id', $filters['subcategory_id']);
        }

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where('name', 'like', "%{$search}%");
        }

        // Summary metrics
        $outOfStockCount = Product::where('is_active', true)->where('stock_quantity', '<=', 0)->count();
        $lowStockCount = Product::where('is_active', true)
            ->whereColumn('stock_quantity', '<=', 'minimum_stock')
            ->where('stock_quantity', '>', 0)
            ->count();

        // Estimated cost to reorder to minimum stock * 2 (standard buffer)
        $reorderEstCost = (float) (Product::where('is_active', true)
            ->whereColumn('stock_quantity', '<=', 'minimum_stock')
            ->selectRaw('SUM(GREATEST(0, (minimum_stock * 2) - stock_quantity) * purchase_price) as total')
            ->value('total') ?? 0);

        $products = $query->orderBy('stock_quantity', 'asc')->paginate($perPage)->withQueryString();

        return [
            'summary' => [
                'total_alerts' => $outOfStockCount + $lowStockCount,
                'out_of_stock_count' => $outOfStockCount,
                'low_stock_count' => $lowStockCount,
                'reorder_cost_estimate' => round($reorderEstCost, 2),
            ],
            'products' => $products,
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'subcategories' => Subcategory::orderBy('name')->get(['id', 'name', 'category_id']),
        ];
    }

    /**
     * Get Comprehensive Profit & Loss (Income Statement) Report.
     */
    public function getProfitReport(array $dateRange): array
    {
        $start = $dateRange['start'];
        $end = $dateRange['end'];

        // 1. Revenue & Sales
        $salesRow = Sale::where('status', 'completed')
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('
                COUNT(*) as sales_count,
                COALESCE(SUM(subtotal), 0) as gross_subtotal,
                COALESCE(SUM(discount), 0) as total_discount,
                COALESCE(SUM(total), 0) as gross_sales,
                COALESCE(SUM(CASE WHEN payment_method = "cash" THEN total ELSE 0 END), 0) as cash_sales,
                COALESCE(SUM(CASE WHEN payment_method = "vodafone_cash" THEN total ELSE 0 END), 0) as vodafone_cash_sales,
                COALESCE(SUM(CASE WHEN payment_method = "instapay" THEN total ELSE 0 END), 0) as instapay_sales
            ')
            ->first();

        // 2. Returns
        $returnsRow = SaleReturn::where('status', 'approved')
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('
                COUNT(*) as returns_count,
                COALESCE(SUM(total_amount), 0) as total_returns,
                COALESCE(SUM(CASE WHEN refund_method = "cash" THEN total_amount ELSE 0 END), 0) as cash_returns,
                COALESCE(SUM(CASE WHEN refund_method = "vodafone_cash" THEN total_amount ELSE 0 END), 0) as vodafone_cash_returns,
                COALESCE(SUM(CASE WHEN refund_method = "instapay" THEN total_amount ELSE 0 END), 0) as instapay_returns
            ')
            ->first();

        $grossSubtotal = (float) $salesRow->gross_subtotal;
        $totalDiscount = (float) $salesRow->total_discount;
        $grossSales = (float) $salesRow->gross_sales;
        $totalReturns = (float) $returnsRow->total_returns;
        $netSales = round($grossSales - $totalReturns, 2);

        // 3. COGS Calculation (Historical unit_cost snapshots)
        $grossCogs = (float) (DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.status', 'completed')
            ->whereBetween('sales.created_at', [$start, $end])
            ->selectRaw('COALESCE(SUM(sale_items.quantity * sale_items.unit_cost), 0) as total')
            ->value('total') ?? 0);

        $returnedCogs = (float) (DB::table('return_items')
            ->join('returns', 'return_items.return_id', '=', 'returns.id')
            ->join('sale_items', 'return_items.sale_item_id', '=', 'sale_items.id')
            ->where('returns.status', 'approved')
            ->whereBetween('returns.created_at', [$start, $end])
            ->selectRaw('COALESCE(SUM(return_items.quantity * sale_items.unit_cost), 0) as total')
            ->value('total') ?? 0);

        $netCogs = max(0, round($grossCogs - $returnedCogs, 2));

        // 4. Gross Profit
        $grossProfit = round($netSales - $netCogs, 2);
        $grossProfitMargin = $netSales > 0 ? round(($grossProfit / $netSales) * 100, 1) : 0;

        // 5. Operating Expenses
        $expensesRow = Expense::where('status', 'active')
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('
                COUNT(*) as count,
                COALESCE(SUM(amount), 0) as total_expenses,
                COALESCE(SUM(CASE WHEN payment_method = "cash" THEN amount ELSE 0 END), 0) as cash_expenses,
                COALESCE(SUM(CASE WHEN payment_method != "cash" THEN amount ELSE 0 END), 0) as non_cash_expenses
            ')
            ->first();

        $totalExpenses = (float) $expensesRow->total_expenses;

        // Expenses by category
        $expensesByCategory = Expense::where('status', 'active')
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('category, COUNT(*) as count, COALESCE(SUM(amount), 0) as total')
            ->groupBy('category')
            ->orderBy('total', 'desc')
            ->get();

        // 6. Net Profit / Net Income
        $netProfit = round($grossProfit - $totalExpenses, 2);
        $netProfitMargin = $netSales > 0 ? round(($netProfit / $netSales) * 100, 1) : 0;

        // 7. Cash Flow Reconciliation (Physical Drawer)
        $cashSales = (float) $salesRow->cash_sales;
        $cashReturns = (float) $returnsRow->cash_returns;
        $cashExpenses = (float) $expensesRow->cash_expenses;
        $netPhysicalCashFlow = round($cashSales - $cashReturns - $cashExpenses, 2);

        // 8. Electronic Wallets & Bank (Vodafone Cash + InstaPay)
        $vodafoneNet = (float) $salesRow->vodafone_cash_sales - (float) $returnsRow->vodafone_cash_returns;
        $instapayNet = (float) $salesRow->instapay_sales - (float) $returnsRow->instapay_returns;
        $electronicExpenses = (float) $expensesRow->non_cash_expenses;
        $netElectronicFlow = round($vodafoneNet + $instapayNet - $electronicExpenses, 2);

        // 9. Periodic Breakdown (Daily for <= 31 days, Monthly otherwise)
        $breakdown = $this->getProfitBreakdown($start, $end);

        return [
            'date_range' => $dateRange,
            'income_statement' => [
                'gross_subtotal' => $grossSubtotal,
                'total_discount' => $totalDiscount,
                'gross_sales' => $grossSales,
                'total_returns' => $totalReturns,
                'net_sales' => $netSales,

                'gross_cogs' => $grossCogs,
                'returned_cogs' => $returnedCogs,
                'net_cogs' => $netCogs,

                'gross_profit' => $grossProfit,
                'gross_profit_margin' => $grossProfitMargin,

                'total_expenses' => $totalExpenses,
                'expenses_by_category' => $expensesByCategory,

                'net_profit' => $netProfit,
                'net_profit_margin' => $netProfitMargin,
            ],
            'cash_flow' => [
                'cash_sales' => $cashSales,
                'cash_returns' => $cashReturns,
                'cash_expenses' => $cashExpenses,
                'net_cash_flow' => $netPhysicalCashFlow,

                'vodafone_cash_sales' => (float) $salesRow->vodafone_cash_sales,
                'vodafone_cash_returns' => (float) $returnsRow->vodafone_cash_returns,
                'instapay_sales' => (float) $salesRow->instapay_sales,
                'instapay_returns' => (float) $returnsRow->instapay_returns,
                'electronic_expenses' => $electronicExpenses,
                'net_electronic_flow' => $netElectronicFlow,
            ],
            'breakdown' => $breakdown,
        ];
    }

    // --- Private Helper Aggregation Queries ---

    /**
     * Get top selling products by net quantity sold.
     */
    protected function getTopSellingProducts(Carbon $start, Carbon $end, int $limit = 5): Collection
    {
        $salesSub = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.status', 'completed')
            ->whereBetween('sales.created_at', [$start, $end])
            ->select('sale_items.product_id', DB::raw('SUM(sale_items.quantity) as qty_sold'), DB::raw('SUM(sale_items.total) as gross_revenue'))
            ->groupBy('sale_items.product_id');

        $returnsSub = DB::table('return_items')
            ->join('returns', 'return_items.return_id', '=', 'returns.id')
            ->where('returns.status', 'approved')
            ->whereBetween('returns.created_at', [$start, $end])
            ->select('return_items.product_id', DB::raw('SUM(return_items.quantity) as qty_returned'), DB::raw('SUM(return_items.total) as refunded_revenue'))
            ->groupBy('return_items.product_id');

        return Product::query()
            ->joinSub($salesSub, 's', 'products.id', '=', 's.product_id')
            ->leftJoinSub($returnsSub, 'r', 'products.id', '=', 'r.product_id')
            ->select([
                'products.id',
                'products.name',
                'products.stock_quantity',
                'products.selling_price',
                DB::raw('COALESCE(s.qty_sold, 0) as qty_sold'),
                DB::raw('COALESCE(r.qty_returned, 0) as qty_returned'),
                DB::raw('(COALESCE(s.qty_sold, 0) - COALESCE(r.qty_returned, 0)) as net_qty'),
                DB::raw('(COALESCE(s.gross_revenue, 0) - COALESCE(r.refunded_revenue, 0)) as net_revenue'),
            ])
            ->whereRaw('(COALESCE(s.qty_sold, 0) - COALESCE(r.qty_returned, 0)) > 0')
            ->orderBy('net_qty', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get daily or hourly chart trend for Sales & Net Sales.
     */
    protected function getSalesChartData(Carbon $start, Carbon $end): array
    {
        $daysDiff = $start->diffInDays($end);
        $labels = [];
        $salesData = [];
        $netSalesData = [];

        if ($daysDiff <= 1) {
            // Hourly breakdown (00:00 to 23:00)
            for ($hour = 0; $hour < 24; $hour++) {
                $hourStart = $start->copy()->hour($hour)->minute(0)->second(0);
                $hourEnd = $start->copy()->hour($hour)->minute(59)->second(59);

                $salesVal = (float) (Sale::where('status', 'completed')
                    ->whereBetween('created_at', [$hourStart, $hourEnd])
                    ->sum('total') ?? 0);

                $retVal = (float) (SaleReturn::where('status', 'approved')
                    ->whereBetween('created_at', [$hourStart, $hourEnd])
                    ->sum('total_amount') ?? 0);

                $labels[] = sprintf('%02d:00', $hour);
                $salesData[] = round($salesVal, 2);
                $netSalesData[] = round($salesVal - $retVal, 2);
            }
        } else {
            // Daily breakdown
            $current = $start->copy();
            while ($current->lte($end)) {
                $dayStart = $current->copy()->startOfDay();
                $dayEnd = $current->copy()->endOfDay();

                $salesVal = (float) (Sale::where('status', 'completed')
                    ->whereBetween('created_at', [$dayStart, $dayEnd])
                    ->sum('total') ?? 0);

                $retVal = (float) (SaleReturn::where('status', 'approved')
                    ->whereBetween('created_at', [$dayStart, $dayEnd])
                    ->sum('total_amount') ?? 0);

                $labels[] = $current->format('m/d');
                $salesData[] = round($salesVal, 2);
                $netSalesData[] = round($salesVal - $retVal, 2);

                $current->addDay();
            }
        }

        return [
            'labels' => $labels,
            'sales' => $salesData,
            'net_sales' => $netSalesData,
        ];
    }

    /**
     * Generate periodic tabular breakdown for profit report.
     */
    protected function getProfitBreakdown(Carbon $start, Carbon $end): array
    {
        $rows = [];
        $current = $start->copy();

        while ($current->lte($end)) {
            $dayStart = $current->copy()->startOfDay();
            $dayEnd = $current->copy()->endOfDay();

            $salesTotal = (float) (Sale::where('status', 'completed')
                ->whereBetween('created_at', [$dayStart, $dayEnd])
                ->sum('total') ?? 0);

            $returnsTotal = (float) (SaleReturn::where('status', 'approved')
                ->whereBetween('created_at', [$dayStart, $dayEnd])
                ->sum('total_amount') ?? 0);

            $netSales = round($salesTotal - $returnsTotal, 2);

            $dayGrossCogs = (float) (DB::table('sale_items')
                ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->where('sales.status', 'completed')
                ->whereBetween('sales.created_at', [$dayStart, $dayEnd])
                ->selectRaw('COALESCE(SUM(sale_items.quantity * sale_items.unit_cost), 0) as total')
                ->value('total') ?? 0);

            $dayRetCogs = (float) (DB::table('return_items')
                ->join('returns', 'return_items.return_id', '=', 'returns.id')
                ->join('sale_items', 'return_items.sale_item_id', '=', 'sale_items.id')
                ->where('returns.status', 'approved')
                ->whereBetween('returns.created_at', [$dayStart, $dayEnd])
                ->selectRaw('COALESCE(SUM(return_items.quantity * sale_items.unit_cost), 0) as total')
                ->value('total') ?? 0);

            $dayNetCogs = max(0, round($dayGrossCogs - $dayRetCogs, 2));
            $grossProfit = round($netSales - $dayNetCogs, 2);

            $expenses = (float) (Expense::where('status', 'active')
                ->whereBetween('created_at', [$dayStart, $dayEnd])
                ->sum('amount') ?? 0);

            $netProfit = round($grossProfit - $expenses, 2);

            $rows[] = [
                'date' => $current->format('Y-m-d'),
                'day_name' => $current->translatedFormat('l'),
                'gross_sales' => $salesTotal,
                'returns' => $returnsTotal,
                'net_sales' => $netSales,
                'net_cogs' => $dayNetCogs,
                'gross_profit' => $grossProfit,
                'expenses' => $expenses,
                'net_profit' => $netProfit,
            ];

            $current->addDay();
        }

        return $rows;
    }
}

