<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Redirect root to dashboard (guests will be redirected to login by auth middleware)
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Authenticated and active routes
Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/dashboard', function () {
        return view('index');
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Authentication routes (login, logout, password)
require __DIR__.'/auth.php';

// Protected template explorer for admin views
Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/{page}', [AdminController::class, 'index'])->where('page', '.*')->name('admin');
});
