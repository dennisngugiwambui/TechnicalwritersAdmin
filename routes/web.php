<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\WriterController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\MpesaController;

// Public routes
Route::get('/', function () {
    return view('welcome');
});

// Authentication routes (without register)
Auth::routes(['register' => false]);

// Dashboard redirect after login
Route::get('/home', [HomeController::class, 'index'])->name('home');

// Admin routes
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    
    // Writer management
    Route::resource('writers', WriterController::class);
    Route::post('/writers/{writer}/suspend', [WriterController::class, 'suspend'])->name('writers.suspend');
    Route::post('/writers/{writer}/verify', [WriterController::class, 'verify'])->name('writers.verify');
    Route::post('/writers/{writer}/warning', [WriterController::class, 'warning'])->name('writers.warning');
    
    // Order management
    Route::resource('orders', OrderController::class);
    Route::post('/orders/{order}/make-available', [OrderController::class, 'makeAvailable'])->name('orders.make-available');
    Route::post('/orders/{order}/assign/{writer}', [OrderController::class, 'assign'])->name('orders.assign');
    Route::post('/orders/{order}/request-revision', [OrderController::class, 'requestRevision'])->name('orders.request-revision');
    Route::post('/orders/{order}/complete', [OrderController::class, 'complete'])->name('orders.complete');
    Route::post('/orders/{order}/dispute', [OrderController::class, 'dispute'])->name('orders.dispute');
    Route::post('/orders/upload-files', [OrderController::class, 'uploadFiles'])->name('orders.upload-files');
    
    // Payment management
    Route::resource('payments', PaymentController::class);
    Route::post('/payments/{payment}/approve', [PaymentController::class, 'approve'])->name('payments.approve');
    Route::get('/settings/exchange-rate', [PaymentController::class, 'exchangeRateForm'])->name('settings.exchange-rate');
    Route::post('/settings/exchange-rate', [PaymentController::class, 'updateExchangeRate'])->name('settings.exchange-rate.update');
    
    // Messaging system
    Route::resource('messages', MessageController::class);
    Route::post('/messages/send-as-client', [MessageController::class, 'sendAsClient'])->name('messages.send-as-client');
    Route::post('/messages/send-as-support', [MessageController::class, 'sendAsSupport'])->name('messages.send-as-support');
});

// Writer routes
Route::prefix('writer')->middleware(['auth', 'writer'])->group(function () {
    // Writer dashboard
    Route::get('/dashboard', [App\Http\Controllers\Writer\DashboardController::class, 'index'])->name('writer.dashboard');
    
    // Available orders
    Route::get('/orders/available', [App\Http\Controllers\Writer\OrderController::class, 'available'])->name('writer.orders.available');
    
    // Assigned orders
    Route::get('/orders/assigned', [App\Http\Controllers\Writer\OrderController::class, 'assigned'])->name('writer.orders.assigned');
    
    // Completed orders
    Route::get('/orders/completed', [App\Http\Controllers\Writer\OrderController::class, 'completed'])->name('writer.orders.completed');
    
    // Order details
    Route::get('/orders/{order}', [App\Http\Controllers\Writer\OrderController::class, 'show'])->name('writer.orders.show');
    
    // Submit order
    Route::post('/orders/{order}/submit', [App\Http\Controllers\Writer\OrderController::class, 'submit'])->name('writer.orders.submit');
    
    // Writer finances
    Route::get('/finances', [App\Http\Controllers\Writer\FinanceController::class, 'index'])->name('writer.finances');
    Route::post('/finances/request-payment', [App\Http\Controllers\Writer\FinanceController::class, 'requestPayment'])->name('writer.finances.request-payment');
    
    // Messages
    Route::get('/messages', [App\Http\Controllers\Writer\MessageController::class, 'index'])->name('writer.messages');
    Route::get('/messages/{order}', [App\Http\Controllers\Writer\MessageController::class, 'show'])->name('writer.messages.show');
    Route::post('/messages/{order}/send', [App\Http\Controllers\Writer\MessageController::class, 'send'])->name('writer.messages.send');
});

// Shared routes
Route::middleware(['auth'])->group(function () {
    // Order files
    Route::get('/files/{file}', [App\Http\Controllers\FileController::class, 'show'])->name('files.show');
    
    // Profile management
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'show'])->name('profile.show');
    Route::post('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
});

// M-Pesa callback
Route::post('/mpesa/callback', [MpesaController::class, 'callback'])->name('mpesa.callback');

// Manual order scraping route (protected by admin middleware)
Route::get('/admin/scrape-orders', [App\Http\Controllers\Admin\ScrapingController::class, 'scrape'])
    ->middleware(['auth', 'admin'])
    ->name('admin.scrape-orders');
