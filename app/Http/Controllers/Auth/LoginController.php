<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/home';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    // Use phone for login instead of email
    public function username()
    {
        return 'phone';
    }

    /**
     * Validate the user login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateLogin(Request $request)
    {
        $request->validate([
            $this->username() => 'required|string',
            'password' => 'required|string',
        ]);
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        // If the user is not an admin, log them out
        if ($user->usertype !== 'admin') {
            Auth::logout();
            
            return redirect()->route('login')
                ->withInput($request->only($this->username()))
                ->withErrors([
                    $this->username() => 'These credentials do not have administrative access.',
                ]);
        }
        
        // Check admin account status
        if ($user->status === 'pending') {
            return redirect()->route('welcome');
        } elseif (in_array($user->status, ['failed', 'suspended', 'banned', 'terminated', 'locked']) || 
                 $user->is_suspended === 'yes') {
            return redirect()->route('failed');
        }
        
        return redirect()->intended($this->redirectPath());
    }
}