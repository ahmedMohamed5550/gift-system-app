<?php

namespace App\Http\Controllers;

use App\Http\Requests\StockAdjustmentRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class InventoryController extends Controller
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Display inventory overview, product stocks, and low stock warnings.
     */
    public function index(Request $request): View
    {
        $query = Product::with(['subcategory.category']);

        // Search by keyword (name or description)
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by main category
        if ($categoryId = $request->input('category_id')) {
            $query->whereHas('subcategory', function ($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            });
        }

        // Filter by stock level
        $stockStatus = $request->input('stock_status', 'all');
        if ($stockStatus === 'low') {
            $query->whereColumn('stock_quantity', '<=', 'minimum_stock')
                  ->where('stock_quantity', '>', 0);
        } elseif ($stockStatus === 'out') {
            $query->where('stock_quantity', '<=', 0);
        } elseif ($stockStatus === 'in_stock') {
            $query->where('stock_quantity', '>', 0);
        }

        // Filter by active status
        if ($request->has('status') && $request->input('status') !== '') {
            $query->where('is_active', (bool) $request->input('status'));
        }

        $products = $query->orderByRaw('CASE WHEN stock_quantity <= 0 THEN 0 WHEN stock_quantity <= minimum_stock THEN 1 ELSE 2 END')
                          ->orderBy('name')
                          ->paginate(15)
                          ->withQueryString();

        $categories = Category::active()->orderBy('name')->get();
        $metrics = $this->inventoryService->getInventoryMetrics();

        return view('inventory.index', compact('products', 'categories', 'metrics', 'stockStatus'));
    }

    /**
     * Display global stock movement audit log with comprehensive filters.
     */
    public function movements(Request $request): View
    {
        $query = StockMovement::with(['product.subcategory.category', 'user'])->latest('id');

        if ($productId = $request->input('product_id')) {
            $query->where('product_id', $productId);
        }

        if ($referenceType = $request->input('reference_type')) {
            $query->where('reference_type', $referenceType);
        }

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        if ($userId = $request->input('user_id')) {
            $query->where('user_id', $userId);
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $movements = $query->paginate(20)->withQueryString();

        $stats = [
            'total_count' => StockMovement::count(),
            'in_count' => StockMovement::where('type', 'in')->count(),
            'out_count' => StockMovement::where('type', 'out')->count(),
            'net_quantity' => (float) (StockMovement::sum('quantity') ?? 0),
        ];

        $products = Product::orderBy('name')->get(['id', 'name']);
        $users = User::orderBy('name')->get(['id', 'name']);

        return view('inventory.movements', compact('movements', 'stats', 'products', 'users'));
    }

    /**
     * Display movement history and metrics for a specific product.
     */
    public function productMovements(Product $product, Request $request): View
    {
        $query = $product->stockMovements()->with('user')->latest('id');

        if ($referenceType = $request->input('reference_type')) {
            $query->where('reference_type', $referenceType);
        }

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $movements = $query->paginate(15)->withQueryString();

        $product->load('subcategory.category');

        $stats = [
            'total_movements' => $product->stockMovements()->count(),
            'in_quantity' => (float) ($product->stockMovements()->where('type', 'in')->sum('quantity') ?? 0),
            'out_quantity' => (float) ($product->stockMovements()->where('type', 'out')->sum('quantity') ?? 0),
        ];

        return view('inventory.product-movements', compact('product', 'movements', 'stats'));
    }

    /**
     * Show form to perform manual stock adjustments.
     */
    public function createAdjustment(Request $request): View
    {
        $selectedProductId = $request->input('product_id');
        $selectedProduct = $selectedProductId ? Product::with('subcategory')->find($selectedProductId) : null;
        $products = Product::active()->with('subcategory')->orderBy('name')->get();

        return view('inventory.adjust', compact('products', 'selectedProduct', 'selectedProductId'));
    }

    /**
     * Process and record a manual stock adjustment.
     */
    public function storeAdjustment(StockAdjustmentRequest $request): RedirectResponse
    {
        $productId = (int) $request->input('product_id');
        $mode = $request->input('adjustment_mode');
        $direction = $request->input('direction', 'add');
        $value = $mode === 'absolute'
            ? (float) $request->input('counted_quantity')
            : (float) $request->input('quantity');
        $reason = $request->input('reason');
        $userId = Auth::id() ?? 1;

        $this->inventoryService->adjustStock(
            product: $productId,
            adjustmentMode: $mode,
            value: $value,
            direction: $direction,
            reason: $reason,
            userId: $userId
        );

        return redirect()->route('inventory.index')
                         ->with('success', 'تم تسجيل التسوية الجردية بنجاح وتحديث رصيد المخزون للمنتج.');
    }
}

