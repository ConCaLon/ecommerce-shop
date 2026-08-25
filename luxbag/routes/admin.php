<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\FlashSaleController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\StaffController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // ─── Products (staff có quyền) ─────────────────────────────────
    Route::prefix('san-pham')->name('products.')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::get('/them-moi', [ProductController::class, 'create'])->name('create');
        Route::post('/', [ProductController::class, 'store'])->name('store');
        Route::get('/{product:id}/chinh-sua', [ProductController::class, 'edit'])->name('edit');
        Route::put('/{product:id}', [ProductController::class, 'update'])->name('update');
        Route::delete('/{product:id}', [ProductController::class, 'destroy'])->name('destroy');
        Route::patch('/{product:id}/trang-thai', [ProductController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/xoa-nhieu', [ProductController::class, 'bulkDelete'])->name('bulk-delete');
        // Variants
        Route::post('/{product:id}/bien-the', [ProductController::class, 'storeVariant'])->name('variants.store');
        Route::delete('/bien-the/{variant}', [ProductController::class, 'destroyVariant'])->name('variants.destroy');
        // Images
        Route::post('/{product:id}/anh', [ProductController::class, 'uploadImages'])->name('images.upload');
        Route::delete('/anh/{image}', [ProductController::class, 'destroyImage'])->name('images.destroy');
        Route::patch('/anh/{image}/chinh', [ProductController::class, 'setPrimaryImage'])->name('images.primary');
    });

    // ─── Categories (staff có quyền) ──────────────────────────────
    Route::prefix('danh-muc')->name('categories.')->group(function () {
        Route::get('/', [CategoryController::class, 'index'])->name('index');
        Route::get('/them-moi', [CategoryController::class, 'create'])->name('create');
        Route::post('/', [CategoryController::class, 'store'])->name('store');
        Route::get('/{category:id}', [CategoryController::class, 'show'])->name('show');
        Route::get('/{category:id}/chinh-sua', [CategoryController::class, 'edit'])->name('edit');
        Route::put('/{category:id}', [CategoryController::class, 'update'])->name('update');
        Route::delete('/{category:id}', [CategoryController::class, 'destroy'])->name('destroy');
    });

    // ─── Brands (staff có quyền) ──────────────────────────────────
    Route::prefix('thuong-hieu')->name('brands.')->group(function () {
        Route::get('/', [BrandController::class, 'index'])->name('index');
        Route::get('/them-moi', [BrandController::class, 'create'])->name('create');
        Route::post('/', [BrandController::class, 'store'])->name('store');
        Route::get('/{brand:id}', [BrandController::class, 'show'])->name('show');
        Route::get('/{brand:id}/chinh-sua', [BrandController::class, 'edit'])->name('edit');
        Route::put('/{brand:id}', [BrandController::class, 'update'])->name('update');
        Route::delete('/{brand:id}', [BrandController::class, 'destroy'])->name('destroy');
    });

    // ─── Orders (staff có quyền) ──────────────────────────────────
    Route::prefix('don-hang')->name('orders.')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::get('/xuat-csv', [OrderController::class, 'exportCsv'])->name('export');
        Route::get('/{order}', [OrderController::class, 'show'])->name('show');
        Route::patch('/{order}/trang-thai', [OrderController::class, 'updateStatus'])->name('status');
        Route::get('/{order}/in-hoa-don', [OrderController::class, 'invoice'])->name('invoice');
    });

    // ─── Reviews (staff có quyền) ─────────────────────────────────
    Route::prefix('danh-gia')->name('reviews.')->group(function () {
        Route::get('/', [ReviewController::class, 'index'])->name('index');
        Route::patch('/{review}/duyet', [ReviewController::class, 'approve'])->name('approve');
        Route::patch('/{review}/tu-choi', [ReviewController::class, 'reject'])->name('reject');
        Route::post('/{review}/tra-loi', [ReviewController::class, 'reply'])->name('reply');
        Route::delete('/{review}', [ReviewController::class, 'destroy'])->name('destroy');
    });

    // ═══════════════════════════════════════════════════════════════
    // Các route chỉ dành cho Manager+ (staff KHÔNG có quyền)
    // ═══════════════════════════════════════════════════════════════
    Route::middleware(['can:manage-customers'])->group(function () {
        // Customers
        Route::prefix('khach-hang')->name('customers.')->group(function () {
            Route::get('/', [CustomerController::class, 'index'])->name('index');
            Route::get('/{user}', [CustomerController::class, 'show'])->name('show');
            Route::patch('/{user}/trang-thai', [CustomerController::class, 'toggleStatus'])->name('toggle-status');
            Route::patch('/{user}/vai-tro', [CustomerController::class, 'updateRole'])->name('role');
        });
    });

    Route::middleware(['can:manage-coupons'])->group(function () {
        // Coupons
        Route::resource('ma-giam-gia', CouponController::class)->names('coupons');
    });

    Route::middleware(['can:manage-coupons'])->group(function () {
        // Flash Sales
        Route::resource('flash-sale', FlashSaleController::class)->names('flash-sales');
        Route::post('flash-sale/{flashSale}/san-pham', [FlashSaleController::class, 'addProduct'])->name('flash-sales.products.add');
        Route::delete('flash-sale/{flashSale}/san-pham/{product:id}', [FlashSaleController::class, 'removeProduct'])->name('flash-sales.products.remove');
    });

    Route::middleware(['can:manage-banners'])->group(function () {
        // Banners
        Route::resource('banner', BannerController::class)->names('banners');
    });

    Route::middleware(['can:manage-reports'])->group(function () {
        // Reports
        Route::prefix('bao-cao')->name('reports.')->group(function () {
            Route::get('/', [ReportController::class, 'index'])->name('index');
            Route::get('/doanh-thu', [ReportController::class, 'revenue'])->name('revenue');
            Route::get('/san-pham-ban-chay', [ReportController::class, 'topProducts'])->name('top-products');
            Route::get('/khach-hang', [ReportController::class, 'customers'])->name('customers');
            Route::get('/xuat', [ReportController::class, 'export'])->name('export');
        });
    });

    Route::middleware(['can:manage-settings'])->group(function () {
        // Settings
        Route::get('cai-dat', [SettingController::class, 'index'])->name('settings');
        Route::put('cai-dat', [SettingController::class, 'update'])->name('settings.update');
    });

    Route::middleware(['can:manage-staff'])->group(function () {
        // ─── Staff Management ──────────────────────────────────────
        Route::prefix('nhan-vien')->name('staff.')->group(function () {
            Route::get('/', [StaffController::class, 'index'])->name('index');
            Route::get('/them-moi', [StaffController::class, 'create'])->name('create');
            Route::post('/', [StaffController::class, 'store'])->name('store');
            Route::get('/{staff}/chinh-sua', [StaffController::class, 'edit'])->name('edit');
            Route::put('/{staff}', [StaffController::class, 'update'])->name('update');
            Route::delete('/{staff}', [StaffController::class, 'destroy'])->name('destroy');
            Route::patch('/{staff}/trang-thai', [StaffController::class, 'toggleStatus'])->name('toggle-status');
        });
    });
});

