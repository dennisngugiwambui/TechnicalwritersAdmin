<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class CheckOrderOwnership
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $orderId = $request->route('order');
        
        if (!$orderId) {
            return redirect('/home')
                ->with('toast', [
                    'title' => 'Error',
                    'message' => 'Order not found.'
                ]);
        }

        $order = Order::findOrFail($orderId);

        // If the user is an admin, allow access
        if (Auth::user()->usertype === 'admin') {
            return $next($request);
        }

        // If the user is a writer and assigned to this order
        if (Auth::user()->usertype === 'writer' && $order->writer_id === Auth::id()) {
            return $next($request);
        }

        // Otherwise, redirect with error message
        return redirect('/home')
            ->with('toast', [
                'title' => 'Access Denied',
                'message' => 'Cannot view other writer\'s order.'
            ]);
    }
}