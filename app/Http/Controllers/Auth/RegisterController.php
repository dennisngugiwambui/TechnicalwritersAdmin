<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;

class RegisterController extends Controller
{
    use RegistersUsers;

    protected $redirectTo = '/home';

    public function __construct()
    {
        $this->middleware('guest');
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'max:15', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    protected function create(array $data)
    {
        // Handle profile picture upload if provided
        $profilePicturePath = null;
        if (isset($data['profile_picture'])) {
            $profilePicturePath = $data['profile_picture']->store('profile_pictures', 'public');
        }

        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'usertype' => 'writer', // Assign default value
            'status' => 'pending',  // Assign default value
            'profile_picture' => $profilePicturePath,
            'is_suspended' => 'no', // Assign default value
            'password' => Hash::make($data['password']),
        ]);
    }

      /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        event(new Registered($user = $this->create($request->all())));

        $this->guard()->login($user);

        // Redirect based on user status
        if ($user->usertype === 'writer' && $user->status === 'pending') {
            return redirect()->route('welcome');
        }

        return $this->registered($request, $user)
            ?: redirect($this->redirectPath());
    }
}
