<?php

use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\AssistantController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BlogPostController;
use App\Http\Controllers\Admin\FormAssistController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StoreController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('admin.login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login')->name('admin.login.attempt');

    Route::middleware('admin')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('admin.logout');
        Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');

        Route::get('/stores', [StoreController::class, 'index'])->name('admin.stores');
        Route::post('/stores/switch', [StoreController::class, 'switch'])->name('admin.stores.switch');

        Route::get('/products', [ProductController::class, 'index'])->name('admin.products.index');
        Route::get('/products/create', [ProductController::class, 'create'])->name('admin.products.create');
        Route::post('/products', [ProductController::class, 'store'])->name('admin.products.store');
        Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('admin.products.edit');
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('admin.products.update');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('admin.products.destroy');

        Route::get('/categories', [CategoryController::class, 'index'])->name('admin.categories.index');
        Route::get('/categories/create', [CategoryController::class, 'create'])->name('admin.categories.create');
        Route::post('/categories', [CategoryController::class, 'store'])->name('admin.categories.store');
        Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('admin.categories.edit');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('admin.categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('admin.categories.destroy');

        Route::get('/brands', [BrandController::class, 'index'])->name('admin.brands.index');
        Route::get('/brands/create', [BrandController::class, 'create'])->name('admin.brands.create');
        Route::post('/brands', [BrandController::class, 'store'])->name('admin.brands.store');
        Route::get('/brands/{brand}/edit', [BrandController::class, 'edit'])->name('admin.brands.edit');
        Route::put('/brands/{brand}', [BrandController::class, 'update'])->name('admin.brands.update');
        Route::delete('/brands/{brand}', [BrandController::class, 'destroy'])->name('admin.brands.destroy');

        Route::get('/inventory', [InventoryController::class, 'index'])->name('admin.inventory.index');
        Route::post('/inventory', [InventoryController::class, 'adjust'])->name('admin.inventory.adjust');

        Route::get('/orders', [OrderController::class, 'index'])->name('admin.orders.index');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('admin.orders.show');
        Route::patch('/orders/{order}', [OrderController::class, 'update'])->name('admin.orders.update');

        Route::get('/customers', [CustomerController::class, 'index'])->name('admin.customers.index');
        Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('admin.customers.show');
        Route::patch('/customers/{customer}', [CustomerController::class, 'update'])->name('admin.customers.update');

        Route::get('/coupons', [CouponController::class, 'index'])->name('admin.coupons.index');
        Route::get('/coupons/create', [CouponController::class, 'create'])->name('admin.coupons.create');
        Route::post('/coupons', [CouponController::class, 'store'])->name('admin.coupons.store');
        Route::get('/coupons/{coupon}/edit', [CouponController::class, 'edit'])->name('admin.coupons.edit');
        Route::put('/coupons/{coupon}', [CouponController::class, 'update'])->name('admin.coupons.update');
        Route::delete('/coupons/{coupon}', [CouponController::class, 'destroy'])->name('admin.coupons.destroy');

        Route::get('/posts', [BlogPostController::class, 'index'])->name('admin.posts.index');
        Route::get('/posts/create', [BlogPostController::class, 'create'])->name('admin.posts.create');
        Route::post('/posts', [BlogPostController::class, 'store'])->name('admin.posts.store');
        Route::get('/posts/{post}/edit', [BlogPostController::class, 'edit'])->name('admin.posts.edit');
        Route::put('/posts/{post}', [BlogPostController::class, 'update'])->name('admin.posts.update');
        Route::delete('/posts/{post}', [BlogPostController::class, 'destroy'])->name('admin.posts.destroy');

        Route::post('/ai/suggest', [FormAssistController::class, 'suggest'])
            ->middleware('throttle:ai-chat')
            ->name('admin.ai.suggest');

        Route::get('/reviews', [ReviewController::class, 'index'])->name('admin.reviews.index');
        Route::patch('/reviews/{review}', [ReviewController::class, 'update'])->name('admin.reviews.update');

        Route::get('/analytics', [AnalyticsController::class, 'index'])->name('admin.analytics');
        Route::get('/reports', [ReportController::class, 'index'])->name('admin.reports');
        Route::post('/reports/download', [ReportController::class, 'download'])
            ->middleware('throttle:20,1')
            ->name('admin.reports.download');
        Route::get('/assistant', [AssistantController::class, 'index'])->name('admin.assistant');
        Route::post('/assistant/chat', [AssistantController::class, 'chat'])
            ->middleware('throttle:ai-chat')
            ->name('admin.assistant.chat');
        Route::get('/settings', [SettingController::class, 'edit'])->name('admin.settings');
        Route::put('/settings', [SettingController::class, 'update'])->name('admin.settings.update');
        Route::post('/settings/banners', [SettingController::class, 'storeBanner'])->name('admin.banners.store');
        Route::delete('/settings/banners/{banner}', [SettingController::class, 'destroyBanner'])->name('admin.banners.destroy');
    });
});
