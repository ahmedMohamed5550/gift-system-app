<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\Shift;
use App\Models\Subcategory;
use App\Models\User;
use App\Services\InventoryService;
use App\Services\PosService;
use App\Services\ReturnService;
use App\Services\ShiftService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuditAndSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected User $employee;
    protected Category $category;
    protected Subcategory $subcategory;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::create([
            'name' => 'مالك المتجر',
            'username' => 'owner_audit',
            'password' => Hash::make('secret123'),
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->employee = User::create([
            'name' => 'موظف كاشير',
            'username' => 'employee_audit',
            'password' => Hash::make('secret123'),
            'role' => 'employee',
            'is_active' => true,
        ]);

        $this->category = Category::create([
            'name' => 'هدايا فاخرة',
            'is_active' => true,
        ]);

        $this->subcategory = Subcategory::create([
            'category_id' => $this->category->id,
            'name' => 'ساعات رجالي',
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'subcategory_id' => $this->subcategory->id,
            'name' => 'ساعة يد كلاسيك',
            'purchase_price' => 100.00,
            'selling_price' => 250.00,
            'stock_quantity' => 50,
            'minimum_stock' => 5,
            'is_active' => true,
        ]);
    }

    /**
     * Test 1: User changes are logged in ActivityLog.
     */
    public function test_user_changes_are_logged_to_activity_logs(): void
    {
        $this->actingAs($this->owner);

        // 1. Create user
        $response = $this->post(route('users.store'), [
            'name' => 'محمد كاشير',
            'username' => 'cashier_mohamed',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'employee',
            'is_active' => 1,
        ]);
        $response->assertRedirect(route('users.index'));

        $newUser = User::where('username', 'cashier_mohamed')->first();
        $this->assertNotNull($newUser);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'user_created',
            'subject_type' => User::class,
            'subject_id' => $newUser->id,
        ]);

        // 2. Update user
        $this->put(route('users.update', $newUser), [
            'name' => 'محمد كاشير معدل',
            'username' => 'cashier_mohamed',
            'role' => 'employee',
            'is_active' => 1,
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'user_updated',
            'subject_type' => User::class,
            'subject_id' => $newUser->id,
        ]);

        // 3. Toggle status
        $this->patch(route('users.toggle-status', $newUser));
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'user_status_toggled',
            'subject_type' => User::class,
            'subject_id' => $newUser->id,
        ]);

        // 4. Delete user
        $this->delete(route('users.destroy', $newUser));
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'user_deleted',
            'subject_type' => User::class,
            'subject_id' => $newUser->id,
        ]);
    }

    /**
     * Test 2: Product and price changes are logged to ActivityLog.
     */
    public function test_product_and_price_changes_are_logged_to_activity_logs(): void
    {
        $this->actingAs($this->owner);

        // 1. Create Product
        $this->post(route('products.store'), [
            'name' => 'عطر زهور فاخر',
            'subcategory_id' => $this->subcategory->id,
            'description' => 'عطر فرنسي راقي',
            'purchase_price' => 200.00,
            'selling_price' => 450.00,
            'stock_quantity' => 20,
            'minimum_stock' => 3,
            'is_active' => 1,
        ]);

        $newProduct = Product::where('name', 'عطر زهور فاخر')->first();
        $this->assertNotNull($newProduct);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'product_created',
            'subject_type' => Product::class,
            'subject_id' => $newProduct->id,
        ]);

        // 2. Update Product Price
        $this->put(route('products.update', $newProduct), [
            'name' => 'عطر زهور فاخر',
            'subcategory_id' => $this->subcategory->id,
            'description' => 'عطر فرنسي راقي',
            'purchase_price' => 220.00,
            'selling_price' => 500.00,
            'stock_quantity' => 20,
            'minimum_stock' => 3,
            'is_active' => 1,
        ]);

        $log = ActivityLog::where('action', 'product_updated')
            ->where('subject_id', $newProduct->id)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(450.00, $log->old_values['selling_price']);
        $this->assertEquals(500.00, $log->new_values['selling_price']);
    }

    /**
     * Test 3: Stock manual adjustment is logged to ActivityLog.
     */
    public function test_stock_adjustment_is_logged_to_activity_logs(): void
    {
        /** @var InventoryService $inventoryService */
        $inventoryService = app(InventoryService::class);

        $inventoryService->adjustStock(
            product: $this->product,
            adjustmentMode: 'relative',
            value: 10,
            direction: 'add',
            reason: 'توريد مخزني إضافي وجرد فعلي',
            userId: $this->owner->id
        );

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'stock_adjusted',
            'subject_type' => Product::class,
            'subject_id' => $this->product->id,
            'user_id' => $this->owner->id,
        ]);

        $log = ActivityLog::where('action', 'stock_adjusted')->first();
        $this->assertEquals(50, $log->old_values['stock_quantity']);
        $this->assertEquals(60, $log->new_values['stock_quantity']);
    }

    /**
     * Test 4: Shift open and close are logged to ActivityLog.
     */
    public function test_shift_open_and_close_are_logged_to_activity_logs(): void
    {
        /** @var ShiftService $shiftService */
        $shiftService = app(ShiftService::class);

        $shift = $shiftService->openShift(
            user: $this->employee,
            openingCash: 500.00,
            notes: 'افتتاح وردية الصباح'
        );

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'shift_opened',
            'subject_type' => Shift::class,
            'subject_id' => $shift->id,
            'user_id' => $this->employee->id,
        ]);

        $shiftService->closeShift(
            shift: $shift,
            actualClosingCash: 500.00,
            notes: 'إغلاق ومطابقة تامة'
        );

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'shift_closed',
            'subject_type' => Shift::class,
            'subject_id' => $shift->id,
        ]);
    }

    /**
     * Test 5: Expense creation, update, and cancellation are logged to ActivityLog.
     */
    public function test_expense_lifecycle_is_logged_to_activity_logs(): void
    {
        $this->actingAs($this->owner);

        // 1. Create expense
        $response = $this->post(route('expenses.store'), [
            'category' => 'نظافة ومستلزمات عامة',
            'amount' => 150.00,
            'payment_method' => 'cash',
            'description' => 'شراء منظفات للمحل',
        ]);
        $response->assertRedirect(route('expenses.index'));

        $expense = Expense::where('description', 'شراء منظفات للمحل')->first();
        $this->assertNotNull($expense);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'expense_created',
            'subject_type' => Expense::class,
            'subject_id' => $expense->id,
        ]);

        // 2. Update expense
        $this->put(route('expenses.update', $expense), [
            'category' => 'نظافة ومستلزمات عامة',
            'amount' => 170.00,
            'payment_method' => 'cash',
            'description' => 'شراء منظفات ومناشف للمحل',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'expense_updated',
            'subject_type' => Expense::class,
            'subject_id' => $expense->id,
        ]);

        // 3. Cancel expense
        $this->patch(route('expenses.cancel', $expense), [
            'reason' => 'تم استرداد المبلغ من البائع',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'expense_cancelled',
            'subject_type' => Expense::class,
            'subject_id' => $expense->id,
        ]);
    }

    /**
     * Test 6: Sales and returns are logged to ActivityLog.
     */
    public function test_sale_and_return_lifecycle_are_logged_to_activity_logs(): void
    {
        /** @var ShiftService $shiftService */
        $shiftService = app(ShiftService::class);
        $shift = $shiftService->openShift($this->employee, 300.00);

        /** @var PosService $posService */
        $posService = app(PosService::class);

        // 1. Create Sale
        $sale = $posService->createSale(
            data: [
                'items' => [
                    ['product_id' => $this->product->id, 'quantity' => 2],
                ],
                'payment_method' => 'cash',
                'discount' => 0,
            ],
            user: $this->employee,
            shift: $shift
        );

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'sale_created',
            'subject_type' => Sale::class,
            'subject_id' => $sale->id,
        ]);

        // 2. Create Return
        /** @var ReturnService $returnService */
        $returnService = app(ReturnService::class);

        $saleItem = $sale->items->first();
        $return = $returnService->createReturn(
            data: [
                'sale_id' => $sale->id,
                'items' => [
                    ['sale_item_id' => $saleItem->id, 'quantity' => 1],
                ],
                'reason' => 'تبديل مقاس',
                'refund_method' => 'cash',
            ],
            user: $this->employee,
            shift: $shift
        );

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'return_created',
            'subject_type' => SaleReturn::class,
            'subject_id' => $return->id,
        ]);

        // 3. Cancel Return
        $returnService->cancelReturn($return, $this->employee, 'إلغاء المرتجع بناء على رغبة العميل');
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'return_cancelled',
            'subject_type' => SaleReturn::class,
            'subject_id' => $return->id,
        ]);

        // 4. Cancel Sale
        $posService->cancelSale($sale, $this->employee, 'إلغاء الفاتورة');
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'sale_cancelled',
            'subject_type' => Sale::class,
            'subject_id' => $sale->id,
        ]);
    }

    /**
     * Test 7: Ordinary users (employees) cannot access activity logs (403 Forbidden).
     */
    public function test_ordinary_employee_cannot_access_activity_logs(): void
    {
        $response = $this->actingAs($this->employee)->get(route('activity-logs.index'));
        $response->assertStatus(403);
    }

    /**
     * Test 8: Owner can access and filter activity logs.
     */
    public function test_owner_can_access_and_filter_activity_logs(): void
    {
        // Generate an audit log
        ActivityLog::create([
            'user_id' => $this->owner->id,
            'action' => 'product_created',
            'subject_type' => Product::class,
            'subject_id' => $this->product->id,
            'description' => 'سجل تجريبي لفحص الفلترة',
        ]);

        $response = $this->actingAs($this->owner)->get(route('activity-logs.index'));
        $response->assertStatus(200);
        $response->assertSee('سجل العمليات والتدقيق');
        $response->assertSee('سجل تجريبي لفحص الفلترة');

        // Test filter by action
        $filteredResponse = $this->actingAs($this->owner)->get(route('activity-logs.index', [
            'action' => 'product_created',
        ]));
        $filteredResponse->assertStatus(200);
        $filteredResponse->assertSee('سجل تجريبي لفحص الفلترة');
    }

    /**
     * Test 9: Deactivated user is immediately logged out by EnsureUserIsActive middleware.
     */
    public function test_deactivated_user_is_logged_out_by_middleware(): void
    {
        $this->actingAs($this->employee);

        $response = $this->get(route('pos.index'));
        $response->assertStatus(200);

        // Deactivate user in database
        $this->employee->update(['is_active' => false]);

        $nextResponse = $this->get(route('pos.index'));
        $nextResponse->assertRedirect(route('login'));
        $this->assertGuest();
    }

    /**
     * Test 10: Historical snapshot unit_cost and unit_price remain unchanged when product prices change.
     */
    public function test_historical_prices_and_costs_remain_unchanged_when_product_prices_are_edited(): void
    {
        /** @var ShiftService $shiftService */
        $shiftService = app(ShiftService::class);
        $shift = $shiftService->openShift($this->owner, 100.00);

        /** @var PosService $posService */
        $posService = app(PosService::class);

        $sale = $posService->createSale(
            data: [
                'items' => [
                    ['product_id' => $this->product->id, 'quantity' => 1],
                ],
                'payment_method' => 'cash',
                'discount' => 0,
            ],
            user: $this->owner,
            shift: $shift
        );

        $saleItem = $sale->items->first();
        $this->assertEquals(250.00, $saleItem->unit_price);
        $this->assertEquals(100.00, $saleItem->unit_cost);

        // Now change product prices drastically
        $this->product->update([
            'selling_price' => 999.00,
            'purchase_price' => 700.00,
        ]);

        // Refresh sale item and verify it is completely unaffected
        $saleItem->refresh();
        $this->assertEquals(250.00, $saleItem->unit_price);
        $this->assertEquals(100.00, $saleItem->unit_cost);
    }
}
