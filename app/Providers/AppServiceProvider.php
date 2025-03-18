<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Order;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Share order counts and unread messages with all views
        View::composer('*', function ($view) {
            if (Auth::check()) {
                // Get order counts
                $orderCounts = [
                    'all' => Order::count(),
                    'available' => Order::where('status', 'available')->count(),
                    'in_progress' => Order::whereIn('status', ['confirmed', 'in_progress', 'done', 'delivered'])->count(),
                    'revision' => Order::where('status', 'revision')->count(),
                    'completed' => Order::whereIn('status', ['completed', 'paid', 'finished'])->count(),
                    'dispute' => Order::where('status', 'dispute')->count(),
                    'cancelled' => Order::where('status', 'cancelled')->count(),
                ];
                
                // Get unread message count
                $unreadMessageCount = Message::whereNull('read_at')->count();
                
                // Get recent messages for notifications
                $recentMessages = Message::whereNull('read_at')
                    ->orderBy('created_at', 'desc')
                    ->take(5)
                    ->get();
                
                $view->with('orderCounts', $orderCounts);
                $view->with('unreadMessageCount', $unreadMessageCount);
                $view->with('recentMessages', $recentMessages);
            }
        });
    }
}