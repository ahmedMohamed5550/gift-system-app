<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubcategoryRequest;
use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubcategoryController extends Controller
{
    public function index(Request $request): View
    {
        $query = Subcategory::with('category')->withCount('products');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($request->has('status') && $request->input('status') !== '') {
            $query->where('is_active', (bool) $request->input('status'));
        }

        $subcategories = $query->latest()->paginate(10)->withQueryString();

        $categories = Category::orderBy('name')->get();

        $stats = [
            'total' => Subcategory::count(),
            'active' => Subcategory::where('is_active', true)->count(),
            'inactive' => Subcategory::where('is_active', false)->count(),
        ];

        return view('subcategories.index', compact('subcategories', 'categories', 'stats'));
    }

    public function create(Request $request): View
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $selectedCategoryId = $request->input('category_id');

        return view('subcategories.create', compact('categories', 'selectedCategoryId'));
    }

    public function store(SubcategoryRequest $request): RedirectResponse
    {
        Subcategory::create([
            'category_id' => $request->category_id,
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('subcategories.index')->with('success', 'تم إنشاء القسم الفرعي بنجاح.');
    }

    public function edit(Subcategory $subcategory): View
    {
        $categories = Category::orderBy('name')->get();

        return view('subcategories.edit', compact('subcategory', 'categories'));
    }

    public function update(SubcategoryRequest $request, Subcategory $subcategory): RedirectResponse
    {
        $subcategory->update([
            'category_id' => $request->category_id,
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('subcategories.index')->with('success', 'تم تحديث بيانات القسم الفرعي بنجاح.');
    }

    public function toggleStatus(Subcategory $subcategory): RedirectResponse
    {
        $subcategory->is_active = ! $subcategory->is_active;
        $subcategory->save();

        $message = $subcategory->is_active ? 'تم تفعيل القسم الفرعي بنجاح.' : 'تم تعطيل القسم الفرعي بنجاح مع الحفاظ على المنتجات والسجلات.';

        return back()->with('success', $message);
    }

    public function destroy(Subcategory $subcategory): RedirectResponse
    {
        if ($subcategory->products()->exists()) {
            return back()->with('error', 'لا يمكن حذف هذا القسم الفرعي لوجود منتجات مرتبطة به. يمكنك إلغاء تفعيله للحفاظ على سلامة المخزون والعمليات السابقة.');
        }

        $subcategory->delete();

        return redirect()->route('subcategories.index')->with('success', 'تم حذف القسم الفرعي بنجاح مع الحفاظ على السجلات التاريخية.');
    }
}

