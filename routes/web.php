<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EventDayController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\ShiftController;
use App\Http\Controllers\Admin\ShopeePaySettingController;
use App\Http\Controllers\Admin\TransactionHistoryController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Cashier\CashierDashboardController;
use App\Http\Controllers\Cashier\PersonalTransactionController;
use App\Http\Controllers\Cashier\POSController;
use App\Http\Controllers\Cashier\QRISVerificationController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Root redirect
Route::get('/', function () {
    if (Auth::check()) {
        return Auth::user()->isAdmin()
            ? redirect()->route('admin.dashboard')
            : redirect()->route('cashier.pos');
    }

    return redirect()->route('login');
});

// Authentication routes
Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Admin Routes Group
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role:admin', 'active.user'])
    ->group(function (): void {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Users Management
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::patch('/users/{user}/toggle', [UserController::class, 'toggleActive'])->name('users.toggle-active');

        // Products Management
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::patch('/products/{product}/toggle', [ProductController::class, 'toggleActive'])->name('products.toggle-active');

        // Event Days Management
        Route::get('/event-days', [EventDayController::class, 'index'])->name('event-days.index');
        Route::put('/event-days/{eventDay}', [EventDayController::class, 'update'])->name('event-days.update');

        // Shifts Management
        Route::get('/shifts', [ShiftController::class, 'index'])->name('shifts.index');
        Route::put('/shifts/{shift}', [ShiftController::class, 'update'])->name('shifts.update');
        Route::post('/shifts/{shift}/open', [ShiftController::class, 'openShift'])->name('shifts.open');
        Route::post('/shifts/{shift}/close', [ShiftController::class, 'closeShift'])->name('shifts.close');

        // Transactions History & Void
        Route::get('/transactions', [TransactionHistoryController::class, 'index'])->name('transactions.index');
        Route::get('/transactions/{transaction}', [TransactionHistoryController::class, 'show'])->name('transactions.show');
        Route::post('/transactions/{transaction}/void', [TransactionHistoryController::class, 'void'])->name('transactions.void');

        // Reports & Exports
        Route::get('/reports/daily', [ReportController::class, 'daily'])->name('reports.daily');
        Route::get('/reports/five-days', [ReportController::class, 'fiveDays'])->name('reports.five-days');
        Route::get('/reports/daily/{eventDay}/export', [ReportController::class, 'exportDaily'])->name('reports.export-daily');
        Route::get('/reports/five-days/export', [ReportController::class, 'exportFiveDays'])->name('reports.export-five-days');

        // Activity Logs
        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');

        // System Settings
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings/clear-cache', [SettingController::class, 'clearCache'])->name('settings.clear-cache');
        Route::post('/settings/reset-transactions', [SettingController::class, 'resetTransactions'])->name('settings.reset-transactions');

        // ShopeePay QRIS Merchant Settings
        Route::get('/settings/qris', [ShopeePaySettingController::class, 'index'])->name('settings.qris');
        Route::post('/settings/qris', [ShopeePaySettingController::class, 'update'])->name('settings.qris.update');
        Route::post('/settings/qris/test', [ShopeePaySettingController::class, 'test'])->name('settings.qris.test');
    });

// Cashier Routes Group
Route::prefix('cashier')
    ->name('cashier.')
    ->middleware(['auth', 'role:cashier,admin', 'active.user'])
    ->group(function (): void {
        Route::get('/dashboard', [CashierDashboardController::class, 'index'])->name('dashboard');
        Route::get('/pos', [POSController::class, 'index'])->name('pos');
        Route::post('/pos', [POSController::class, 'store'])->name('pos.store');
        Route::get('/history', [PersonalTransactionController::class, 'index'])->name('history');

        // Live ShopeePay QRIS Verification
        Route::get('/qris/live-mutations', [QRISVerificationController::class, 'liveMutations'])->name('qris.live-mutations');
        Route::post('/qris/check-match', [QRISVerificationController::class, 'checkMatch'])->name('qris.check-match');
    });
