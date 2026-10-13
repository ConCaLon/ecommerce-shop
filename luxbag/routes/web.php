<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\GhnController;
use App\Http\Controllers\MomoController;
use Illuminate\Support\Facades\Route;

// ─── Public Routes ────────────────────────────────────────────────────────────
Route::get('/', [HomeController::class, 'index'])->name('home');

// Chatbot AI
Route::post('/chatbot/message', [ChatbotController::class, 'message'])->name('chatbot.message');

// Products
Route::prefix('san-pham')->name('products.')->group(function () {
    Route::get('/', [ProductController::class, 'index'])->name('index');
    Route::get('/{product:slug}', [ProductController::class, 'show'])->name('show');
    Route::get('/danh-muc/{category:slug}', [ProductController::class, 'byCategory'])->name('category');
    Route::get('/thuong-hieu/{brand:slug}', [ProductController::class, 'byBrand'])->name('brand');
});

Route::get('/tim-kiem', [ProductController::class, 'search'])->name('products.search');

// Static Pages
Route::prefix('')->name('pages.')->group(function () {
    Route::get('/gioi-thieu', [PageController::class, 'about'])->name('about');
    Route::get('/lien-he', [PageController::class, 'contact'])->name('contact');
    Route::post('/lien-he', [PageController::class, 'sendContact'])->name('contact.send');
    Route::get('/chinh-sach-doi-tra', [PageController::class, 'policy'])->name('policy');
    Route::get('/faq', [PageController::class, 'faq'])->name('faq');
});

// ─── GHN API Routes (AJAX) ───────────────────────────────────────────────────
Route::prefix('api/ghn')->name('ghn.')->group(function () {
    Route::get('/provinces', [GhnController::class, 'provinces'])->name('provinces');
    Route::get('/districts/{provinceId}', [GhnController::class, 'districts'])->name('districts');
    Route::get('/wards/{districtId}', [GhnController::class, 'wards'])->name('wards');
    Route::post('/calculate-fee', [GhnController::class, 'calculateFee'])->name('calculate-fee');
});

// ─── MoMo Payment Callback/IPN ───────────────────────────────────────────────
Route::get('/momo/callback', [MomoController::class, 'callback'])->name('momo.callback');
Route::post('/momo/ipn', [MomoController::class, 'ipn'])->name('momo.ipn');

// ─── Auth Routes ──────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/dang-nhap', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/dang-nhap', [AuthController::class, 'login']);
    Route::get('/dang-ky', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/dang-ky', [AuthController::class, 'register'])->middleware('throttle:5,1');
    Route::get('/quen-mat-khau', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/quen-mat-khau', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/dat-lai-mat-khau/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/dat-lai-mat-khau', [AuthController::class, 'resetPassword'])->name('password.update');
    // Google OAuth
    Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
});

Route::post('/dang-xuat', [AuthController::class, 'logout'])->name('logout');
Route::middleware(['auth', 'check.banned'])->group(function () {
    Route::get('/xac-thuc-email', [AuthController::class, 'showVerificationNotice'])->name('verification.notice');
    Route::get('/xac-thuc-email/{id}/{hash}', [AuthController::class, 'verifyEmail'])
        ->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('/gui-lai-email-xac-thuc', [AuthController::class, 'resendVerificationEmail'])
        ->middleware('throttle:6,1')->name('verification.send');
});

// ─── Cart Routes (guest + auth) ───────────────────────────────────────────────
Route::prefix('gio-hang')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/them', [CartController::class, 'add'])->name('add');
    Route::patch('/cap-nhat/{id}', [CartController::class, 'update'])->name('update');
    Route::delete('/xoa/{id}', [CartController::class, 'remove'])->name('remove');
    Route::delete('/xoa-het', [CartController::class, 'clear'])->name('clear');
    Route::post('/ap-ma-giam-gia', [CartController::class, 'applyCoupon'])->name('coupon.apply');
    Route::delete('/xoa-ma-giam-gia', [CartController::class, 'removeCoupon'])->name('coupon.remove');
    Route::get('/count', [CartController::class, 'count'])->name('count');
});

// ─── Authenticated User Routes ────────────────────────────────────────────────
Route::middleware(['auth', 'check.banned', 'verified'])->group(function () {
    // Checkout & Orders
    Route::prefix('dat-hang')->name('orders.')->group(function () {
        Route::get('/thanh-toan', [OrderController::class, 'checkout'])->name('checkout');
        Route::post('/dat-hang', [OrderController::class, 'store'])->name('store');
        Route::get('/xac-nhan/{orderCode}', [OrderController::class, 'confirmation'])->name('confirmation');
        Route::get('/lich-su', [OrderController::class, 'history'])->name('history');
        Route::get('/{orderCode}', [OrderController::class, 'show'])->name('show');
        Route::post('/{orderCode}/huy', [OrderController::class, 'cancel'])->name('cancel');
    });

    // Profile
    Route::prefix('tai-khoan')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('index');
        Route::put('/cap-nhat', [ProfileController::class, 'update'])->name('update');
        Route::put('/doi-mat-khau', [ProfileController::class, 'changePassword'])->name('password');
        Route::get('/dia-chi', [ProfileController::class, 'addresses'])->name('addresses');
        Route::post('/dia-chi', [ProfileController::class, 'storeAddress'])->name('addresses.store');
        Route::put('/dia-chi/{id}', [ProfileController::class, 'updateAddress'])->name('addresses.update');
        Route::delete('/dia-chi/{id}', [ProfileController::class, 'deleteAddress'])->name('addresses.delete');
        Route::patch('/dia-chi/{id}/mac-dinh', [ProfileController::class, 'setDefaultAddress'])->name('addresses.default');
        Route::get('/avatar', [ProfileController::class, 'avatarForm'])->name('avatar');
        Route::post('/avatar', [ProfileController::class, 'uploadAvatar'])->name('avatar.upload');
    });

    // Wishlist
    Route::prefix('yeu-thich')->name('wishlist.')->group(function () {
        Route::get('/', [WishlistController::class, 'index'])->name('index');
        Route::post('/them/{productId}', [WishlistController::class, 'toggle'])->name('toggle');
        Route::delete('/xoa/{productId}', [WishlistController::class, 'remove'])->name('remove');
    });

    // Reviews
    Route::prefix('danh-gia')->name('reviews.')->group(function () {
        Route::post('/san-pham/{productId}', [ReviewController::class, 'store'])->name('store');
        Route::put('/{id}', [ReviewController::class, 'update'])->name('update');
        Route::delete('/{id}', [ReviewController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/helpful', [ReviewController::class, 'markHelpful'])->name('helpful');
    });
});
