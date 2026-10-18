<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSaleRequest;
use App\Models\Category;
use App\Models\Product;
use App\Services\PosService;
use App\Services\ShiftService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PosController extends Controller
{
    public function __construct(
        protected PosService $posService,
        protected ShiftService $shiftService
    ) {}

    /**
     * Display the POS checkout terminal interface.
     */
    public function index(): View
    {
        $user = Auth::user();
        $activeShift = $this->shiftService->getActiveShift($user);

        // Load active categories for quick category filter pills
        $categories = Category::active()
            ->withCount(['subcategories'])
            ->orderBy('name')
            ->get();

        // Load active products with category hierarchy for catalog display
        $products = Product::active()
            ->with(['subcategory.category'])
            ->orderBy('name')
            ->get();

        $productsData = $products->mapWithKeys(fn($p) => [
            $p->id => [
                'id' => $p->id,
                'name' => $p->name,
                'category_id' => $p->subcategory?->category_id ?? 0,
                'category_name' => $p->subcategory?->name ?? 'عام',
                'price' => (float) $p->selling_price,
                'stock' => (float) $p->stock_quantity,
                'is_active' => (bool) $p->is_active,
            ]
        ])->all();

        return view('pos.index', compact('activeShift', 'categories', 'products', 'productsData'));
    }

    /**
     * Process checkout and create a sale invoice.
     */
    public function store(StoreSaleRequest $request): JsonResponse|RedirectResponse
    {
        $sale = $this->posService->createSale(
            data: $request->validated(),
            user: $request->user()
        );

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "تم إصدار الفاتورة رقم #{$sale->invoice_number} بنجاح.",
                'sale_id' => $sale->id,
                'invoice_number' => $sale->invoice_number,
                'redirect_url' => route('sales.show', $sale),
                'print_url' => route('sales.print', $sale),
            ]);
        }

        return redirect()
            ->route('sales.show', $sale)
            ->with('success', "تم إصدار الفاتورة رقم #{$sale->invoice_number} بنجاح.");
    }

    /**
     * Quick search endpoint for products by barcode, ID, or name (JSON).
     */
    public function search(Request $request): JsonResponse
    {
        $query = trim($request->input('q', ''));

        if (empty($query)) {
            $products = Product::active()
                ->with('subcategory.category')
                ->limit(30)
                ->get();
        } else {
            $products = Product::active()
                ->with('subcategory.category')
                ->where(function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                      ->orWhere('id', $query);
                })
                ->limit(30)
                ->get();
        }

        return response()->json([
            'products' => $products->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'category_name' => $p->subcategory?->name ?? 'عام',
                'selling_price' => (float) $p->selling_price,
                'formatted_price' => number_format($p->selling_price, 2) . ' جنيه مصري',
                'stock_quantity' => (float) $p->stock_quantity,
                'is_low_stock' => (bool) $p->is_low_stock,
                'is_out_of_stock' => (float) $p->stock_quantity <= 0,
            ]),
        ]);
    }
}
