<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Category;
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
use App\Services\ReturnService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ReturnTest extends TestCase
{
    use RefreshDatabase;

    protected User $cashier;
    protected Shift $activeShift;
    protected Product $product1;
    protected Product $product2;
    protected Sale $completedSale;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cashier = User::factory()->create([
            'username' => 'cashier_return_test',
            'role' => 'employee',
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'عطور وهدايا',
            'is_active' => true,
        ]);

        $subcategory = Subcategory::create([
            'category_id' => $category->id,
            'name' => 'عطور شرقية',
            'is_active' => true,
        ]);

        $this->product1 = Product::create([
            'subcategory_id' => $subcategory->id,
            'name' => 'عطر مخلط ملكي',
            'purchase_price' => 100.00,
            'selling_price' => 200.00,
            'stock_quantity' => 20.000,
            'minimum_stock' => 3.000,
            'is_active' => true,
        ]);

        $this->product2 = Product::create([
            'subcategory_id' => $subcategory->id,
            'name' => 'ساعة فاخرة',
            'purchase_price' => 300.00,
            'selling_price' => 500.00,
            'stock_quantity' => 10.000,
            'minimum_stock' => 1.000,
            'is_active' => true,
        ]);

        $this->activeShift = Shift::create([
            'user_id' => $this->cashier->id,
            'opened_at' => now(),
            'opening_cash' => 500.00,
            'status' => 'open',
        ]);

        // Create a completed sale: 5 units of product1, 3 units of product2
        /** @var PosService $posService */
        $posService = app(PosService::class);
        $this->completedSale = $posService->createSale([
            'payment_method' => 'cash',
            'discount' => 0,
            'items' => [
                ['product_id' => $this->product1->id, 'quantity' => 5],
                ['product_id' => $this->product2->id, 'quantity' => 3],
            ],
        ], $this->cashier, $this->activeShift);

        // After sale, product1 stock: 15, product2 stock: 7
    }

    public function test_cashier_can_view_returns_index_and_create_pages(): void
    {
        $response = $this->actingAs($this->cashier)->get(route('returns.index'));
        $response->assertStatus(200);
        $response->assertSee('مرتجعات المبيعات');
        $response->assertSee('إجمالي المرتجعات المعتمدة');

        $responseCreate = $this->actingAs($this->cashier)->get(route('returns.create'));
        $responseCreate->assertStatus(200);
        $responseCreate->assertSee('إرجاع فاتورة مبيعات');
        $responseCreate->assertSee('البحث عن الفاتورة الأصلية');
    }

    public function test_cannot_create_return_without_open_shift(): void
    {
        // Close cashier's shift
        $this->activeShift->update(['status' => 'closed', 'closed_at' => now()]);

        $saleItem = $this->completedSale->items->first();

        $payload = [
            'sale_id' => $this->completedSale->id,
            'refund_method' => 'cash',
            'reason' => 'عيب صناعة',
            'items' => [
                ['sale_item_id' => $saleItem->id, 'quantity' => 1],
            ],
        ];

        $response = $this->actingAs($this->cashier)
            ->post(route('returns.store'), $payload);

        $response->assertSessionHasErrors(['shift']);
        $this->assertDatabaseCount('returns', 0);
    }

    public function test_search_invoice_and_calculate_remaining_quantities(): void
    {
        /** @var ReturnService $returnService */
        $returnService = app(ReturnService::class);

        $sale = $returnService->getInvoiceForReturn($this->completedSale->invoice_number);

        $this->assertEquals($this->completedSale->id, $sale->id);
        $this->assertEquals(8.0, $sale->total_returnable_items);

        $item1 = $sale->items->where('product_id', $this->product1->id)->first();
        $this->assertEquals(5.0, $item1->returnable_quantity_calculated);
        $this->assertEquals(0.0, $item1->returned_quantity_calculated);

        // AJAX search endpoint
        $response = $this->actingAs($this->cashier)
            ->getJson(route('returns.search-invoice', ['search' => $this->completedSale->invoice_number]));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'sale' => [
                'invoice_number' => $this->completedSale->invoice_number,
                'total_returnable_items' => 8,
            ],
        ]);
    }

    public function test_successful_partial_return_with_cash_refund(): void
    {
        $saleItem1 = $this->completedSale->items->where('product_id', $this->product1->id)->first();

        // Initial product1 stock is 15 (after 5 were sold from initial 20)
        $this->assertEquals(15.000, (float) $this->product1->fresh()->stock_quantity);

        $payload = [
            'sale_id' => $this->completedSale->id,
            'refund_method' => 'cash',
            'reason' => 'إرجاع قطعتين بسبب تغيير رأي العميل',
            'items' => [
                ['sale_item_id' => $saleItem1->id, 'quantity' => 2],
            ],
        ];

        $response = $this->actingAs($this->cashier)->post(route('returns.store'), $payload);

        $saleReturn = SaleReturn::first();
        $this->assertNotNull($saleReturn);

        $response->assertRedirect(route('returns.show', $saleReturn));

        // 2 units * 200.00 = 400.00
        $this->assertEquals(400.00, (float) $saleReturn->total_amount);
        $this->assertEquals('approved', $saleReturn->status);
        $this->assertEquals('cash', $saleReturn->refund_method);
        $this->assertEquals($this->cashier->id, $saleReturn->user_id);
        $this->assertEquals($this->activeShift->id, $saleReturn->shift_id);

        // Verify ReturnItem
        $this->assertDatabaseHas('return_items', [
            'return_id' => $saleReturn->id,
            'sale_item_id' => $saleItem1->id,
            'product_id' => $this->product1->id,
            'quantity' => 2.000,
            'unit_price' => 200.00,
            'total' => 400.00,
        ]);

        // Stock must increase from 15 to 17
        $this->assertEquals(17.000, (float) $this->product1->fresh()->stock_quantity);

        // Stock movement recorded
        $movement = StockMovement::where('reference_type', 'return')
            ->where('reference_id', $saleReturn->id)
            ->first();
        $this->assertNotNull($movement);
        $this->assertEquals('in', $movement->type);
        $this->assertEquals(2.000, (float) $movement->quantity);
        $this->assertEquals($this->cashier->id, $movement->user_id);

        // Activity log recorded
        $this->assertDatabaseHas('activity_logs', [
            'subject_type' => SaleReturn::class,
            'subject_id' => $saleReturn->id,
            'action' => 'return_created',
        ]);

        // Shift cash calculation:
        // Cash sales: 5*200 + 3*500 = 1000 + 1500 = 2500. Opening cash = 500.
        // Cash returns: 400. Expected cash = 500 + 2500 - 400 = 2600.
        $freshShift = $this->activeShift->fresh();
        $this->assertEquals(400.00, $freshShift->cash_returns_total);
        $this->assertEquals(2600.00, $freshShift->calculated_expected_cash);
    }

    public function test_successful_full_return_with_electronic_refund_does_not_reduce_cash_drawer(): void
    {
        $saleItem1 = $this->completedSale->items->where('product_id', $this->product1->id)->first();
        $saleItem2 = $this->completedSale->items->where('product_id', $this->product2->id)->first();

        $payload = [
            'sale_id' => $this->completedSale->id,
            'refund_method' => 'vodafone_cash', // Electronic refund
            'reason' => 'إرجاع الفاتورة بالكامل على محفظة فودافون كاش',
            'items' => [
                ['sale_item_id' => $saleItem1->id, 'quantity' => 5],
                ['sale_item_id' => $saleItem2->id, 'quantity' => 3],
            ],
        ];

        $response = $this->actingAs($this->cashier)->post(route('returns.store'), $payload);

        $saleReturn = SaleReturn::first();
        $this->assertNotNull($saleReturn);

        // 5 * 200 + 3 * 500 = 1000 + 1500 = 2500.00
        $this->assertEquals(2500.00, (float) $saleReturn->total_amount);
        $this->assertEquals('vodafone_cash', $saleReturn->refund_method);

        // Stocks restored back to original initial quantities (20 and 10)
        $this->assertEquals(20.000, (float) $this->product1->fresh()->stock_quantity);
        $this->assertEquals(10.000, (float) $this->product2->fresh()->stock_quantity);

        // Shift cash drawer: Electronic refund must NOT decrease physical cash drawer!
        $freshShift = $this->activeShift->fresh();
        $this->assertEquals(0.00, $freshShift->cash_returns_total);
        $this->assertEquals(2500.00, $freshShift->total_returns);
        // Opening cash (500) + Cash sales (2500) - Cash refunds (0) = 3000
        $this->assertEquals(3000.00, $freshShift->calculated_expected_cash);
    }

    public function test_cannot_exceed_returnable_quantity(): void
    {
        $saleItem1 = $this->completedSale->items->where('product_id', $this->product1->id)->first();

        // Original sold quantity is 5; cashier attempts to return 6
        $payload = [
            'sale_id' => $this->completedSale->id,
            'refund_method' => 'cash',
            'reason' => 'محاولة تجاوز الكمية',
            'items' => [
                ['sale_item_id' => $saleItem1->id, 'quantity' => 6],
            ],
        ];

        $response = $this->actingAs($this->cashier)->post(route('returns.store'), $payload);

        $response->assertSessionHasErrors(['items']);
        $this->assertDatabaseCount('returns', 0);
        // Stock should remain unchanged (15)
        $this->assertEquals(15.000, (float) $this->product1->fresh()->stock_quantity);
    }

    public function test_repeated_returns_strictly_enforce_remaining_quantity(): void
    {
        /** @var ReturnService $returnService */
        $returnService = app(ReturnService::class);
        $saleItem1 = $this->completedSale->items->where('product_id', $this->product1->id)->first();

        // 1st Return: return 2 of 5. Remaining = 3.
        $return1 = $returnService->createReturn([
            'sale_id' => $this->completedSale->id,
            'refund_method' => 'cash',
            'reason' => 'مرتجع أول جزئي',
            'items' => [
                ['sale_item_id' => $saleItem1->id, 'quantity' => 2],
            ],
        ], $this->cashier, $this->activeShift);

        $this->assertEquals('approved', $return1->status);
        $this->assertEquals(17.000, (float) $this->product1->fresh()->stock_quantity);

        // 2nd Return: return 2 of remaining 3. Remaining = 1.
        $return2 = $returnService->createReturn([
            'sale_id' => $this->completedSale->id,
            'refund_method' => 'cash',
            'reason' => 'مرتجع ثانٍ جزئي',
            'items' => [
                ['sale_item_id' => $saleItem1->id, 'quantity' => 2],
            ],
        ], $this->cashier, $this->activeShift);

        $this->assertEquals('approved', $return2->status);
        $this->assertEquals(19.000, (float) $this->product1->fresh()->stock_quantity);

        // 3rd Return attempt: attempt to return 2 (only 1 remaining!). Must throw ValidationException!
        $this->expectException(ValidationException::class);
        $returnService->createReturn([
            'sale_id' => $this->completedSale->id,
            'refund_method' => 'cash',
            'reason' => 'محاولة مرتجع يتجاوز المتبقي',
            'items' => [
                ['sale_item_id' => $saleItem1->id, 'quantity' => 2],
            ],
        ], $this->cashier, $this->activeShift);
    }

    public function test_transaction_rolls_back_if_any_step_fails(): void
    {
        $saleItem1 = $this->completedSale->items->where('product_id', $this->product1->id)->first();
        $initialProduct1Stock = (float) $this->product1->fresh()->stock_quantity;

        // Passing one valid item and one invalid item (exceeding returnable quantity)
        $payload = [
            'sale_id' => $this->completedSale->id,
            'refund_method' => 'cash',
            'reason' => 'فشل تزامني',
            'items' => [
                ['sale_item_id' => $saleItem1->id, 'quantity' => 10], // Exceeds 5
            ],
        ];

        $response = $this->actingAs($this->cashier)->post(route('returns.store'), $payload);
        $response->assertSessionHasErrors(['items']);

        // Check no partial records were persisted
        $this->assertDatabaseCount('returns', 0);
        $this->assertDatabaseCount('return_items', 0);
        $this->assertEquals($initialProduct1Stock, (float) $this->product1->fresh()->stock_quantity);
    }

    public function test_safe_return_cancellation_reverses_stock_exactly_once(): void
    {
        /** @var ReturnService $returnService */
        $returnService = app(ReturnService::class);
        $saleItem1 = $this->completedSale->items->where('product_id', $this->product1->id)->first();

        // 1. Create return of 3 units
        $saleReturn = $returnService->createReturn([
            'sale_id' => $this->completedSale->id,
            'refund_method' => 'cash',
            'reason' => 'مرتجع تجريبي قبل الإلغاء',
            'items' => [
                ['sale_item_id' => $saleItem1->id, 'quantity' => 3],
            ],
        ], $this->cashier, $this->activeShift);

        $this->assertEquals('approved', $saleReturn->status);
        // Stock increased from 15 to 18
        $this->assertEquals(18.000, (float) $this->product1->fresh()->stock_quantity);
        $this->assertEquals(600.00, $this->activeShift->fresh()->cash_returns_total);

        // 2. Cancel the return
        $response = $this->actingAs($this->cashier)->patch(route('returns.cancel', $saleReturn), [
            'reason' => 'تم إلغاء المرتجع بناء على رغبة العميل وإعادة القطع للمخزون الفعلي',
        ]);

        $response->assertRedirect(route('returns.show', $saleReturn));

        $freshReturn = $saleReturn->fresh();
        $this->assertEquals('cancelled', $freshReturn->status);
        $this->assertEquals('تم إلغاء المرتجع بناء على رغبة العميل وإعادة القطع للمخزون الفعلي', $freshReturn->cancellation_reason);
        $this->assertNotNull($freshReturn->cancelled_at);
        $this->assertEquals($this->cashier->id, $freshReturn->cancelled_by);

        // Stock decreased back from 18 to 15 (reversal exactly once)
        $this->assertEquals(15.000, (float) $this->product1->fresh()->stock_quantity);

        // Verify stock movement for cancellation
        $cancelMovement = StockMovement::where('reference_type', 'return')
            ->where('reference_id', $saleReturn->id)
            ->where('type', 'out')
            ->first();
        $this->assertNotNull($cancelMovement);
        $this->assertEquals(-3.000, (float) $cancelMovement->quantity);

        // Cancelled return is excluded from shift cash returns
        $this->assertEquals(0.00, $this->activeShift->fresh()->cash_returns_total);
    }

    public function test_cannot_cancel_already_cancelled_return_preventing_duplicate_reversals(): void
    {
        /** @var ReturnService $returnService */
        $returnService = app(ReturnService::class);
        $saleItem1 = $this->completedSale->items->where('product_id', $this->product1->id)->first();

        $saleReturn = $returnService->createReturn([
            'sale_id' => $this->completedSale->id,
            'refund_method' => 'cash',
            'reason' => 'مرتجع للإلغاء المزدوج',
            'items' => [
                ['sale_item_id' => $saleItem1->id, 'quantity' => 2],
            ],
        ], $this->cashier, $this->activeShift);

        // Cancel once
        $returnService->cancelReturn($saleReturn, $this->cashier, 'إلغاء أول');
        $this->assertEquals('cancelled', $saleReturn->fresh()->status);
        $stockAfterFirstCancel = (float) $this->product1->fresh()->stock_quantity;

        // Attempt to cancel again
        $this->expectException(ValidationException::class);
        $returnService->cancelReturn($saleReturn->fresh(), $this->cashier, 'محاولة إلغاء ثان');

        // Verify stock did not decrease again
        $this->assertEquals($stockAfterFirstCancel, (float) $this->product1->fresh()->stock_quantity);
    }

    public function test_cannot_return_cancelled_sale(): void
    {
        /** @var PosService $posService */
        $posService = app(PosService::class);
        /** @var ReturnService $returnService */
        $returnService = app(ReturnService::class);

        // Cancel the original sale
        $posService->cancelSale($this->completedSale, $this->cashier, 'إلغاء الفاتورة بالكامل');
        $this->assertEquals('cancelled', $this->completedSale->fresh()->status);

        $saleItem1 = $this->completedSale->items->first();

        // Attempt to create return on cancelled sale
        $this->expectException(ValidationException::class);
        $returnService->createReturn([
            'sale_id' => $this->completedSale->id,
            'refund_method' => 'cash',
            'reason' => 'مرتجع لفاتورة ملغاة',
            'items' => [
                ['sale_item_id' => $saleItem1->id, 'quantity' => 1],
            ],
        ], $this->cashier, $this->activeShift);
    }
}

