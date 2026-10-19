<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Expense;
use App\Models\Note;
use App\Models\Product;
use App\Models\ReturnItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use App\Models\Shift;
use App\Models\Subcategory;
use App\Models\User;
use App\Services\PosService;
use App\Services\ReportService;
use App\Services\ReturnService;
use App\Services\ShiftService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected User $employee1;
    protected User $employee2;
    protected Category $category;
    protected Subcategory $subcategory;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->owner()->create([
            'name' => 'مالك المتجر',
            'username' => 'owner_user',
            'is_active' => true,
        ]);

        $this->employee1 = User::factory()->employee()->create([
            'name' => 'الموظف الأول',
            'username' => 'employee_one',
            'is_active' => true,
        ]);

        $this->employee2 = User::factory()->employee()->create([
            'name' => 'الموظف الثاني',
            'username' => 'employee_two',
            'is_active' => true,
        ]);

        $this->category = Category::create([
            'name' => 'ساعات وهدايا',
            'is_active' => true,
        ]);

        $this->subcategory = Subcategory::create([
            'name' => 'ساعات يد',
            'category_id' => $this->category->id,
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'name' => 'ساعة رولكس كلاسيك',
            'subcategory_id' => $this->subcategory->id,
            'purchase_price' => 500.00,
            'selling_price' => 800.00,
            'stock_quantity' => 20,
            'minimum_stock' => 5,
            'is_active' => true,
        ]);
    }

    /**
     * Requirement 1 & 2:
     * Owner can access dashboard and all reports.
     */
    public function test_owner_can_access_dashboard_and_all_reports(): void
    {
        $this->actingAs($this->owner);

        $this->get(route('dashboard'))->assertStatus(200);
        $this->get(route('reports.index'))->assertStatus(200);
        $this->get(route('reports.sales'))->assertStatus(200);
        $this->get(route('reports.payment-methods'))->assertStatus(200);
        $this->get(route('reports.products'))->assertStatus(200);
        $this->get(route('reports.employees'))->assertStatus(200);
        $this->get(route('reports.shifts'))->assertStatus(200);
        $this->get(route('reports.expenses'))->assertStatus(200);
        $this->get(route('reports.inventory'))->assertStatus(200);
        $this->get(route('reports.low-stock'))->assertStatus(200);
        $this->get(route('reports.profit'))->assertStatus(200);
    }

    /**
     * Requirement 2:
     * Employee gets 403 Forbidden on dashboard and all reports.
     */
    public function test_employee_gets_403_on_dashboard_and_reports(): void
    {
        $this->actingAs($this->employee1);

        $this->get(route('dashboard'))->assertStatus(403);
        $this->get(route('reports.index'))->assertStatus(403);
        $this->get(route('reports.sales'))->assertStatus(403);
        $this->get(route('reports.payment-methods'))->assertStatus(403);
        $this->get(route('reports.products'))->assertStatus(403);
        $this->get(route('reports.employees'))->assertStatus(403);
        $this->get(route('reports.shifts'))->assertStatus(403);
        $this->get(route('reports.expenses'))->assertStatus(403);
        $this->get(route('reports.inventory'))->assertStatus(403);
        $this->get(route('reports.low-stock'))->assertStatus(403);
        $this->get(route('reports.profit'))->assertStatus(403);
    }

    /**
     * Requirement 15 & 16:
     * Employee cannot access user management endpoints.
     */
    public function test_employee_cannot_access_user_management(): void
    {
        $this->actingAs($this->employee1);

        $this->get(route('users.index'))->assertStatus(403);
        $this->get(route('users.create'))->assertStatus(403);
        $this->post(route('users.store'), [
            'name' => 'هاكر',
            'username' => 'hacker',
            'password' => 'secret123',
            'role' => 'owner',
            'is_active' => true,
        ])->assertStatus(403);

        $this->get(route('users.edit', $this->employee2))->assertStatus(403);
        $this->put(route('users.update', $this->employee2), [
            'name' => 'تعديل غير مصرح',
            'username' => 'unauthorized',
            'role' => 'owner',
            'is_active' => true,
        ])->assertStatus(403);

        $this->patch(route('users.toggle-status', $this->employee2))->assertStatus(403);
        $this->delete(route('users.destroy', $this->employee2))->assertStatus(403);
    }

    /**
     * Owner can access and manage users.
     */
    public function test_owner_can_manage_users(): void
    {
        $this->actingAs($this->owner);

        $this->get(route('users.index'))->assertStatus(200);
        $this->get(route('users.create'))->assertStatus(200);
        $this->get(route('users.edit', $this->employee1))->assertStatus(200);

        $response = $this->post(route('users.store'), [
            'name' => 'موظف جديد',
            'username' => 'new_cashier',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'role' => 'employee',
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', ['username' => 'new_cashier']);
    }

    /**
     * Requirement 15:
     * Employee cannot access activity logs.
     */
    public function test_employee_cannot_access_global_activity_logs(): void
    {
        $this->actingAs($this->employee1);

        $this->get(route('activity-logs.index'))->assertStatus(403);

        $log = ActivityLog::create([
            'user_id' => $this->owner->id,
            'action' => 'test_action',
            'description' => 'سجل تجريبي',
        ]);

        $this->get(route('activity-logs.show', $log))->assertStatus(403);
    }

    /**
     * Owner can access activity logs.
     */
    public function test_owner_can_access_activity_logs(): void
    {
        $this->actingAs($this->owner);

        $this->get(route('activity-logs.index'))->assertStatus(200);
    }

    /**
     * Requirement 3 & 4:
     * Employee has shared access to products, categories, subcategories, inventory, and adjustments.
     */
    public function test_employee_has_shared_access_to_products_categories_and_inventory(): void
    {
        $this->actingAs($this->employee1);

        // Catalog viewing & creation
        $this->get(route('products.index'))->assertStatus(200);
        $this->get(route('categories.index'))->assertStatus(200);
        $this->get(route('subcategories.index'))->assertStatus(200);

        $prodResponse = $this->post(route('products.store'), [
            'name' => 'محفظة جلدية طبيعية',
            'subcategory_id' => $this->subcategory->id,
            'purchase_price' => 100,
            'selling_price' => 200,
            'stock_quantity' => 15,
            'minimum_stock' => 3,
            'is_active' => 1,
        ]);
        $prodResponse->assertRedirect(route('products.index'));
        $this->assertDatabaseHas('products', ['name' => 'محفظة جلدية طبيعية']);

        // Inventory & stock adjustments
        $this->get(route('inventory.index'))->assertStatus(200);
        $this->get(route('inventory.movements'))->assertStatus(200);

        $adjustResponse = $this->post(route('inventory.adjustments.store'), [
            'product_id' => $this->product->id,
            'adjustment_mode' => 'relative',
            'direction' => 'add',
            'quantity' => 5,
            'reason' => 'استلام شحنة إضافية بالمحل',
        ]);
        $adjustResponse->assertRedirect(route('inventory.index'));
        $this->assertEquals(25, $this->product->fresh()->stock_quantity);
    }

    /**
     * Requirement 5 & 6:
     * Sales Isolation: Employee only sees their own invoices; 403 on other employees' invoices.
     */
    public function test_sales_isolation_between_employees(): void
    {
        /** @var PosService $posService */
        $posService = app(PosService::class);
        /** @var ShiftService $shiftService */
        $shiftService = app(ShiftService::class);

        $shift1 = $shiftService->openShift($this->employee1, 200.00);
        $sale1 = $posService->createSale([
            'payment_method' => 'cash',
            'discount' => 0,
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 1],
            ],
        ], $this->employee1, $shift1);

        $shift2 = $shiftService->openShift($this->employee2, 300.00);
        $sale2 = $posService->createSale([
            'payment_method' => 'cash',
            'discount' => 0,
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 2],
            ],
        ], $this->employee2, $shift2);

        // 1. Employee 1 in sales index
        $response1 = $this->actingAs($this->employee1)->get(route('sales.index'));
        $response1->assertStatus(200);
        $response1->assertSee($sale1->invoice_number);
        $response1->assertDontSee($sale2->invoice_number);

        // 2. Employee 2 in sales index
        $response2 = $this->actingAs($this->employee2)->get(route('sales.index'));
        $response2->assertStatus(200);
        $response2->assertSee($sale2->invoice_number);
        $response2->assertDontSee($sale1->invoice_number);

        // 3. Direct show / print / cancel authorization
        $this->actingAs($this->employee1)->get(route('sales.show', $sale1))->assertStatus(200);
        $this->actingAs($this->employee1)->get(route('sales.print', $sale1))->assertStatus(200);

        $this->actingAs($this->employee1)->get(route('sales.show', $sale2))->assertStatus(403);
        $this->actingAs($this->employee1)->get(route('sales.print', $sale2))->assertStatus(403);
        $this->actingAs($this->employee1)->patch(route('sales.cancel', $sale2), [
            'reason' => 'محاولة إلغاء غير مصرح بها',
        ])->assertStatus(403);

        // 4. Owner has full store-wide visibility
        $ownerResponse = $this->actingAs($this->owner)->get(route('sales.index'));
        $ownerResponse->assertStatus(200);
        $ownerResponse->assertSee($sale1->invoice_number);
        $ownerResponse->assertSee($sale2->invoice_number);

        $this->actingAs($this->owner)->get(route('sales.show', $sale1))->assertStatus(200);
        $this->actingAs($this->owner)->get(route('sales.show', $sale2))->assertStatus(200);
    }

    /**
     * Requirement 7 & 8:
     * Returns Authorization:
     * Employee can create a return for ANY invoice (regardless of which employee sold it).
     * The created return strictly belongs to the authenticated employee.
     */
    public function test_employee_can_create_return_against_another_employees_sale_and_owns_the_return(): void
    {
        /** @var PosService $posService */
        $posService = app(PosService::class);
        /** @var ShiftService $shiftService */
        $shiftService = app(ShiftService::class);

        $shift1 = $shiftService->openShift($this->employee1, 200.00);
        $sale1 = $posService->createSale([
            'payment_method' => 'cash',
            'discount' => 0,
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 2],
            ],
        ], $this->employee1, $shift1);

        $shift2 = $shiftService->openShift($this->employee2, 300.00);

        // 1. Employee 2 searches Employee 1's invoice via AJAX -> 200, success true
        $ajaxResponse = $this->actingAs($this->employee2)->getJson(
            route('returns.search-invoice', ['search' => $sale1->invoice_number])
        );
        $ajaxResponse->assertStatus(200);
        $ajaxResponse->assertJsonPath('success', true);
        $ajaxResponse->assertJsonPath('sale.invoice_number', $sale1->invoice_number);

        // 2. Employee 2 prepares return via GET query string -> 200, no warning
        $createResponse = $this->actingAs($this->employee2)->get(
            route('returns.create', ['invoice' => $sale1->invoice_number])
        );
        $createResponse->assertStatus(200);
        $createResponse->assertSessionMissing('warning');
        $createResponse->assertSee($sale1->invoice_number);

        // 3. Employee 2 posts return for Employee 1's sale -> successfully creates return owned by Employee 2
        $saleItem = $sale1->items->first();
        $storeResponse = $this->actingAs($this->employee2)->post(route('returns.store'), [
            'sale_id' => $sale1->id,
            'refund_method' => 'cash',
            'reason' => 'إرجاع صنف من فاتورة زميل في العمل',
            'items' => [
                ['sale_item_id' => $saleItem->id, 'quantity' => 1],
            ],
        ]);
        $storeResponse->assertSessionHasNoErrors();
        $storeResponse->assertRedirect();

        // 4. Verify return ownership is strictly Employee 2
        $this->assertDatabaseHas('sale_returns', [
            'sale_id' => $sale1->id,
            'user_id' => $this->employee2->id,
            'status' => 'approved',
        ]);

        $createdReturn = SaleReturn::where('sale_id', $sale1->id)->where('user_id', $this->employee2->id)->firstOrFail();

        // 5. Employee 1 cannot see this return in their returns list
        $emp1Index = $this->actingAs($this->employee1)->get(route('returns.index'));
        $emp1Index->assertStatus(200);
        $emp1Index->assertDontSee($createdReturn->return_number);

        // 6. Employee 1 cannot access it via direct URL
        $this->actingAs($this->employee1)->get(route('returns.show', $createdReturn))->assertStatus(403);

        // 7. Employee 2 sees their own created return in list and can view details
        $emp2Index = $this->actingAs($this->employee2)->get(route('returns.index'));
        $emp2Index->assertStatus(200);
        $emp2Index->assertSee($createdReturn->return_number);

        $this->actingAs($this->employee2)->get(route('returns.show', $createdReturn))->assertStatus(200);

        // 8. Owner sees the return
        $ownerIndex = $this->actingAs($this->owner)->get(route('returns.index'));
        $ownerIndex->assertStatus(200);
        $ownerIndex->assertSee($createdReturn->return_number);
    }

    /**
     * Requirement 8 & 9:
     * Returns Isolation: Employee only sees/manages their own returns; 403 on other employees' returns.
     */
    public function test_returns_isolation_between_employees(): void
    {
        /** @var PosService $posService */
        $posService = app(PosService::class);
        /** @var ShiftService $shiftService */
        $shiftService = app(ShiftService::class);
        /** @var ReturnService $returnService */
        $returnService = app(ReturnService::class);

        $shift1 = $shiftService->openShift($this->employee1, 200.00);
        $sale1 = $posService->createSale([
            'payment_method' => 'cash',
            'discount' => 0,
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 2],
            ],
        ], $this->employee1, $shift1);

        $return1 = $returnService->createReturn([
            'sale_id' => $sale1->id,
            'refund_method' => 'cash',
            'reason' => 'مرتجع الموظف الأول',
            'items' => [
                ['sale_item_id' => $sale1->items->first()->id, 'quantity' => 1],
            ],
        ], $this->employee1, $shift1);

        $shift2 = $shiftService->openShift($this->employee2, 300.00);
        $sale2 = $posService->createSale([
            'payment_method' => 'cash',
            'discount' => 0,
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 2],
            ],
        ], $this->employee2, $shift2);

        $return2 = $returnService->createReturn([
            'sale_id' => $sale2->id,
            'refund_method' => 'cash',
            'reason' => 'مرتجع الموظف الثاني',
            'items' => [
                ['sale_item_id' => $sale2->items->first()->id, 'quantity' => 1],
            ],
        ], $this->employee2, $shift2);

        // Employee 1 views returns list
        $response1 = $this->actingAs($this->employee1)->get(route('returns.index'));
        $response1->assertStatus(200);
        $response1->assertSee($return1->return_number);
        $response1->assertDontSee($return2->return_number);

        // Direct return show / print / cancel
        $this->actingAs($this->employee1)->get(route('returns.show', $return1))->assertStatus(200);
        $this->actingAs($this->employee1)->get(route('returns.print', $return1))->assertStatus(200);

        $this->actingAs($this->employee1)->get(route('returns.show', $return2))->assertStatus(403);
        $this->actingAs($this->employee1)->get(route('returns.print', $return2))->assertStatus(403);
        $this->actingAs($this->employee1)->patch(route('returns.cancel', $return2), [
            'reason' => 'محاولة إلغاء غير مصرحة',
        ])->assertStatus(403);

        // Owner can view all returns
        $ownerResponse = $this->actingAs($this->owner)->get(route('returns.index'));
        $ownerResponse->assertStatus(200);
        $ownerResponse->assertSee($return1->return_number);
        $ownerResponse->assertSee($return2->return_number);
    }

    /**
     * Requirement 9 & 10:
     * Shift Isolation: Employee only sees their own shifts; 403 on others' shifts.
     */
    public function test_shifts_isolation_between_employees(): void
    {
        /** @var ShiftService $shiftService */
        $shiftService = app(ShiftService::class);

        $shift1 = $shiftService->openShift($this->employee1, 150.00);
        $shift2 = $shiftService->openShift($this->employee2, 250.00);

        // In shifts list
        $response1 = $this->actingAs($this->employee1)->get(route('shifts.index'));
        $response1->assertStatus(200);
        $response1->assertSee("#{$shift1->id}");
        $response1->assertSee($this->employee1->name);
        $response1->assertDontSee("#{$shift2->id}");
        $response1->assertDontSee($this->employee2->name);

        // Direct show / print / close
        $this->actingAs($this->employee1)->get(route('shifts.show', $shift1))->assertStatus(200);
        $this->actingAs($this->employee1)->get(route('shifts.print', $shift1))->assertStatus(200);

        $this->actingAs($this->employee1)->get(route('shifts.show', $shift2))->assertStatus(403);
        $this->actingAs($this->employee1)->get(route('shifts.print', $shift2))->assertStatus(403);
        $this->actingAs($this->employee1)->post(route('shifts.close', $shift2), [
            'closing_cash' => 250.00,
        ])->assertStatus(403);

        // Owner can access all shifts
        $ownerResponse = $this->actingAs($this->owner)->get(route('shifts.index'));
        $ownerResponse->assertStatus(200);
        $ownerResponse->assertSee("#{$shift1->id}");
        $ownerResponse->assertSee("#{$shift2->id}");
    }

    /**
     * Requirement 10, 11 & 12:
     * Expense Isolation: Employee can create, view, and edit own older expenses, but 403 on others.
     */
    public function test_expenses_isolation_and_employee_can_edit_own_older_expenses(): void
    {
        $expense1 = Expense::create([
            'user_id' => $this->employee1->id,
            'category' => 'بوفيه وضيافة',
            'amount' => 45.00,
            'payment_method' => 'cash',
            'description' => 'شاي وسكر للمحل',
            'status' => 'active',
        ]);

        $expense2 = Expense::create([
            'user_id' => $this->employee2->id,
            'category' => 'كهرباء وإنارة',
            'amount' => 120.00,
            'payment_method' => 'cash',
            'description' => 'لمبات إضاءة',
            'status' => 'active',
        ]);

        // Employee 1 views expenses index
        $response1 = $this->actingAs($this->employee1)->get(route('expenses.index'));
        $response1->assertStatus(200);
        $response1->assertSee('شاي وسكر للمحل');
        $response1->assertDontSee('لمبات إضاءة');

        // Employee 1 can view, edit, and update their own older expense
        $this->actingAs($this->employee1)->get(route('expenses.show', $expense1))->assertStatus(200);
        $this->actingAs($this->employee1)->get(route('expenses.edit', $expense1))->assertStatus(200);

        $updateResponse = $this->actingAs($this->employee1)->put(route('expenses.update', $expense1), [
            'category' => 'بوفيه وضيافة',
            'amount' => 50.00,
            'payment_method' => 'cash',
            'description' => 'شاي وسكر وبن للمحل',
        ]);
        $updateResponse->assertRedirect(route('expenses.index'));
        $this->assertEquals(50.00, $expense1->fresh()->amount);

        // Employee 1 gets 403 when trying to access or modify Employee 2's expense
        $this->actingAs($this->employee1)->get(route('expenses.show', $expense2))->assertStatus(403);
        $this->actingAs($this->employee1)->get(route('expenses.edit', $expense2))->assertStatus(403);
        $this->actingAs($this->employee1)->put(route('expenses.update', $expense2), [
            'category' => 'كهرباء وإنارة',
            'amount' => 999.00,
            'payment_method' => 'cash',
        ])->assertStatus(403);
        $this->actingAs($this->employee1)->patch(route('expenses.cancel', $expense2), [
            'reason' => 'محاولة إلغاء غير مصرح بها',
        ])->assertStatus(403);

        // Owner can view all expenses
        $ownerResponse = $this->actingAs($this->owner)->get(route('expenses.index'));
        $ownerResponse->assertStatus(200);
        $ownerResponse->assertSee('شاي وسكر وبن للمحل');
        $ownerResponse->assertSee('لمبات إضاءة');
    }

    /**
     * Requirement 17:
     * Changing IDs or submitting another user's ID cannot bypass ownership checks.
     */
    public function test_employee_cannot_link_expense_to_another_employees_shift(): void
    {
        /** @var ShiftService $shiftService */
        $shiftService = app(ShiftService::class);

        $shift2 = $shiftService->openShift($this->employee2, 300.00);

        $response = $this->actingAs($this->employee1)->post(route('expenses.store'), [
            'category' => 'صيانة',
            'amount' => 50.00,
            'payment_method' => 'cash',
            'description' => 'محاولة ربط وردية موظف آخر',
            'shift_id' => $shift2->id,
        ]);

        $response->assertStatus(403);
    }

    /**
     * Requirement 10 & 14:
     * Note amount does not affect cash balance or financial reports.
     */
    public function test_note_amount_does_not_affect_cash_or_financial_reports(): void
    {
        /** @var PosService $posService */
        $posService = app(PosService::class);
        /** @var ShiftService $shiftService */
        $shiftService = app(ShiftService::class);
        /** @var ReportService $reportService */
        $reportService = app(ReportService::class);

        $shift = $shiftService->openShift($this->employee1, 100.00);

        // Create a Note with a large amount (e.g. 50,000 EGP)
        Note::create([
            'user_id' => $this->employee1->id,
            'title' => 'تذكير سداد شيك مؤجل للمورد',
            'content' => 'مبلغ 50,000 ج.م مستحق نهاية الشهر',
            'amount' => 50000.00,
            'status' => 'open',
        ]);

        // Shift cash summary must NOT include note amount
        $shiftSummary = $shiftService->getShiftFinancialSummary($shift);
        $this->assertEquals(100.00, $shiftSummary['reconciliation']['expected_cash']);
        $this->assertEquals(0.00, $shiftSummary['sales']['cash']);
        $this->assertEquals(0.00, $shiftSummary['expenses']['cash']);

        // Reports overview must NOT count note amount into sales or revenue
        $dateRange = $reportService->parseDateRange('today');
        $dashboardData = $reportService->getDashboardData($dateRange);
        $this->assertEquals(0.00, $dashboardData['period']['net_sales']);
        $this->assertEquals(0.00, $dashboardData['period']['expenses_total']);
    }

    /**
     * Root URL Redirection based on role.
     */
    public function test_root_url_redirection_based_on_role(): void
    {
        // Guest -> redirect to dashboard (which redirects to login)
        $this->get('/')->assertRedirect('/dashboard');

        // Owner -> redirect to dashboard
        $this->actingAs($this->owner)->get('/')->assertRedirect(route('dashboard'));

        // Employee -> redirect to POS terminal
        $this->actingAs($this->employee1)->get('/')->assertRedirect(route('pos.index'));
    }

    /**
     * Sidebar navigation items visibility based on role.
     */
    public function test_sidebar_navigation_visibility_by_role(): void
    {
        // Owner sees Dashboard, Users, Reports, Activity Logs
        $ownerView = $this->actingAs($this->owner)->get(route('products.index'));
        $ownerView->assertStatus(200);
        $ownerView->assertSee('لوحة التحكم');
        $ownerView->assertSee('إدارة المستخدمين');
        $ownerView->assertSee('تقارير النظام');
        $ownerView->assertSee('سجل التدقيق والنشاطات');

        // Employee does NOT see Dashboard, Users, Reports, Activity Logs
        $employeeView = $this->actingAs($this->employee1)->get(route('products.index'));
        $employeeView->assertStatus(200);
        $employeeView->assertDontSee('لوحة التحكم');
        $employeeView->assertDontSee('إدارة المستخدمين');
        $employeeView->assertDontSee('تقارير النظام');
        $employeeView->assertDontSee('سجل التدقيق والنشاطات');
        // But Employee sees POS, Products, Inventory, Sales, Returns, Shifts, Expenses, Notes
        $employeeView->assertSee('نقطة البيع');
        $employeeView->assertSee('المنتجات');
        $employeeView->assertSee('المخزون والجرد');
        $employeeView->assertSee('فواتير المبيعات');
        $employeeView->assertSee('مرتجعات المبيعات');
        $employeeView->assertSee('الورديات والخزينة');
        $employeeView->assertSee('المصروفات');
        $employeeView->assertSee('الملاحظات والتنبيهات');
    }
}
