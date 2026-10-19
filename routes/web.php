<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReturnController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\SubcategoryController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Redirect root to dashboard (guests will be redirected to login by auth middleware)
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Authenticated and active routes
Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // 1. User Management Routes
    Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::resource('users', UserController::class);

    // 2. Categories Routes
    Route::patch('categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('categories.toggle-status');
    Route::resource('categories', CategoryController::class);

    // 3. Subcategories Routes
    Route::patch('subcategories/{subcategory}/toggle-status', [SubcategoryController::class, 'toggleStatus'])->name('subcategories.toggle-status');
    Route::resource('subcategories', SubcategoryController::class);

    // 4. Products Routes
    Route::patch('products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])->name('products.toggle-status');
    Route::resource('products', ProductController::class);

    // 5. Inventory & Stock Management Routes
    Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('inventory/movements', [InventoryController::class, 'movements'])->name('inventory.movements');
    Route::get('inventory/adjust', [InventoryController::class, 'createAdjustment'])->name('inventory.adjustments.create');
    Route::post('inventory/adjust', [InventoryController::class, 'storeAdjustment'])->name('inventory.adjustments.store');
    Route::get('inventory/products/{product}/movements', [InventoryController::class, 'productMovements'])->name('inventory.product-movements');

    // 6. Shift Management Routes
    Route::get('shifts', [ShiftController::class, 'index'])->name('shifts.index');
    Route::get('shifts/current', [ShiftController::class, 'current'])->name('shifts.current');
    Route::post('shifts/open', [ShiftController::class, 'open'])->name('shifts.open');
    Route::post('shifts/{shift}/close', [ShiftController::class, 'close'])->name('shifts.close');
    Route::get('shifts/{shift}/print', [ShiftController::class, 'print'])->name('shifts.print');
    Route::get('shifts/{shift}', [ShiftController::class, 'show'])->name('shifts.show');

    // 7. Expense Management Routes
    Route::patch('expenses/{expense}/cancel', [ExpenseController::class, 'cancel'])->name('expenses.cancel');
    Route::resource('expenses', ExpenseController::class);

    // 8. POS & Sales Management Routes
    Route::get('pos', [PosController::class, 'index'])->name('pos.index');
    Route::post('pos/checkout', [PosController::class, 'store'])->name('pos.checkout');
    Route::get('pos/search-products', [PosController::class, 'search'])->name('pos.search');

    Route::get('sales', [SaleController::class, 'index'])->name('sales.index');
    Route::get('sales/{sale}/print', [SaleController::class, 'print'])->name('sales.print');
    Route::patch('sales/{sale}/cancel', [SaleController::class, 'cancel'])->name('sales.cancel');
    Route::get('sales/{sale}', [SaleController::class, 'show'])->name('sales.show');

    // 9. Returns Management Routes
    Route::get('returns', [ReturnController::class, 'index'])->name('returns.index');
    Route::get('returns/create', [ReturnController::class, 'create'])->name('returns.create');
    Route::post('returns', [ReturnController::class, 'store'])->name('returns.store');
    Route::get('returns/search-invoice', [ReturnController::class, 'searchInvoice'])->name('returns.search-invoice');
    Route::get('returns/{return}/print', [ReturnController::class, 'print'])->name('returns.print');
    Route::patch('returns/{return}/cancel', [ReturnController::class, 'cancel'])->name('returns.cancel');
    Route::get('returns/{return}', [ReturnController::class, 'show'])->name('returns.show');

    // 10. Reports & Analytics Routes
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/sales', [ReportController::class, 'sales'])->name('sales');
        Route::get('/payment-methods', [ReportController::class, 'paymentMethods'])->name('payment-methods');
        Route::get('/products', [ReportController::class, 'products'])->name('products');
        Route::get('/employees', [ReportController::class, 'employees'])->name('employees');
        Route::get('/shifts', [ReportController::class, 'shifts'])->name('shifts');
        Route::get('/expenses', [ReportController::class, 'expenses'])->name('expenses');
        Route::get('/inventory', [ReportController::class, 'inventory'])->name('inventory');
        Route::get('/low-stock', [ReportController::class, 'lowStock'])->name('low-stock');
        Route::get('/profit', [ReportController::class, 'profit'])->name('profit');
    });

    // 11. Activity & Audit Logs Routes (Strictly Read-Only)
    Route::get('activity-logs', [\App\Http\Controllers\ActivityLogController::class, 'index'])->name('activity-logs.index');
    Route::get('activity-logs/{activityLog}', [\App\Http\Controllers\ActivityLogController::class, 'show'])->name('activity-logs.show');
});

// Authentication routes (login, logout, password)
require __DIR__.'/auth.php';

// Protected template explorer for admin views
Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/{page}', [AdminController::class, 'index'])->where('page', '.*')->name('admin');
});
