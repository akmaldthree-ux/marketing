<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    AuthController, DashboardController, StoreController, PicController,
    UserController, UploadController, TargetController, CogController,
    FunnelTargetController, DailySalesController, RoasController,
    PnlController, FunnelController, CustomerController, SettingsController,
    StoreCompareController, ProductAnalysisController, ExportController,
    ImportController, ForecastController, ProductController
};

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/', fn() => redirect()->route('dashboard'));
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Analytics (read-only)
    Route::get('/daily-sales', [DailySalesController::class, 'index'])->name('daily-sales');
    Route::get('/roas', [RoasController::class, 'index'])->name('roas');
    Route::get('/funnel', [FunnelController::class, 'index'])->name('funnel');
    Route::get('/pnl', [PnlController::class, 'index'])->name('pnl');
    Route::post('/pnl/ops', [PnlController::class, 'saveOps'])->name('pnl.saveOps');
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers');
    Route::get('/store-compare', [StoreCompareController::class, 'index'])->name('store-compare');
    Route::get('/product-analysis', [ProductAnalysisController::class, 'index'])->name('product-analysis');

    // Forecast (semua role)
    Route::get('/forecast', [ForecastController::class, 'index'])->name('forecast');
    Route::get('/forecast/export', [ForecastController::class, 'export'])->name('forecast.export');

    // Export CSV
    Route::get('/export/targets', [ExportController::class, 'targets'])->name('export.targets');
    Route::get('/export/pnl', [ExportController::class, 'pnl'])->name('export.pnl');
    Route::get('/export/daily-sales', [ExportController::class, 'dailySales'])->name('export.daily-sales');

    // Upload
    Route::get('/upload', [UploadController::class, 'index'])->name('upload.index');
    Route::post('/upload', [UploadController::class, 'store'])->name('upload.store');
    Route::delete('/upload/{log}', [UploadController::class, 'destroy'])->name('upload.destroy');

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::put('/settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.profile');
    Route::put('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password');

    // Admin only
    Route::middleware('admin')->group(function () {
        Route::resource('stores', StoreController::class)->except(['show']);
        Route::resource('pics', PicController::class)->except(['show']);
        Route::resource('users', UserController::class)->except(['show']);

        Route::get('/targets', [TargetController::class, 'index'])->name('targets.index');
        Route::post('/targets', [TargetController::class, 'store'])->name('targets.store');
        Route::put('/targets/{target}', [TargetController::class, 'update'])->name('targets.update');
        Route::delete('/targets/{target}', [TargetController::class, 'destroy'])->name('targets.destroy');

        Route::resource('cogs', CogController::class)->except(['show']);
        Route::resource('funnel-targets', FunnelTargetController::class)->except(['show']);

        // Master Produk (admin only CRUD)
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
        Route::post('/products/import', [ProductController::class, 'importProducts'])->name('products.import');
        Route::get('/products/template', [ProductController::class, 'templateProducts'])->name('products.template');

        // Import routes
        Route::post('/import/target-gmv', [ImportController::class, 'importTargetGmv'])->name('import.target-gmv');
        Route::post('/import/hpp', [ImportController::class, 'importHpp'])->name('import.hpp');
        Route::post('/import/biaya-ops', [ImportController::class, 'importBiayaOps'])->name('import.biaya-ops');
        Route::post('/import/funnel-target', [ImportController::class, 'importFunnelTarget'])->name('import.funnel-target');
    });

    // Template downloads (semua user bisa download)
    Route::get('/template/target-gmv', [ImportController::class, 'templateTargetGmv'])->name('template.target-gmv');
    Route::get('/template/hpp', [ImportController::class, 'templateHpp'])->name('template.hpp');
    Route::get('/template/biaya-ops', [ImportController::class, 'templateBiayaOps'])->name('template.biaya-ops');
    Route::get('/template/funnel-target', [ImportController::class, 'templateFunnelTarget'])->name('template.funnel-target');
});
