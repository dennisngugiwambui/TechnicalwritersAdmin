<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Store a newly created admin user in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => 'required|string|in:admin,super_admin',
        ]);

        try {
            // Create new admin user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'usertype' => User::ROLE_ADMIN,
                'status' => User::STATUS_ACTIVE,
                'role' => $request->role, // Additional role field if your schema has it
            ]);

            return redirect()->route('admin.settings', ['#users'])->with('success', 'Admin user created successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to create admin user: ' . $e->getMessage(), [
                'name' => $request->name,
                'email' => $request->email,
            ]);

            return redirect()->route('admin.settings', ['#users'])->with('error', 'Failed to create admin user. Please try again.');
        }
    }

    /**
     * Update the specified admin user in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Ensure only admin users can be modified
        if ($user->usertype !== User::ROLE_ADMIN) {
            return redirect()->route('admin.settings', ['#users'])->with('error', 'Only admin users can be modified here.');
        }

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'role' => 'required|string|in:admin,super_admin',
            'status' => 'required|string|in:active,inactive,suspended',
        ];

        // Only validate password if it's provided
        if ($request->filled('password')) {
            $rules['password'] = ['required', 'confirmed', Password::defaults()];
        }

        $request->validate($rules);

        try {
            // Update basic user information
            $user->name = $request->name;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->status = $request->status;
            $user->role = $request->role; // If your schema has this field

            // Update password if provided
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            $user->save();

            return redirect()->route('admin.settings', ['#users'])->with('success', 'Admin user updated successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to update admin user: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'name' => $request->name,
                'email' => $request->email,
            ]);

            return redirect()->route('admin.settings', ['#users'])->with('error', 'Failed to update admin user. Please try again.');
        }
    }

    /**
     * Remove the specified admin user from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
   /**
 * Remove the specified admin user from storage.
 *
 * @param  int  $id
 * @return \Illuminate\Http\RedirectResponse
    */
    public function destroy($id)
    {
        // Cannot delete yourself
        if ($id == Auth::user()->id) {
            return redirect()->route('admin.settings', ['#users'])->with('error', 'You cannot delete your own account.');
        }

        $user = User::findOrFail($id);

        // Ensure only admin users can be deleted
        if ($user->usertype !== User::ROLE_ADMIN) {
            return redirect()->route('admin.settings', ['#users'])->with('error', 'Only admin users can be deleted here.');
        }

        try {
            $user->delete();
            return redirect()->route('admin.settings', ['#users'])->with('success', 'Admin user deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to delete admin user: ' . $e->getMessage(), [
                'user_id' => $user->id,
            ]);

            return redirect()->route('admin.settings', ['#users'])->with('error', 'Failed to delete admin user. Please try again.');
        }
    }
}