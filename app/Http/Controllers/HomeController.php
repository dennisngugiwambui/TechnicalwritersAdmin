<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard based on user type.
     *
     * @return \Illuminate\Contracts\Support\Renderable|\Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        $user = Auth::user();
        
        // Check if user is an admin
        if ($user->usertype !== 'admin') {
            // Log out non-admin users
            Auth::logout();
            
            // Redirect to login with error
            return redirect()->route('login')
                ->withErrors(['phone' => 'These credentials do not have administrative access.']);
        }
        
        // Check admin account status
        if ($user->status === 'pending') {
            return redirect()->route('welcome');
        } elseif (in_array($user->status, ['failed', 'suspended', 'banned', 'terminated', 'locked']) || 
                 $user->is_suspended === 'yes') {
            return redirect()->route('failed');
        }
        
        // Only active admins reach this point
        return view('home');
    }
}