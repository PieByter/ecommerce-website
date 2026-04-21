<?php

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\PurchaseOrderController as AdminPurchaseOrderController;
use App\Http\Controllers\Admin\SupplierController as AdminSupplierController;
use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\CheckoutController;
use App\Http\Controllers\Customer\HomeController as CustomerHomeController;
use App\Http\Controllers\Customer\OrderController as CustomerOrderController;
use App\Http\Controllers\Customer\ProductController as CustomerProductController;
use App\Http\Controllers\Customer\ProfileController;
use App\Http\Controllers\Customer\ReviewController as CustomerReviewController;
use App\Http\Controllers\HomeController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Auth::routes(['verify' => true]);

Route::get('password/email', function () {
    return redirect()->route('password.request');
})->name('password.email.get');

Route::get('/', function () {
    if (! Auth::check()) {
        return redirect()->route('login');
    }

    /** @var User $user */
    $user = Auth::user();

    return $user->isAdmin()
        ? redirect()->route('admin.dashboard')
        : redirect()->route('customer.home');
})->name('home');

Route::middleware(['auth', 'verified', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::resource('categories', AdminCategoryController::class)->except('show');
        Route::resource('suppliers', AdminSupplierController::class)->except('show');
        Route::resource('products', AdminProductController::class)->except('show');
        Route::resource('purchase-orders', AdminPurchaseOrderController::class)->only(['index', 'create', 'store', 'show', 'update', 'destroy']);
        Route::post('purchase-orders/{purchaseOrder}/receive', [AdminPurchaseOrderController::class, 'receive'])->name('purchase-orders.receive');
        Route::resource('customers', AdminCustomerController::class)->only(['index', 'edit', 'update']);
        Route::resource('orders', AdminOrderController::class)->only(['index', 'show', 'update']);
        Route::resource('users', AdminUserController::class)->only(['index', 'edit', 'update']);
    });

Route::middleware(['auth', 'verified', 'role:customer'])->group(function (): void {
    Route::get('shop', [CustomerHomeController::class, 'index'])->name('customer.home');
    Route::get('products', [CustomerProductController::class, 'index'])->name('products.index');
    Route::get('products/{product:slug}', [CustomerProductController::class, 'show'])->name('products.show');
    Route::post('products/{product:slug}/reviews', [CustomerReviewController::class, 'store'])->name('products.reviews.store');
    Route::get('cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('cart/{product}', [CartController::class, 'store'])->name('cart.store');
    Route::post('cart/{product}/adjust', [CartController::class, 'adjust'])->name('cart.adjust');
    Route::patch('cart/{cart}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('cart/{cart}', [CartController::class, 'destroy'])->name('cart.destroy');
    Route::get('checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('orders', [CustomerOrderController::class, 'index'])->name('customer.orders.index');
    Route::patch('orders/{order}/cancel', [CustomerOrderController::class, 'cancel'])->name('customer.orders.cancel');
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('reviews', [CustomerReviewController::class, 'index'])->name('customer.reviews.index');
    Route::get('notifications', function () {
        /** @var User $user */
        $user = Auth::user();

        $orders = $user
            ->orders()
            ->whereIn('status', ['confirmed', 'shipped', 'delivered'])
            ->latest()
            ->take(10)
            ->get();

        return view('customer.notifications', compact('orders'));
    })->name('customer.notifications.index');
});

Route::middleware('auth')->get('/home', [HomeController::class, 'index'])->name('legacy.home');

if (file_exists(__DIR__.'/settings.php')) {
    require __DIR__.'/settings.php';
}
