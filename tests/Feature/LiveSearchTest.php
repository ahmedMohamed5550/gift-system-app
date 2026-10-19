<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Expense;
use App\Models\Note;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\Shift;
use App\Models\Subcategory;
use App\Models\User;
use App\Services\PosService;
use App\Services\ReturnService;
use App\Services\ShiftService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LiveSearchTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected User $employee1;
    protected User $employee2;
    protected Category $category;
    protected Subcategory $subcategory;
    protected Product $product1;
    protected Product $product2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->owner()->create([
            'username' => 'owner_search',
            'name' => 'مالك المتجر',
        ]);

        $this->employee1 = User::factory()->employee()->create([
            'username' => 'emp1_search',
            'name' => 'أحمد علي',
        ]);

        $this->employee2 = User::factory()->employee()->create([
            'username' => 'emp2_search',
            'name' => 'محمود حسن',
        ]);

        $this->category = Category::create([
            'name' => 'عطور وهدايا شرقية',
            'description' => 'قسم العطور والهدايا',
            'is_active' => true,
        ]);

        $this->subcategory = Subcategory::create([
            'category_id' => $this->category->id,
            'name' => 'بخور وعود فاخر',
            'is_active' => true,
        ]);

        $this->product1 = Product::create([
            'subcategory_id' => $this->subcategory->id,
            'name' => 'عطر المسك الأبيض الممتاز',
            'purchase_price' => 150,
            'selling_price' => 300,
            'stock_quantity' => 20,
            'minimum_stock' => 5,
            'is_active' => true,
        ]);

        $this->product2 = Product::create([
            'subcategory_id' => $this->subcategory->id,
            'name' => 'ساعة رجالية جلدية كلاسيك',
            'purchase_price' => 400,
            'selling_price' => 700,
            'stock_quantity' => 10,
            'minimum_stock' => 2,
            'is_active' => true,
        ]);
    }

    public function test_products_search_filters_by_name_and_restores_on_empty(): void
    {
        $this->actingAs($this->employee1);

        // Search for product1
        $res1 = $this->get(route('products.index', ['search' => 'المسك']));
        $res1->assertStatus(200);
        $res1->assertSee('عطر المسك الأبيض الممتاز');
        $res1->assertDontSee('ساعة رجالية جلدية كلاسيك');

        // Search for product2
        $res2 = $this->get(route('products.index', ['search' => 'ساعة رجالية']));
        $res2->assertStatus(200);
        $res2->assertSee('ساعة رجالية جلدية كلاسيك');
        $res2->assertDontSee('عطر المسك الأبيض الممتاز');

        // Empty search restores all
        $resEmpty = $this->get(route('products.index', ['search' => '']));
        $resEmpty->assertStatus(200);
        $resEmpty->assertSee('عطر المسك الأبيض الممتاز');
        $resEmpty->assertSee('ساعة رجالية جلدية كلاسيك');
    }

    public function test_categories_and_subcategories_search(): void
    {
        $this->actingAs($this->employee1);

        $catRes = $this->get(route('categories.index', ['search' => 'عطور']));
        $catRes->assertStatus(200);
        $catRes->assertSee('عطور وهدايا شرقية');

        $subRes = $this->get(route('subcategories.index', ['search' => 'بخور']));
        $subRes->assertStatus(200);
        $subRes->assertSee('بخور وعود فاخر');
    }

    public function test_sales_search_respects_rbac_isolation(): void
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
                ['product_id' => $this->product1->id, 'quantity' => 1],
            ],
        ], $this->employee1, $shift1);

        $shift2 = $shiftService->openShift($this->employee2, 300.00);
        $sale2 = $posService->createSale([
            'payment_method' => 'cash',
            'discount' => 0,
            'items' => [
                ['product_id' => $this->product2->id, 'quantity' => 1],
            ],
        ], $this->employee2, $shift2);

        // Employee 1 searches by invoice number of their own sale -> sees it
        $emp1SearchOwn = $this->actingAs($this->employee1)->get(route('sales.index', [
            'search' => $sale1->invoice_number,
        ]));
        $emp1SearchOwn->assertStatus(200);
        $emp1SearchOwn->assertSee($sale1->invoice_number);

        // Employee 1 searches for Employee 2's invoice -> isolated, does not see it
        $emp1SearchOther = $this->actingAs($this->employee1)->get(route('sales.index', [
            'search' => $sale2->invoice_number,
        ]));
        $emp1SearchOther->assertStatus(200);
        $emp1SearchOther->assertDontSee($sale2->invoice_number);

        // Owner searches for Employee 2's invoice -> sees it
        $ownerSearch = $this->actingAs($this->owner)->get(route('sales.index', [
            'search' => $sale2->invoice_number,
        ]));
        $ownerSearch->assertStatus(200);
        $ownerSearch->assertSee($sale2->invoice_number);
    }

    public function test_notes_search_respects_permissions_and_filters(): void
    {
        $note1 = Note::create([
            'user_id' => $this->employee1->id,
            'title' => 'متابعة عميل',
            'content' => 'العميل محمد استفسر عن عطر المسك',
            'status' => 'open',
        ]);

        $note2 = Note::create([
            'user_id' => $this->employee2->id,
            'title' => 'نواقص أو طلب بضاعة',
            'content' => 'نحتاج طلب علب هدايا مخملية',
            'status' => 'open',
        ]);

        // Employee 1 searches for "المسك" -> finds note 1
        $emp1Res = $this->actingAs($this->employee1)->get(route('notes.index', ['search' => 'المسك']));
        $emp1Res->assertStatus(200);
        $emp1Res->assertSee('العميل محمد استفسر عن عطر المسك');

        // Employee 1 searches for note 2's content -> cannot see other employee's note
        $emp1Res2 = $this->actingAs($this->employee1)->get(route('notes.index', ['search' => 'مخملية']));
        $emp1Res2->assertStatus(200);
        $emp1Res2->assertDontSee('علب هدايا مخملية');

        // Owner searches for note 2 -> sees it
        $ownerRes = $this->actingAs($this->owner)->get(route('notes.index', ['search' => 'مخملية']));
        $ownerRes->assertStatus(200);
        $ownerRes->assertSee('علب هدايا مخملية');
    }
}

