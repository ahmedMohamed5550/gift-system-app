<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Shift;
use App\Models\StockMovement;
use App\Models\Subcategory;
use App\Models\User;
use App\Services\PosService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PosTest extends TestCase
{
    use RefreshDatabase;

    protected User $cashier;
    protected Shift $activeShift;
    protected Product $product1;
    protected Product $product2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cashier = User::factory()->create([
            'username' => 'cashier_test',
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
            'stock_quantity' => 15.000,
            'minimum_stock' => 3.000,
            'is_active' => true,
        ]);

        $this->product2 = Product::create([
            'subcategory_id' => $subcategory->id,
            'name' => 'ساعة هدية فاخرة',
            'purchase_price' => 300.00,
            'selling_price' => 500.00,
            'stock_quantity' => 5.000,
            'minimum_stock' => 1.000,
            'is_active' => true,
        ]);

        $this->activeShift = Shift::create([
            'user_id' => $this->cashier->id,
            'opened_at' => now(),
            'opening_cash' => 200.00,
            'status' => 'open',
        ]);
    }

    public function test_cashier_can_view_pos_terminal(): void
    {
        $response = $this->actingAs($this->cashier)->get(route('pos.index'));

        $response->assertStatus(200);
        $response->assertSee('نقطة البيع');
        $response->assertSee('عطر مخلط ملكي');
        $response->assertSee('ساعة هدية فاخرة');
        $response->assertSee('الوردية #' . $this->activeShift->id . ' مفتوحة');
    }

    public function test_cannot_checkout_without_active_shift(): void
    {
        // Close active shift
        $this->activeShift->update(['status' => 'closed', 'closed_at' => now()]);

        $payload = [
            'payment_method' => 'cash',
            'discount' => 0,
            'items' => [
                ['product_id' => $this->product1->id, 'quantity' => 1],
            ],
        ];

        $response = $this->actingAs($this->cashier)
            ->postJson(route('pos.checkout'), $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['shift']);
    }

    public function test_successful_sale_creation_with_cash_payment(): void
    {
        $payload = [
            'payment_method' => 'cash',
            'discount' => 0,
            'items' => [
                ['product_id' => $this->product1->id, 'quantity' => 2], // 2 * 200 = 400
                ['product_id' => $this->product2->id, 'quantity' => 1], // 1 * 500 = 500
            ],
        ];

        $response = $this->actingAs($this->cashier)
            ->postJson(route('pos.checkout'), $payload);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('sales', [
            'user_id' => $this->cashier->id,
            'shift_id' => $this->activeShift->id,
            'subtotal' => 900.00,
            'discount' => 0.00,
            'total' => 900.00,
            'payment_method' => 'cash',
            'status' => 'completed',
        ]);

        $sale = Sale::latest('id')->first();
        $this->assertNotNull($sale);
        $this->assertCount(2, $sale->items);

        // Check stock decrease
        $this->assertEquals(13.000, (float) $this->product1->fresh()->stock_quantity);
        $this->assertEquals(4.000, (float) $this->product2->fresh()->stock_quantity);

        // Check stock movement records
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product1->id,
            'user_id' => $this->cashier->id,
            'type' => 'out',
            'quantity' => -2.000,
            'reference_type' => 'sale',
            'reference_id' => $sale->id,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product2->id,
            'user_id' => $this->cashier->id,
            'type' => 'out',
            'quantity' => -1.000,
            'reference_type' => 'sale',
            'reference_id' => $sale->id,
        ]);

        // Check audit log
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->cashier->id,
            'action' => 'sale_created',
            'subject_type' => Sale::class,
            'subject_id' => $sale->id,
        ]);
    }

    public function test_successful_sale_with_vodafone_cash_and_instapay(): void
    {
        // Vodafone Cash sale
        $payloadVoda = [
            'payment_method' => 'vodafone_cash',
            'discount' => 0,
            'items' => [
                ['product_id' => $this->product1->id, 'quantity' => 1],
            ],
        ];

        $responseVoda = $this->actingAs($this->cashier)
            ->postJson(route('pos.checkout'), $payloadVoda);

        $responseVoda->assertStatus(200);
        $this->assertDatabaseHas('sales', [
            'payment_method' => 'vodafone_cash',
            'total' => 200.00,
        ]);

        // InstaPay sale
        $payloadInsta = [
            'payment_method' => 'instapay',
            'discount' => 0,
            'items' => [
                ['product_id' => $this->product2->id, 'quantity' => 1],
            ],
        ];

        $responseInsta = $this->actingAs($this->cashier)
            ->postJson(route('pos.checkout'), $payloadInsta);

        $responseInsta->assertStatus(200);
        $this->assertDatabaseHas('sales', [
            'payment_method' => 'instapay',
            'total' => 500.00,
        ]);
    }

    public function test_server_recalculates_line_totals_subtotal_and_total_ignoring_browser_input(): void
    {
        // Attacker attempts to forge prices: unit_price = 10, total = 20
        $forgedPayload = [
            'payment_method' => 'cash',
            'subtotal' => 20.00, // Real is 200.00
            'total' => 20.00,    // Real is 200.00
            'discount' => 0,
            'items' => [
                [
                    'product_id' => $this->product1->id,
                    'quantity' => 1,
                    'unit_price' => 10.00, // Real selling_price is 200.00
                    'total' => 10.00,
                ],
            ],
        ];

        $response = $this->actingAs($this->cashier)
            ->postJson(route('pos.checkout'), $forgedPayload);

        $response->assertStatus(200);

        // Server must ignore forged prices and use official selling_price from database
        $sale = Sale::latest('id')->first();
        $this->assertEquals(200.00, (float) $sale->subtotal);
        $this->assertEquals(200.00, (float) $sale->total);

        $item = $sale->items->first();
        $this->assertEquals(200.00, (float) $item->unit_price);
        $this->assertEquals(100.00, (float) $item->unit_cost);
        $this->assertEquals(200.00, (float) $item->total);
    }

    public function test_discount_cannot_be_negative_or_exceed_subtotal(): void
    {
        // Negative discount
        $responseNegative = $this->actingAs($this->cashier)
            ->postJson(route('pos.checkout'), [
                'payment_method' => 'cash',
                'discount' => -50,
                'items' => [['product_id' => $this->product1->id, 'quantity' => 1]],
            ]);
        $responseNegative->assertStatus(422);

        // Discount exceeds subtotal (subtotal is 200)
        $responseExcessive = $this->actingAs($this->cashier)
            ->postJson(route('pos.checkout'), [
                'payment_method' => 'cash',
                'discount' => 300,
                'items' => [['product_id' => $this->product1->id, 'quantity' => 1]],
            ]);
        $responseExcessive->assertStatus(422);
        $responseExcessive->assertJsonValidationErrors(['discount']);

        // Valid discount (50 discount on 200 subtotal = 150 total)
        $responseValid = $this->actingAs($this->cashier)
            ->postJson(route('pos.checkout'), [
                'payment_method' => 'cash',
                'discount' => 50,
                'items' => [['product_id' => $this->product1->id, 'quantity' => 1]],
            ]);
        $responseValid->assertStatus(200);

        $sale = Sale::latest('id')->first();
        $this->assertEquals(200.00, (float) $sale->subtotal);
        $this->assertEquals(50.00, (float) $sale->discount);
        $this->assertEquals(150.00, (float) $sale->total);
    }

    public function test_cannot_sell_more_than_available_stock_preventing_overselling(): void
    {
        // Product 2 has only 5 in stock
        $payload = [
            'payment_method' => 'cash',
            'discount' => 0,
            'items' => [
                ['product_id' => $this->product2->id, 'quantity' => 10], // Requesting 10
            ],
        ];

        $response = $this->actingAs($this->cashier)
            ->postJson(route('pos.checkout'), $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['items']);

        // Stock remains unchanged
        $this->assertEquals(5.000, (float) $this->product2->fresh()->stock_quantity);
        $this->assertDatabaseCount('sales', 0);
    }

    public function test_transaction_rolls_back_if_any_item_fails(): void
    {
        // First item is available (quantity 2), but second item is oversold (quantity 10)
        $payload = [
            'payment_method' => 'cash',
            'discount' => 0,
            'items' => [
                ['product_id' => $this->product1->id, 'quantity' => 2],
                ['product_id' => $this->product2->id, 'quantity' => 10], // Will fail
            ],
        ];

        $response = $this->actingAs($this->cashier)
            ->postJson(route('pos.checkout'), $payload);

        $response->assertStatus(422);

        // Entire transaction must rollback: product 1 stock must NOT change
        $this->assertEquals(15.000, (float) $this->product1->fresh()->stock_quantity);
        $this->assertEquals(5.000, (float) $this->product2->fresh()->stock_quantity);

        $this->assertDatabaseCount('sales', 0);
        $this->assertDatabaseCount('sale_items', 0);
        $this->assertDatabaseCount('stock_movements', 0);
    }

    public function test_cannot_sell_inactive_product(): void
    {
        $this->product1->update(['is_active' => false]);

        $payload = [
            'payment_method' => 'cash',
            'discount' => 0,
            'items' => [
                ['product_id' => $this->product1->id, 'quantity' => 1],
            ],
        ];

        $response = $this->actingAs($this->cashier)
            ->postJson(route('pos.checkout'), $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['items']);
    }

    public function test_unit_price_and_unit_cost_snapshots_saved_in_sale_items(): void
    {
        $payload = [
            'payment_method' => 'cash',
            'discount' => 0,
            'items' => [
                ['product_id' => $this->product1->id, 'quantity' => 1],
            ],
        ];

        $this->actingAs($this->cashier)->postJson(route('pos.checkout'), $payload);

        $item = SaleItem::first();
        $this->assertEquals(200.00, (float) $item->unit_price);
        $this->assertEquals(100.00, (float) $item->unit_cost);
        $this->assertEquals(100.00, (float) $item->profit); // (200 - 100) * 1
    }

    public function test_unique_invoice_numbers_generated(): void
    {
        $payload = [
            'payment_method' => 'cash',
            'discount' => 0,
            'items' => [
                ['product_id' => $this->product1->id, 'quantity' => 1],
            ],
        ];

        $this->actingAs($this->cashier)->postJson(route('pos.checkout'), $payload);
        $this->actingAs($this->cashier)->postJson(route('pos.checkout'), $payload);

        $sales = Sale::all();
        $this->assertCount(2, $sales);
        $this->assertNotEquals($sales[0]->invoice_number, $sales[1]->invoice_number);
    }

    public function test_sale_cancellation_reverses_stock_and_marks_status_cancelled(): void
    {
        // 1. Create sale
        $payload = [
            'payment_method' => 'cash',
            'discount' => 0,
            'items' => [
                ['product_id' => $this->product1->id, 'quantity' => 3], // 15 - 3 = 12
            ],
        ];

        $this->actingAs($this->cashier)->postJson(route('pos.checkout'), $payload);
        $sale = Sale::latest('id')->first();

        $this->assertEquals(12.000, (float) $this->product1->fresh()->stock_quantity);

        // 2. Cancel sale
        $response = $this->actingAs($this->cashier)
            ->patch(route('sales.cancel', $sale), [
                'reason' => 'طلب العميل إلغاء العملية فوراً واسترجاع المبلغ',
            ]);

        $response->assertRedirect(route('sales.show', $sale));

        // Sale status updated to cancelled
        $this->assertEquals('cancelled', $sale->fresh()->status);

        // Stock restored back from 12 to 15!
        $this->assertEquals(15.000, (float) $this->product1->fresh()->stock_quantity);

        // Stock movement recorded for reversal (type: in, reference_type: sale)
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product1->id,
            'type' => 'in',
            'quantity' => 3.000,
            'reference_type' => 'sale',
            'reference_id' => $sale->id,
        ]);

        // Audit log created for cancellation
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'sale_cancelled',
            'subject_type' => Sale::class,
            'subject_id' => $sale->id,
        ]);
    }

    public function test_cannot_cancel_already_cancelled_sale_preventing_duplicate_reversals(): void
    {
        // Create and cancel once
        $posService = app(PosService::class);
        $sale = $posService->createSale([
            'payment_method' => 'cash',
            'discount' => 0,
            'items' => [
                ['product_id' => $this->product1->id, 'quantity' => 2],
            ],
        ], $this->cashier, $this->activeShift);

        $posService->cancelSale($sale, $this->cashier, 'إلغاء أول');

        // Stock restored to 15
        $this->assertEquals(15.000, (float) $this->product1->fresh()->stock_quantity);

        // Attempt second cancellation - MUST fail with ValidationException
        $this->expectException(ValidationException::class);
        $posService->cancelSale($sale, $this->cashier, 'إلغاء ثاني مكرر');

        // Stock must still be 15, not 17!
        $this->assertEquals(15.000, (float) $this->product1->fresh()->stock_quantity);
    }

    public function test_sales_history_and_filtering(): void
    {
        $posService = app(PosService::class);

        $saleCash = $posService->createSale([
            'payment_method' => 'cash',
            'items' => [['product_id' => $this->product1->id, 'quantity' => 1]],
        ], $this->cashier, $this->activeShift);

        $saleVoda = $posService->createSale([
            'payment_method' => 'vodafone_cash',
            'items' => [['product_id' => $this->product2->id, 'quantity' => 1]],
        ], $this->cashier, $this->activeShift);

        // View list
        $response = $this->actingAs($this->cashier)->get(route('sales.index'));
        $response->assertStatus(200);
        $response->assertSee($saleCash->invoice_number);
        $response->assertSee($saleVoda->invoice_number);

        // Filter by payment method
        $responseFilter = $this->actingAs($this->cashier)
            ->get(route('sales.index', ['payment_method' => 'vodafone_cash']));
        $responseFilter->assertStatus(200);
        $responseFilter->assertSee($saleVoda->invoice_number);
        $responseFilter->assertDontSee($saleCash->invoice_number);
    }

    public function test_invoice_details_and_printable_receipt_views(): void
    {
        $posService = app(PosService::class);
        $sale = $posService->createSale([
            'payment_method' => 'cash',
            'items' => [
                ['product_id' => $this->product1->id, 'quantity' => 2],
            ],
        ], $this->cashier, $this->activeShift);

        // Show page
        $responseShow = $this->actingAs($this->cashier)->get(route('sales.show', $sale));
        $responseShow->assertStatus(200);
        $responseShow->assertSee($sale->invoice_number);
        $responseShow->assertSee('عطر مخلط ملكي');

        // Print receipt page
        $responsePrint = $this->actingAs($this->cashier)->get(route('sales.print', $sale));
        $responsePrint->assertStatus(200);
        $responsePrint->assertSee('إيصال فاتورة مبيعات');
        $responsePrint->assertSee($sale->invoice_number);
    }
}

