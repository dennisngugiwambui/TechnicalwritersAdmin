<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\WriterController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\AdminHomeController;
use App\Http\Controllers\Api\MpesaController; // Changed from Admin to Api namespace
use App\Http\Controllers\Admin\FinanceController;
use App\Http\Controllers\FileController;

// Public routes
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication routes (without register)
Auth::routes(['register' => false]);

// Custom route for register to show "not hiring" message
Route::get('/register', function() {
    return view('auth.not-hiring');
})->name('not-hiring');

// Welcome page for pending admins
Route::get('/welcome', function() {
    return view('admin.welcome');
})->name('welcome')->middleware('auth');

// Failed access page for suspended/banned admins
Route::get('/failed', function() {
    return view('admin.failed', ['user' => Auth::user()]);
})->name('failed')->middleware('auth');

// Dashboard route
Route::get('/home', [AdminHomeController::class, 'index'])->name('home');

// Dashboard chart data
Route::get('/admin/dashboard/chart-data', [AdminHomeController::class, 'getChartData'])
    ->name('admin.dashboard.chart-data')
    ->middleware('auth');

// Admin routes
Route::prefix('admin')->middleware(['auth'])->name('admin.')->group(function () {
    // Dashboard
    Route::get('/', [AdminHomeController::class, 'index'])->name('dashboard');
    
    // Orders Management
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/create', [OrderController::class, 'create'])->name('orders.create');
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('/orders/{order}/edit', [OrderController::class, 'edit'])->name('orders.edit');
    Route::put('/orders/{order}', [OrderController::class, 'update'])->name('orders.update');
    Route::post('/orders/{order}/make-available', [OrderController::class, 'makeAvailable'])->name('orders.make-available');
    Route::post('/orders/{order}/assign', [OrderController::class, 'assign'])->name('orders.assign');
    Route::post('/orders/{order}/request-revision', [OrderController::class, 'requestRevision'])->name('orders.request-revision');
    Route::post('/orders/{order}/complete', [OrderController::class, 'complete'])->name('orders.complete');
    Route::post('/orders/{order}/dispute', [OrderController::class, 'dispute'])->name('orders.dispute');
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::post('/orders/upload-files', [OrderController::class, 'uploadFiles'])->name('orders.upload-files');


    Route::post('/settings/toggle-writer-maintenance', [SettingsController::class, 'toggleWriterMaintenance'])->name('settings.toggle-writer-maintenance');
    Route::post('/settings/toggle-writer-debug', [SettingsController::class, 'toggleWriterDebug'])->name('settings.toggle-writer-debug');
    
    // Writers Management
    Route::get('/writers', [WriterController::class, 'index'])->name('writers.index');
    Route::get('/writers/{writer}', [WriterController::class, 'show'])->name('writers.show');
    Route::post('/writers/{writer}/suspend', [WriterController::class, 'suspend'])->name('writers.suspend');
    Route::post('/writers/{writer}/activate', [WriterController::class, 'activate'])->name('writers.activate');
    Route::post('/writers/{writer}/verify', [WriterController::class, 'verify'])->name('writers.verify');
    Route::post('/writers/{writer}/reject', [WriterController::class, 'reject'])->name('writers.reject');
    
    // Payment Management (Legacy routes)
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
    Route::post('/payments/{payment}/approve', [PaymentController::class, 'approve'])->name('payments.approve');
    Route::post('/payments/{payment}/reject', [PaymentController::class, 'reject'])->name('payments.reject');
    Route::post('/payments/{payment}/process-mpesa', [PaymentController::class, 'processMpesa'])->name('payments.process-mpesa');
    
    // Finance Management (New routes)
    Route::prefix('finance')->name('finance.')->group(function () {
        // Dashboard
        Route::get('/dashboard', [PaymentController::class, 'dashboard'])->name('dashboard');
        
        // Transactions
        Route::get('/transactions', [PaymentController::class, 'index'])->name('transactions');
        Route::get('/transaction/{id}', [PaymentController::class, 'show'])->name('transaction');
        
        // Payments
        Route::get('/payments', [PaymentController::class, 'payments'])->name('payments');
        
        // Withdrawals
        Route::get('/withdrawals', [PaymentController::class, 'withdrawals'])->name('withdrawals');
        Route::put('/withdraw/{id}/approve', [PaymentController::class, 'approveWithdrawal'])->name('approve');
        Route::put('/withdraw/{id}/complete', [PaymentController::class, 'completeWithdrawal'])->name('complete');
        Route::put('/withdraw/{id}/reject', [PaymentController::class, 'rejectWithdrawal'])->name('reject');
        
        // Reports
        Route::get('/reports', [PaymentController::class, 'reports'])->name('reports');
        Route::get('/chart-data', [PaymentController::class, 'getChartData'])->name('chart-data');
        
        // Bonuses
        Route::get('/bonus/create', [PaymentController::class, 'createBonus'])->name('bonus.create');
        Route::post('/bonus', [PaymentController::class, 'storeBonus'])->name('bonus.store');
        
        // Penalties
        Route::get('/penalty/create', [PaymentController::class, 'createPenalty'])->name('penalty.create');
        Route::post('/penalty', [PaymentController::class, 'storePenalty'])->name('penalty.store');
        
        // Refunds
        Route::post('/transaction/{id}/refund', [PaymentController::class, 'processRefund'])->name('refund');
    });
    
    // Messages Management
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/create', [MessageController::class, 'create'])->name('messages.create');
    Route::post('/messages', [MessageController::class, 'store'])->name('messages.store');
    Route::get('/messages/{message}', [MessageController::class, 'show'])->name('messages.show');
    Route::post('/messages/{order}/send-as-client', [MessageController::class, 'sendAsClient'])->name('messages.send-as-client');
    Route::post('/messages/{order}/send-as-support', [MessageController::class, 'sendAsSupport'])->name('messages.send-as-support');
    Route::post('/messages/reply/{conversationId}', [MessageController::class, 'reply'])->name('messages.reply');
    Route::get('/messages/recipients/{type}', [MessageController::class, 'recipients'])->name('messages.recipients');
    Route::get('/messages/check', [MessageController::class, 'checkNewMessages'])->name('messages.check');
    Route::post('/messages/ajax-reply/{conversationId}', [MessageController::class, 'ajaxReply'])->name('messages.ajax-reply');
    
    // Settings Management
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::get('/settings/exchange-rate', [SettingsController::class, 'exchangeRate'])->name('settings.exchange-rate');
    Route::post('/settings/exchange-rate', [SettingsController::class, 'updateExchangeRate'])->name('settings.update-exchange-rate');
    
    // Add missing routes for settings
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::put('/settings/exchange-rate', [SettingsController::class, 'updateExchangeRate'])->name('settings.update-exchange-rate');
    Route::put('/settings/mpesa', [SettingsController::class, 'updateMpesa'])->name('settings.update-mpesa');
    Route::put('/settings/email-template', [SettingsController::class, 'updateEmailTemplate'])->name('settings.update-email-template');
    Route::post('/settings/toggle-maintenance', [SettingsController::class, 'toggleMaintenance'])->name('settings.toggle-maintenance');
    Route::post('/settings/create-backup', [SettingsController::class, 'createBackup'])->name('settings.create-backup');
    Route::get('/settings/download-backup/{filename}', [SettingsController::class, 'downloadBackup'])->name('settings.download-backup');
    Route::post('/settings/clear-cache', [SettingsController::class, 'clearCache'])->name('settings.clear-cache');
    
    // Add missing admin users routes
    Route::post('/users', [SettingsController::class, 'storeUser'])->name('users.store');
    Route::put('/users/{id}', [SettingsController::class, 'updateUser'])->name('users.update');
    Route::delete('/users/{id}', [SettingsController::class, 'destroyUser'])->name('users.destroy');
    
    // Profile routes
    Route::get('/profile', [HomeController::class, 'profile'])->name('profile.show');
    Route::put('/profile', [HomeController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password', [HomeController::class, 'updatePassword'])->name('profile.password');
});

// Order files download route
Route::get('/files/{file}/download', [FileController::class, 'download'])
    ->name('files.download')
    ->middleware('auth');

// M-Pesa client-facing routes
Route::middleware(['auth'])->group(function () {
    Route::post('/payment/mpesa', [PaymentController::class, 'initiatePayment'])->name('payment.mpesa.initiate');
    Route::get('/payment/mpesa/status', [PaymentController::class, 'checkPaymentStatus'])->name('payment.mpesa.status');
});

// M-Pesa callback routes - Now using Api namespace for MpesaController
Route::post('/api/mpesa/stk/callback', [MpesaController::class, 'handleCallback'])->name('api.mpesa.stk.callback');
Route::post('/api/mpesa/b2c/result', [MpesaController::class, 'handleResult'])->name('api.mpesa.b2c.result');
Route::post('/api/mpesa/b2c/timeout', [MpesaController::class, 'handleTimeout'])->name('api.mpesa.b2c.timeout');

// Legacy M-Pesa routes (keeping for backward compatibility)
Route::post('/api/mpesa/callback', [MpesaController::class, 'handleCallback'])->name('mpesa.callback');
Route::post('/api/mpesa/timeout', [MpesaController::class, 'handleTimeout'])->name('mpesa.timeout');
Route::post('/api/mpesa/result', [MpesaController::class, 'handleResult'])->name('mpesa.result');



