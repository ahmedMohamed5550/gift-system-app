<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $query = Category::withCount(['subcategories', 'products']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->has('status') && $request->input('status') !== '') {
            $query->where('is_active', (bool) $request->input('status'));
        }

        $categories = $query->latest()->paginate(10)->withQueryString();

        $stats = [
            'total' => Category::count(),
            'active' => Category::where('is_active', true)->count(),
            'inactive' => Category::where('is_active', false)->count(),
        ];

        return view('categories.index', compact('categories', 'stats'));
    }

    public function create(): View
    {
        return view('categories.create');
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        Category::create([
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('categories.index')->with('success', 'تم إنشاء القسم الرئيسي بنجاح.');
    }

    public function show(Category $category): View
    {
        $category->load([
            'subcategories' => function ($query) {
                $query->withCount('products')->latest();
            },
        ]);

        return view('categories.show', compact('category'));
    }

    public function edit(Category $category): View
    {
        return view('categories.edit', compact('category'));
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update([
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('categories.index')->with('success', 'تم تحديث بيانات القسم الرئيسي بنجاح.');
    }

    public function toggleStatus(Category $category): RedirectResponse
    {
        $category->is_active = ! $category->is_active;
        $category->save();

        $message = $category->is_active ? 'تم تفعيل القسم بنجاح.' : 'تم تعطيل القسم بنجاح مع الحفاظ على المنتجات والسجلات.';

        return back()->with('success', $message);
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->subcategories()->exists()) {
            return back()->with('error', 'لا يمكن حذف القسم الرئيسي لوجود أقسام فرعية مرتبطة به. يمكنك إلغاء تفعيله بدلاً من حذفه للحفاظ على البيانات.');
        }

        $category->delete();

        return redirect()->route('categories.index')->with('success', 'تم حذف القسم بنجاح مع الحفاظ على السجلات التاريخية.');
    }
}

