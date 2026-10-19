<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Subcategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::with(['subcategory.category']);

        // Search by keyword
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by Subcategory
        if ($subcategoryId = $request->input('subcategory_id')) {
            $query->where('subcategory_id', $subcategoryId);
        }

        // Filter by Category
        if ($categoryId = $request->input('category_id')) {
            $query->whereHas('subcategory', function ($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            });
        }

        // Filter by Status
        if ($request->has('status') && $request->input('status') !== '') {
            $query->where('is_active', (bool) $request->input('status'));
        }

        // Filter by Low Stock
        if ($request->input('stock_filter') === 'low') {
            $query->lowStock();
        }

        $products = $query->latest()->paginate(10)->withQueryString();

        $categories = Category::with('subcategories')->orderBy('name')->get();
        $subcategories = Subcategory::with('category')->orderBy('name')->get();

        $stats = [
            'total' => Product::count(),
            'active' => Product::where('is_active', true)->count(),
            'low_stock' => Product::whereColumn('stock_quantity', '<=', 'minimum_stock')->count(),
            'inventory_value' => (float) Product::selectRaw('SUM(stock_quantity * purchase_price) as total')->value('total') ?? 0,
        ];

        return view('products.index', compact('products', 'categories', 'subcategories', 'stats'));
    }

    public function create(): View
    {
        $categories = Category::with(['subcategories' => function ($q) {
            $q->where('is_active', true);
        }])->where('is_active', true)->orderBy('name')->get();

        return view('products.create', compact('categories'));
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $product = Product::create([
            'name' => $request->name,
            'subcategory_id' => $request->subcategory_id,
            'description' => $request->description,
            'purchase_price' => $request->purchase_price,
            'selling_price' => $request->selling_price,
            'stock_quantity' => $request->stock_quantity,
            'minimum_stock' => $request->minimum_stock,
            'is_active' => $request->boolean('is_active'),
        ]);

        \App\Services\ActivityLogService::log(
            action: 'product_created',
            subject: $product,
            description: "تم إضافة منتج جديد للمخزون: \"{$product->name}\" بسعر بيع {$product->selling_price} ج.م وسعر شراء {$product->purchase_price} ج.م ورصيد افتتاحي {$product->stock_quantity}.",
            newValues: [
                'name' => $product->name,
                'subcategory_id' => $product->subcategory_id,
                'purchase_price' => (float) $product->purchase_price,
                'selling_price' => (float) $product->selling_price,
                'stock_quantity' => (float) $product->stock_quantity,
                'minimum_stock' => (float) $product->minimum_stock,
                'is_active' => $product->is_active,
            ]
        );

        return redirect()->route('products.index')->with('success', 'تم إضافة المنتج بنجاح إلى المخزون.');
    }

    public function show(Product $product): View
    {
        $product->load(['subcategory.category', 'saleItems', 'stockMovements']);

        return view('products.show', compact('product'));
    }

    public function edit(Product $product): View
    {
        $categories = Category::with('subcategories')->orderBy('name')->get();

        return view('products.edit', compact('product', 'categories'));
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $oldValues = [
            'name' => $product->name,
            'subcategory_id' => $product->subcategory_id,
            'purchase_price' => (float) $product->purchase_price,
            'selling_price' => (float) $product->selling_price,
            'stock_quantity' => (float) $product->stock_quantity,
            'minimum_stock' => (float) $product->minimum_stock,
            'is_active' => $product->is_active,
        ];

        $product->update([
            'name' => $request->name,
            'subcategory_id' => $request->subcategory_id,
            'description' => $request->description,
            'purchase_price' => $request->purchase_price,
            'selling_price' => $request->selling_price,
            'stock_quantity' => $request->stock_quantity,
            'minimum_stock' => $request->minimum_stock,
            'is_active' => $request->boolean('is_active'),
        ]);

        $newValues = [
            'name' => $product->name,
            'subcategory_id' => $product->subcategory_id,
            'purchase_price' => (float) $product->purchase_price,
            'selling_price' => (float) $product->selling_price,
            'stock_quantity' => (float) $product->stock_quantity,
            'minimum_stock' => (float) $product->minimum_stock,
            'is_active' => $product->is_active,
        ];

        $priceNote = '';
        if ($oldValues['selling_price'] != $newValues['selling_price'] || $oldValues['purchase_price'] != $newValues['purchase_price']) {
            $priceNote = " (تعديل الأسعار: البيع من {$oldValues['selling_price']} إلى {$newValues['selling_price']} ج.م، الشراء من {$oldValues['purchase_price']} إلى {$newValues['purchase_price']} ج.م)";
        }

        \App\Services\ActivityLogService::log(
            action: 'product_updated',
            subject: $product,
            description: "تم تحديث بيانات المنتج: \"{$product->name}\"{$priceNote}.",
            oldValues: $oldValues,
            newValues: $newValues
        );

        return redirect()->route('products.index')->with('success', 'تم تحديث بيانات المنتج بنجاح.');
    }

    public function toggleStatus(Product $product): RedirectResponse
    {
        $oldStatus = $product->is_active;
        $product->is_active = ! $product->is_active;
        $product->save();

        \App\Services\ActivityLogService::log(
            action: 'product_status_toggled',
            subject: $product,
            description: $product->is_active
                ? "تم تفعيل المنتج: \"{$product->name}\"."
                : "تم تعطيل المنتج: \"{$product->name}\".",
            oldValues: ['is_active' => $oldStatus],
            newValues: ['is_active' => $product->is_active]
        );

        $message = $product->is_active ? 'تم تفعيل المنتج بنجاح.' : 'تم تعطيل المنتج بنجاح مع الحفاظ على سجل المبيعات والمخزون.';

        return back()->with('success', $message);
    }

    public function destroy(Product $product): RedirectResponse
    {
        $oldValues = [
            'name' => $product->name,
            'selling_price' => (float) $product->selling_price,
            'stock_quantity' => (float) $product->stock_quantity,
        ];

        $product->delete();

        \App\Services\ActivityLogService::log(
            action: 'product_deleted',
            subject: $product,
            description: "تم حذف المنتج: \"{$product->name}\" مع الحفاظ على السجلات المالية والمخزنية السابقة.",
            oldValues: $oldValues
        );

        return redirect()->route('products.index')->with('success', 'تم حذف المنتج بنجاح مع الحفاظ على جميع السجلات المالية والمخزنية السابقة.');
    }
}

