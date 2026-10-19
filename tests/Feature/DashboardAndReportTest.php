<?php

namespace Tests\Feature;

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
use App\Services\PosService;
use App\Services\ReportService;
use App\Services\ReturnService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardAndReportTest extends TestCase
{
    use RefreshDatabase;

    protected User $cashier;
    protected User $owner;
    protected Shift $shift;
    protected Product $productA; // price: 200, cost: 120
    protected Product $productB; // price: 500, cost: 300
    protected Product $productLow; // stock: 1, min: 5
    protected Sale $sale1; // Cash sale
    protected Sale $sale2; // Vodafone Cash sale
    protected SaleReturn $return1; // Return 1 unit of productA from sale1

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create([
            'username' => 'owner_reports',
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->cashier = User::factory()->create([
            'username' => 'cashier_reports',
            'role' => 'employee',
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'هدايا وتحف',
            'is_active' => true,
        ]);

        $subcategory = Subcategory::create([
            'category_id' => $category->id,
            'name' => 'ساعات وعطور',
            'is_active' => true,
        ]);

        // Product A: 20 in stock, cost 120, price 200, min 3
        $this->productA = Product::create([
            'subcategory_id' => $subcategory->id,
            'name' => 'عطر فرنسي فاخر',
            'purchase_price' => 120.00,
            'selling_price' => 200.00,
            'stock_quantity' => 20.000,
            'minimum_stock' => 3.000,
            'is_active' => true,
        ]);

        // Product B: 10 in stock, cost 300, price 500, min 2
        $this->productB = Product::create([
            'subcategory_id' => $subcategory->id,
            'name' => 'ساعة كلاسيك رجالي',
            'purchase_price' => 300.00,
            'selling_price' => 500.00,
            'stock_quantity' => 10.000,
            'minimum_stock' => 2.000,
            'is_active' => true,
        ]);

        // Low stock product: 1 in stock, cost 50, price 80, min 5
        $this->productLow = Product::create([
            'subcategory_id' => $subcategory->id,
            'name' => 'ميدالية فضية',
            'purchase_price' => 50.00,
            'selling_price' => 80.00,
            'stock_quantity' => 1.000,
            'minimum_stock' => 5.000,
            'is_active' => true,
        ]);

        // Open shift for cashier with 300 opening cash
        $this->shift = Shift::create([
            'user_id' => $this->cashier->id,
            'opened_at' => now(),
            'opening_cash' => 300.00,
            'status' => 'open',
        ]);

        /** @var PosService $posService */
        $posService = app(PosService::class);

        // Sale 1: Cash sale -> 3 units of Product A (3 * 200 = 600), discount 50 -> total 550.
        // Cost: 3 * 120 = 360.
        $this->sale1 = $posService->createSale([
            'payment_method' => 'cash',
            'discount' => 50.00,
            'items' => [
                ['product_id' => $this->productA->id, 'quantity' => 3],
            ],
        ], $this->cashier, $this->shift);

        // Sale 2: Vodafone Cash sale -> 2 units of Product B (2 * 500 = 1000), discount 0 -> total 1000.
        // Cost: 2 * 300 = 600.
        $this->sale2 = $posService->createSale([
            'payment_method' => 'vodafone_cash',
            'discount' => 0.00,
            'items' => [
                ['product_id' => $this->productB->id, 'quantity' => 2],
            ],
        ], $this->cashier, $this->shift);

        // Return 1: Return 1 unit of Product A from Sale 1 (1 * 200 = 200), cash refund.
        // Returned cost: 1 * 120 = 120.
        /** @var ReturnService $returnService */
        $returnService = app(ReturnService::class);
        $saleItemA = $this->sale1->items->first();

        $this->return1 = $returnService->createReturn([
            'sale_id' => $this->sale1->id,
            'refund_method' => 'cash',
            'reason' => 'عيب بسيط في العبوة',
            'items' => [
                ['sale_item_id' => $saleItemA->id, 'quantity' => 1],
            ],
        ], $this->cashier, $this->shift);

        // Active Expense: 100 EGP cash expense
        Expense::create([
            'user_id' => $this->cashier->id,
            'shift_id' => $this->shift->id,
            'category' => 'نظافة وضيافة',
            'amount' => 100.00,
            'payment_method' => 'cash',
            'description' => 'شراء مستلزمات نظافة',
            'status' => 'active',
        ]);

        // Cancelled Expense: 70 EGP (must be excluded from active calculations)
        Expense::create([
            'user_id' => $this->cashier->id,
            'shift_id' => $this->shift->id,
            'category' => 'صيانة',
            'amount' => 70.00,
            'payment_method' => 'cash',
            'description' => 'مصروف ملغي للاختبار',
            'status' => 'cancelled',
            'cancellation_reason' => 'تم الإلغاء بالخطأ',
        ]);
    }

    public function test_dashboard_can_be_rendered_with_accurate_metrics(): void
    {
        $response = $this->actingAs($this->owner)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('لوحة التحكم والمؤشرات');
        $response->assertSee('صافي المبيعات');
        $response->assertSee('مجمل الربح');
        $response->assertSee('صافي الربح');
        $response->assertSee('عطر فرنسي فاخر');
        $response->assertSee('ميدالية فضية'); // in low stock alerts

        // Mathematical verification:
        // Gross sales: 550 + 1000 = 1550.00
        // Returns: 200.00
        // Net sales: 1550 - 200 = 1350.00
        $response->assertSee('1,350.00');
    }

    public function test_sales_report_calculates_net_sales_after_completed_returns(): void
    {
        $response = $this->actingAs($this->owner)->get(route('reports.sales', ['period' => 'this_month']));

        $response->assertStatus(200);
        $response->assertSee('تقرير المبيعات والفواتير');

        /** @var ReportService $reportService */
        $reportService = app(ReportService::class);
        $range = $reportService->parseDateRange('this_month');
        $data = $reportService->getSalesReport($range);

        $this->assertEquals(2, $data['summary']['total_invoices']);
        $this->assertEquals(1600.00, $data['summary']['total_subtotal']); // 600 + 1000
        $this->assertEquals(50.00, $data['summary']['total_discount']);
        $this->assertEquals(1550.00, $data['summary']['gross_sales']); // 550 + 1000
        $this->assertEquals(200.00, $data['summary']['returns_total']);
        $this->assertEquals(1350.00, $data['summary']['net_sales']); // 1550 - 200
        $this->assertEquals(550.00, $data['summary']['cash_sales']);
        $this->assertEquals(1000.00, $data['summary']['vodafone_cash_sales']);
    }

    public function test_payment_methods_report_separates_physical_cash_from_electronic_methods(): void
    {
        /** @var ReportService $reportService */
        $reportService = app(ReportService::class);
        $range = $reportService->parseDateRange('this_month');
        $report = $reportService->getPaymentMethodsReport($range);

        // Cash: Gross 550, Refund 200 -> Net 350
        $cash = $report['methods']['cash'];
        $this->assertEquals(550.00, $cash['gross_amount']);
        $this->assertEquals(200.00, $cash['refunds_amount']);
        $this->assertEquals(350.00, $cash['net_amount']);
        $this->assertTrue($cash['is_cash']);

        // Vodafone Cash: Gross 1000, Refund 0 -> Net 1000
        $voda = $report['methods']['vodafone_cash'];
        $this->assertEquals(1000.00, $voda['gross_amount']);
        $this->assertEquals(0.00, $voda['refunds_amount']);
        $this->assertEquals(1000.00, $voda['net_amount']);
        $this->assertFalse($voda['is_cash']);

        // Physical Cash net vs Electronic Net
        $this->assertEquals(350.00, $report['physical_cash']['net_amount']);
        $this->assertEquals(1000.00, $report['electronic']['net_amount']);
        $this->assertEquals(1350.00, $report['totals']['net']);

        $response = $this->actingAs($this->owner)->get(route('reports.payment-methods'));
        $response->assertStatus(200);
        $response->assertSee('تقرير المبيعات حسب طرق الدفع');
        $response->assertSee('350.00');
        $response->assertSee('1,000.00');
    }

    public function test_product_sales_report_uses_snapshot_unit_cost_for_cogs_and_profit(): void
    {
        /** @var ReportService $reportService */
        $reportService = app(ReportService::class);
        $range = $reportService->parseDateRange('this_month');
        $report = $reportService->getProductSalesReport($range);

        // Product A:
        // Sold: 3 units * 200 = 600 revenue. Cost: 3 * 120 = 360.
        // Returned: 1 unit * 200 = 200 refunded. Returned Cost: 1 * 120 = 120.
        // Net Qty: 2 units.
        // Net Revenue: 400.00.
        // Net Cost (COGS): 360 - 120 = 240.00.
        // Gross Profit: 400 - 240 = 160.00.
        $itemA = collect($report['products']->items())->firstWhere('id', $this->productA->id);
        $this->assertNotNull($itemA);
        $this->assertEquals(3.0, (float) $itemA->qty_sold);
        $this->assertEquals(1.0, (float) $itemA->qty_returned);
        $this->assertEquals(2.0, (float) $itemA->net_qty);
        $this->assertEquals(400.00, (float) $itemA->net_revenue);
        $this->assertEquals(240.00, (float) $itemA->net_cost);
        $this->assertEquals(160.00, (float) $itemA->gross_profit);

        // Product B:
        // Sold: 2 units * 500 = 1000 revenue. Cost: 2 * 300 = 600.
        // Returned: 0.
        // Net Qty: 2 units.
        // Net Revenue: 1000.00. Net Cost: 600.00. Gross Profit: 400.00.
        $itemB = collect($report['products']->items())->firstWhere('id', $this->productB->id);
        $this->assertNotNull($itemB);
        $this->assertEquals(2.0, (float) $itemB->qty_sold);
        $this->assertEquals(0.0, (float) $itemB->qty_returned);
        $this->assertEquals(2.0, (float) $itemB->net_qty);
        $this->assertEquals(1000.00, (float) $itemB->net_revenue);
        $this->assertEquals(600.00, (float) $itemB->net_cost);
        $this->assertEquals(400.00, (float) $itemB->gross_profit);

        // Totals:
        // Net Revenue: 1400 (Note: line items subtotal without order discount). Net Cost: 240 + 600 = 840.
        $this->assertEquals(1400.00, $report['summary']['total_net_revenue']);
        $this->assertEquals(840.00, $report['summary']['total_net_cost']);
        $this->assertEquals(560.00, $report['summary']['total_gross_profit']);

        $response = $this->actingAs($this->owner)->get(route('reports.products'));
        $response->assertStatus(200);
        $response->assertSee('تقرير مبيعات المنتجات والأصناف');
    }

    public function test_employee_sales_report(): void
    {
        $response = $this->actingAs($this->owner)->get(route('reports.employees'));

        $response->assertStatus(200);
        $response->assertSee('تقرير مبيعات الموظفين والكاشير');
        $response->assertSee($this->cashier->name);

        /** @var ReportService $reportService */
        $reportService = app(ReportService::class);
        $range = $reportService->parseDateRange('this_month');
        $report = $reportService->getEmployeeSalesReport($range);

        $cashierStats = $report['employees']->firstWhere('id', $this->cashier->id);
        $this->assertNotNull($cashierStats);
        $this->assertEquals(2, $cashierStats->sales_count);
        $this->assertEquals(1550.00, (float) $cashierStats->gross_sales);
        $this->assertEquals(200.00, (float) $cashierStats->returns_total);
        $this->assertEquals(1350.00, (float) $cashierStats->net_sales);
    }

    public function test_shift_report_and_cash_reconciliation(): void
    {
        // Expected cash for current shift:
        // Opening cash: 300.00
        // Cash sales: 550.00
        // Cash returns: 200.00
        // Cash expenses: 100.00 (70 cancelled is excluded)
        // Expected = 300 + 550 - 200 - 100 = 550.00
        $this->assertEquals(550.00, $this->shift->calculated_expected_cash);

        $response = $this->actingAs($this->owner)->get(route('reports.shifts'));
        $response->assertStatus(200);
        $response->assertSee('تقرير الورديات ومطابقة الخزينة');
        $response->assertSee('550.00');
    }

    public function test_expense_report_excludes_cancelled_expenses(): void
    {
        /** @var ReportService $reportService */
        $reportService = app(ReportService::class);
        $range = $reportService->parseDateRange('this_month');
        $report = $reportService->getExpenseReport($range);

        // Only active expense (100.00) must be counted in active summary
        $this->assertEquals(1, $report['summary']['total_count']);
        $this->assertEquals(100.00, $report['summary']['total_amount']);
        $this->assertEquals(100.00, $report['summary']['cash_expenses']);
        $this->assertEquals(0.00, $report['summary']['electronic_expenses']);

        $response = $this->actingAs($this->owner)->get(route('reports.expenses'));
        $response->assertStatus(200);
        $response->assertSee('تقرير المصروفات والنفقات');
        $response->assertSee('نظافة وضيافة');
    }

    public function test_inventory_report_and_stock_movements(): void
    {
        $response = $this->actingAs($this->owner)->get(route('reports.inventory'));

        $response->assertStatus(200);
        $response->assertSee('تقرير حركة المخزون وتقييم المستودع');
        $response->assertSee('القيمة التكليفية للمخزون');
        $response->assertSee('القيمة البيعية للمخزون');
    }

    public function test_low_stock_report_detects_products_below_threshold(): void
    {
        $response = $this->actingAs($this->owner)->get(route('reports.low-stock'));

        $response->assertStatus(200);
        $response->assertSee('تقرير النواقص وحد الأمان للمخزون');
        $response->assertSee('ميدالية فضية'); // ProductLow has stock 1 <= min 5

        /** @var ReportService $reportService */
        $reportService = app(ReportService::class);
        $report = $reportService->getLowStockReport();

        $this->assertGreaterThanOrEqual(1, $report['summary']['total_alerts']);
    }

    public function test_profit_report_formal_income_statement_calculations(): void
    {
        /** @var ReportService $reportService */
        $reportService = app(ReportService::class);
        $range = $reportService->parseDateRange('this_month');
        $report = $reportService->getProfitReport($range);

        $inc = $report['income_statement'];

        // 1. Gross Subtotal: 600 (Sale1) + 1000 (Sale2) = 1600.00
        $this->assertEquals(1600.00, $inc['gross_subtotal']);
        // 2. Discounts: 50.00
        $this->assertEquals(50.00, $inc['total_discount']);
        // 3. Gross Sales: 1550.00
        $this->assertEquals(1550.00, $inc['gross_sales']);
        // 4. Returns: 200.00
        $this->assertEquals(200.00, $inc['total_returns']);
        // 5. Net Sales: 1550 - 200 = 1350.00
        $this->assertEquals(1350.00, $inc['net_sales']);

        // 6. Gross COGS:
        // Sale 1: 3 * 120 = 360
        // Sale 2: 2 * 300 = 600
        // Total Gross COGS = 960.00
        $this->assertEquals(960.00, $inc['gross_cogs']);

        // 7. Returned COGS:
        // Return 1: 1 * 120 = 120.00
        $this->assertEquals(120.00, $inc['returned_cogs']);

        // 8. Net COGS: 960 - 120 = 840.00
        $this->assertEquals(840.00, $inc['net_cogs']);

        // 9. Gross Profit: Net Sales (1350) - Net COGS (840) = 510.00
        $this->assertEquals(510.00, $inc['gross_profit']);

        // 10. Operating Expenses (Active only): 100.00
        $this->assertEquals(100.00, $inc['total_expenses']);

        // 11. Net Profit: Gross Profit (510) - Operating Expenses (100) = 410.00
        $this->assertEquals(410.00, $inc['net_profit']);

        // 12. Profit Margins:
        // Gross Margin: (510 / 1350) * 100 = 37.8%
        $this->assertEquals(37.8, $inc['gross_profit_margin']);
        // Net Margin: (410 / 1350) * 100 = 30.4%
        $this->assertEquals(30.4, $inc['net_profit_margin']);

        // 13. Cash Flow Reconciliation:
        // Cash Sales (550) - Cash Returns (200) - Cash Expenses (100) = 250.00
        $this->assertEquals(250.00, $report['cash_flow']['net_cash_flow']);

        // Electronic Flow:
        // Vodafone Sales (1000) - 0 - 0 = 1000.00
        $this->assertEquals(1000.00, $report['cash_flow']['net_electronic_flow']);

        $response = $this->actingAs($this->owner)->get(route('reports.profit'));
        $response->assertStatus(200);
        $response->assertSee('تقرير الأرباح والخسائر وقائمة الدخل');
        $response->assertSee('510.00'); // Gross Profit
        $response->assertSee('410.00'); // Net Profit
    }

    public function test_reports_overview_hub_can_be_rendered(): void
    {
        $response = $this->actingAs($this->owner)->get(route('reports.index'));

        $response->assertStatus(200);
        $response->assertSee('مركز التقارير والإحصائيات');
        $response->assertSee('تقرير المبيعات والفواتير');
        $response->assertSee('المبيعات حسب طرق الدفع');
        $response->assertSee('تقرير الأرباح والخسائر');
    }
}

