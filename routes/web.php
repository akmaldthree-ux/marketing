<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{AuthController, DashboardController, PicPerformanceController, FunnelController, UploadController, PnlController, DailySalesController, RoasController, TargetController, DemandForecastController, CustomerController, SettingsController};

// Auth
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Redirect root
Route::get('/', fn() => redirect('/dashboard'));

// Protected routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/daily-sales', [DailySalesController::class, 'index'])->name('daily-sales');
    Route::get('/roas', [RoasController::class, 'index'])->name('roas');
    Route::get('/pic-performance', [PicPerformanceController::class, 'index'])->name('pic-performance');
    Route::get('/funnel', [FunnelController::class, 'index'])->name('funnel');
    Route::get('/pnl', [PnlController::class, 'index'])->name('pnl');
    Route::get('/demand-forecast', [DemandForecastController::class, 'index'])->name('demand-forecast');
    Route::post('/demand-forecast/generate', [DemandForecastController::class, 'generate'])->name('demand-forecast.generate');
    Route::get('/customer', [CustomerController::class, 'index'])->name('customer');
    Route::get('/upload', [UploadController::class, 'index'])->name('upload');
    Route::post('/upload', [UploadController::class, 'upload'])->name('upload.store');
    Route::get('/targets', [TargetController::class, 'index'])->name('targets');
    Route::post('/targets/update', [TargetController::class, 'update'])->name('targets.update');
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::post('/settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.profile');
});
