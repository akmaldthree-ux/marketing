<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    AuthController, DashboardController, StoreController, PicController,
    UserController, UploadController, TargetController, CogController,
    FunnelTargetController, DailySalesController, RoasController,
    PnlController, FunnelController, CustomerController, SettingsController
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
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers');

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
    });
});
