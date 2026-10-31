<?php

use App\Http\Controllers\Storefront\AccountController;
use App\Http\Controllers\Storefront\CartController;
use App\Http\Controllers\Storefront\CatalogController;
use App\Http\Controllers\Storefront\ChatController;
use App\Http\Controllers\Storefront\CheckoutController;
use App\Http\Controllers\Storefront\ComingSoonController;
use App\Http\Controllers\Storefront\CompareController;
use App\Http\Controllers\Storefront\CustomerAuthController;
use App\Http\Controllers\Storefront\HomeController;
use App\Http\Controllers\Storefront\NewsletterController;
use App\Http\Controllers\Storefront\OrderController;
use App\Http\Controllers\Storefront\ProductController;
use App\Http\Controllers\Storefront\ReviewController;
use App\Http\Controllers\Storefront\WishlistController;
use Illuminate\Support\Facades\Route;

Route::prefix('store/{store}')
    ->middleware(['store.resolve', 'demo.mode'])
    ->group(function () {
        Route::get('/', [HomeController::class, 'index'])->name('store.home');
        Route::get('/shop', [CatalogController::class, 'index'])->name('store.shop');
        Route::get('/category/{category}', [CatalogController::class, 'category'])->name('store.category');
        Route::get('/product/{product}', [ProductController::class, 'show'])->name('store.product');
        Route::get('/search', [CatalogController::class, 'search'])->name('store.search');
        Route::get('/coming-soon/{feature}', [ComingSoonController::class, 'show'])->name('store.coming-soon');
        Route::post('/newsletter', [NewsletterController::class, 'store'])->name('store.newsletter');
        Route::post('/ai/chat', [ChatController::class, 'store'])->middleware('throttle:ai-chat')->name('store.ai.chat');
        Route::post('/ai/conversations', [ChatController::class, 'conversations'])->middleware('throttle:ai-chat')->name('store.ai.conversations');
        Route::get('/ai/conversations/{token}', [ChatController::class, 'messages'])->middleware('throttle:ai-chat')->name('store.ai.messages');
        Route::post('/ai/cart', [ChatController::class, 'addToCart'])->middleware('throttle:ai-chat')->name('store.ai.cart');

        Route::get('/cart', [CartController::class, 'index'])->name('store.cart');
        Route::post('/cart', [CartController::class, 'store'])->name('store.cart.add');
        Route::patch('/cart', [CartController::class, 'update'])->name('store.cart.update');
        Route::delete('/cart', [CartController::class, 'destroy'])->name('store.cart.remove');
        Route::post('/cart/coupon', [CartController::class, 'coupon'])->name('store.cart.coupon');

        Route::get('/checkout', [CheckoutController::class, 'show'])->name('store.checkout');
        Route::post('/checkout', [CheckoutController::class, 'place'])->middleware('throttle:checkout')->name('store.checkout.place');
        Route::get('/order/{order}', [OrderController::class, 'show'])->name('store.order');

        Route::get('/compare', [CompareController::class, 'index'])->name('store.compare');
        Route::post('/compare', [CompareController::class, 'store'])->name('store.compare.add');
        Route::delete('/compare', [CompareController::class, 'destroy'])->name('store.compare.remove');

        Route::middleware('auth')->group(function () {
            Route::get('/wishlist', [WishlistController::class, 'index'])->name('store.wishlist');
            Route::post('/wishlist', [WishlistController::class, 'store'])->name('store.wishlist.add');
            Route::delete('/wishlist', [WishlistController::class, 'destroy'])->name('store.wishlist.remove');
            Route::post('/wishlist/move', [WishlistController::class, 'move'])->name('store.wishlist.move');
            Route::post('/product/{product}/reviews', [ReviewController::class, 'store'])->name('store.reviews.store');

            Route::get('/account', [AccountController::class, 'index'])->name('store.account');
            Route::get('/account/profile', [AccountController::class, 'profile'])->name('store.account.profile');
            Route::put('/account/profile', [AccountController::class, 'updateProfile'])->name('store.account.profile.update');
            Route::get('/account/orders', [AccountController::class, 'orders'])->name('store.account.orders');
            Route::get('/account/addresses', [AccountController::class, 'addresses'])->name('store.account.addresses');
            Route::post('/account/addresses', [AccountController::class, 'storeAddress'])->name('store.account.addresses.store');
            Route::delete('/account/addresses/{address}', [AccountController::class, 'destroyAddress'])->name('store.account.addresses.destroy');
            Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('store.logout');
        });

        Route::middleware('guest')->group(function () {
            Route::get('/login', [CustomerAuthController::class, 'showLogin'])->name('store.login');
            Route::post('/login', [CustomerAuthController::class, 'login'])->middleware('throttle:login')->name('store.login.attempt');
            Route::get('/register', [CustomerAuthController::class, 'showRegister'])->name('store.register');
            Route::post('/register', [CustomerAuthController::class, 'register'])->middleware('throttle:login')->name('store.register.store');
        });
    });
