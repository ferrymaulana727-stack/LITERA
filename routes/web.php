<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Root redirect
Route::get('/', function () {
    if (auth()->check()) {
        $user = auth()->user();
        return $user->isCustomer() ? redirect()->route('catalog.index') : redirect()->route('dashboard');
    }
    return redirect()->route('catalog.index');
});

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Catalog & Shopping Cart (Public browsing, Customer / Staff / Admin)
Route::get('/catalog', [CatalogController::class, 'index'])->name('catalog.index');
Route::post('/cart/add', [CatalogController::class, 'addToCart'])->name('cart.add');
Route::post('/cart/update', [CatalogController::class, 'updateCart'])->name('cart.update');
Route::post('/cart/remove/{product}', [CatalogController::class, 'removeFromCart'])->name('cart.remove');
Route::post('/cart/clear', [CatalogController::class, 'clearCart'])->name('cart.clear');
Route::post('/checkout', [CatalogController::class, 'checkout'])->middleware('auth')->name('checkout');

// Customer / User Orders
Route::middleware('auth')->group(function () {
    Route::get('/my-orders', [OrderController::class, 'myOrders'])->name('orders.my');
    Route::get('/my-orders/{sale}', [OrderController::class, 'showOrder'])->name('orders.show');
});

// Back-Office Protected Routes (Admin & Staff)
Route::middleware(['auth', 'role:admin,staff'])->group(function () {
    // Dashboard (Admin & Staff)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Categories (Staff: Read-Only, Admin: Full CRUD)
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::middleware('role:admin')->group(function () {
        Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    });

    // Suppliers (Staff: Read-Only, Admin: Full CRUD)
    Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
    Route::middleware('role:admin')->group(function () {
        Route::get('/suppliers/create', [SupplierController::class, 'create'])->name('suppliers.create');
        Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
        Route::get('/suppliers/{supplier}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit');
        Route::put('/suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
        Route::delete('/suppliers/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');
    });

    // Products (Staff: Read-Only, Admin: Full CRUD + Soft Delete)
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
    Route::middleware('role:admin')->group(function () {
        Route::get('/products/create/form', [ProductController::class, 'create'])->name('products.create');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    });

    // Purchases / Stock-In (Staff & Admin can create and view)
    Route::get('/purchases', [PurchaseController::class, 'index'])->name('purchases.index');
    Route::get('/purchases/create', [PurchaseController::class, 'create'])->name('purchases.create');
    Route::post('/purchases', [PurchaseController::class, 'store'])->name('purchases.store');
    Route::get('/purchases/{purchase}', [PurchaseController::class, 'show'])->name('purchases.show');
    // Batalkan Nota Kulakan (Hanya Admin)
    Route::post('/purchases/{purchase}/cancel', [PurchaseController::class, 'cancel'])
        ->middleware('role:admin')
        ->name('purchases.cancel');

    // Sales / Penjualan Kasir (Staff & Admin can create kasir and view)
    Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
    Route::get('/sales/create', [SaleController::class, 'create'])->name('sales.create');
    Route::post('/sales', [SaleController::class, 'store'])->name('sales.store');
    Route::get('/sales/{sale}', [SaleController::class, 'show'])->name('sales.show');
    // Batalkan Nota Penjualan (Hanya Admin)
    Route::post('/sales/{sale}/cancel', [SaleController::class, 'cancel'])
        ->middleware('role:admin')
        ->name('sales.cancel');
});

// Admin ONLY: User Management
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('users', UserController::class);
});
