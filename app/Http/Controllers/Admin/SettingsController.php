<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\MpesaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SettingsController extends Controller
{
    /**
     * Display the settings page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $exchangeRate = config('mpesa.exchange_rate', 120);
        $adminUsers = User::where('usertype', User::ROLE_ADMIN)->get();
        
        // System statistics
        $stats = [
            'total_users' => User::count(),
            'total_admins' => User::where('usertype', User::ROLE_ADMIN)->count(),
            'total_writers' => User::where('usertype', User::ROLE_WRITER)->count(),
            'total_orders' => \App\Models\Order::count(),
        ];
        
        return view('admin.settings', compact('exchangeRate', 'adminUsers', 'stats'));
    }
    
    /**
     * Update general settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $request->validate([
            'site_name' => 'nullable|string|max:255',
            'site_email' => 'nullable|email|max:255',
            'support_email' => 'nullable|email|max:255',
            'support_phone' => 'nullable|string|max:20',
            'notification_email' => 'nullable|email|max:255',
        ]);
        
        // Update settings
        $this->updateSettings($request->all());
        
        return redirect()->route('settings')->with('success', 'Settings updated successfully');
    }
    
    /**
     * Show the exchange rate settings.
     *
     * @return \Illuminate\View\View
     */
    public function exchangeRate()
    {
        $exchangeRate = config('mpesa.exchange_rate', 120);
        
        return view('admin.settings.exchange-rate', compact('exchangeRate'));
    }
    
    /**
     * Update the exchange rate.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateExchangeRate(Request $request)
    {
        $request->validate([
            'exchange_rate' => 'required|numeric|min:1',
        ]);
        
        // Update exchange rate
        $mpesaService = new MpesaService();
        $success = $mpesaService->updateExchangeRate($request->exchange_rate);
        
        if ($success) {
            return redirect()->route('settings')->with('success', 'Exchange rate updated successfully');
        } else {
            return redirect()->route('settings')->with('error', 'Failed to update exchange rate');
        }
    }
    
    /**
     * Update settings in .env file or cache.
     *
     * @param  array  $settings
     * @return void
     */
    protected function updateSettings($settings)
    {
        foreach ($settings as $key => $value) {
            if (!empty($value)) {
                Cache::forever('setting_' . $key, $value);
            }
        }
    }
}