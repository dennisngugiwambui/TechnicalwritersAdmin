<!-- resources/views/admin/profile.blade.php -->
@extends('layouts.admin')

@section('title', 'Admin Profile')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-semibold text-gray-900">Admin Profile</h1>
        
        <!-- Success Message -->
        @if(session('success'))
        <div id="successAlert" class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 mt-4 rounded shadow-sm">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-700">{{ session('success') }}</p>
                </div>
                <div class="ml-auto pl-3">
                    <div class="-mx-1.5 -my-1.5">
                        <button onclick="document.getElementById('successAlert').remove()" class="inline-flex rounded-md p-1.5 text-green-500 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Profile Card -->
            <div class="bg-white rounded-lg shadow overflow-hidden lg:col-span-1">
                <div class="p-6">
                    <div class="text-center">
                        <div class="relative inline-block">
                            @if($user->avatar)
                                <img class="h-32 w-32 rounded-full object-cover mx-auto ring-4 ring-gray-200" src="{{ asset('storage/avatars/' . $user->avatar) }}" alt="{{ $user->name }}">
                            @else
                                <div class="h-32 w-32 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center mx-auto ring-4 ring-gray-200">
                                    <span class="text-3xl font-bold text-white">{{ substr($user->name, 0, 1) }}</span>
                                </div>
                            @endif
                            <span class="absolute bottom-0 right-6 h-5 w-5 rounded-full bg-green-400 border-2 border-white"></span>
                        </div>
                        <h3 class="mt-4 text-xl font-medium text-gray-900">{{ $user->name }}</h3>
                        <div class="mt-1 text-sm font-medium text-gray-500">Administrator</div>
                    </div>
                    
                    <div class="mt-6 border-t border-gray-200 pt-4">
                        <div class="flex items-center py-2">
                            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M14.243 5.757a6 6 0 10-.986 9.284 1 1 0 111.087 1.678A8 8 0 1118 10a3 3 0 01-4.8 2.401A4 4 0 1114 10a1 1 0 102 0c0-1.537-.586-3.07-1.757-4.243zM12 10a2 2 0 10-4 0 2 2 0 004 0z" clip-rule="evenodd" />
                            </svg>
                            <span class="ml-3 text-sm text-gray-700">{{ $user->email }}</span>
                        </div>
                        
                        <div class="flex items-center py-2">
                            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" />
                            </svg>
                            <span class="ml-3 text-sm text-gray-700">{{ $user->phone ?? 'No phone number' }}</span>
                        </div>
                        
                        <div class="flex items-center py-2">
                            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd" />
                            </svg>
                            <span class="ml-3 text-sm text-gray-700">Member since {{ $user->created_at->format('M Y') }}</span>
                        </div>
                    </div>
                    
                    <div class="mt-6">
                        <div class="rounded-md bg-gray-50 p-4">
                            <div class="text-sm">
                                <h4 class="font-medium text-gray-900">About</h4>
                                <div class="mt-2 text-gray-700">
                                    {{ $user->bio ?? 'No bio information added yet.' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tabs and Forms -->
            <div class="bg-white rounded-lg shadow lg:col-span-2">
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex" x-data="{ activeTab: 'profile' }">
                        <button @click="activeTab = 'profile'" :class="{'border-indigo-500 text-indigo-600': activeTab === 'profile', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'profile'}" class="w-1/2 py-4 px-1 text-center border-b-2 font-medium text-sm">
                            Profile Information
                        </button>
                        <button @click="activeTab = 'security'" :class="{'border-indigo-500 text-indigo-600': activeTab === 'security', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'security'}" class="w-1/2 py-4 px-1 text-center border-b-2 font-medium text-sm">
                            Security Settings
                        </button>
                    </nav>
                </div>
                
                <div class="p-6" x-show="activeTab === 'profile'">
                    <h3 class="text-lg font-medium text-gray-900">Profile Information</h3>
                    <p class="mt-1 text-sm text-gray-600">Update your account's profile information and email address.</p>
                    
                    <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
                        @csrf
                        @method('PUT')
                        
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                            <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="bio" class="block text-sm font-medium text-gray-700">Bio</label>
                            <textarea name="bio" id="bio" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('bio', $user->bio) }}</textarea>
                            @error('bio')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Profile Photo</label>
                            <div class="mt-1 flex items-center">
                                @if($user->avatar)
                                    <div class="relative inline-block mr-3">
                                        <img class="h-12 w-12 rounded-full object-cover" src="{{ asset('storage/avatars/' . $user->avatar) }}" alt="{{ $user->name }}">
                                    </div>
                                @else
                                    <div class="h-12 w-12 rounded-full bg-gray-200 flex items-center justify-center mr-3">
                                        <svg class="h-6 w-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                @endif
                                <button type="button" onclick="document.getElementById('avatar').click()" class="ml-2 bg-white py-2 px-3 border border-gray-300 rounded-md shadow-sm text-sm leading-4 font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Change
                                </button>
                                <input id="avatar" name="avatar" type="file" class="hidden" accept="image/jpeg,image/png,image/jpg">
                            </div>
                            <p class="mt-1 text-xs text-gray-500">JPG, PNG or JPEG. Max 2MB.</p>
                            @error('avatar')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
                
                <div class="p-6" x-show="activeTab === 'security'" style="display: none;">
                    <h3 class="text-lg font-medium text-gray-900">Security Settings</h3>
                    <p class="mt-1 text-sm text-gray-600">Update your password to keep your account secure.</p>
                    
                    <form method="POST" action="{{ route('admin.profile.password') }}" class="mt-6 space-y-6">
                        @csrf
                        @method('PUT')
                        
                        <div>
                            <label for="current_password" class="block text-sm font-medium text-gray-700">Current Password</label>
                            <input type="password" name="current_password" id="current_password" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('current_password')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700">New Password</label>
                            <input type="password" name="password" id="password" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('password')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Update Password
                            </button>
                        </div>
                    </form>
                    
                    <hr class="mt-8 mb-8">
                    
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Two-Factor Authentication</h3>
                        <p class="mt-1 text-sm text-gray-600">Add additional security to your account using two-factor authentication.</p>
                        
                        <div class="mt-4 p-4 bg-gray-50 rounded-md">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-700">You have not enabled two-factor authentication.</p>
                                    <p class="mt-1 text-xs text-gray-500">When two-factor authentication is enabled, you will be prompted for a secure, random token during authentication.</p>
                                    <div class="mt-3">
                                        <button type="button" class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            Enable
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <hr class="mt-8 mb-8">
                    
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Browser Sessions</h3>
                        <p class="mt-1 text-sm text-gray-600">Manage and log out your active sessions on other browsers and devices.</p>
                        
                        <div class="mt-5 space-y-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <svg class="h-8 w-8 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-700">Chrome on Windows</div>
                                        <div class="text-xs text-gray-500">
                                            <span class="font-semibold">Current device</span> - 192.168.1.1
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Active
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-6">
                            <button type="button" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                Log Out Other Browser Sessions
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Admin Stats -->
            <div class="bg-white rounded-lg shadow overflow-hidden lg:col-span-3">
                <div class="px-6 py-5 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        Admin Activity Summary
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                        <!-- Managed Writers -->
                        <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-200">
                            <div class="p-5">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 bg-indigo-100 rounded-md p-3">
                                        <svg class="h-6 w-6 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </div>
                                    <div class="ml-5 w-0 flex-1">
                                        <dl>
                                            <dt class="text-sm font-medium text-gray-500 truncate">
                                                Managed Writers
                                            </dt>
                                            <dd>
                                                <div class="text-lg font-medium text-gray-900">
                                                    45
                                                </div>
                                            </dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 px-5 py-3">
                                <div class="text-sm">
                                    <a href="#" class="font-medium text-indigo-600 hover:text-indigo-500">
                                        View all
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Completed Orders -->
                        <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-200">
                            <div class="p-5">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 bg-green-100 rounded-md p-3">
                                        <svg class="h-6 w-6 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                        </svg>
                                    </div>
                                    <div class="ml-5 w-0 flex-1">
                                        <dl>
                                            <dt class="text-sm font-medium text-gray-500 truncate">
                                                Completed Orders
                                            </dt>
                                            <dd>
                                                <div class="text-lg font-medium text-gray-900">
                                                    189
                                                </div>
                                            </dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                            <!-- Pending Payments -->
                    <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-200">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-yellow-100 rounded-md p-3">
                                    <svg class="h-6 w-6 text-yellow-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">
                                            Pending Payments
                                        </dt>
                                        <dd>
                                            <div class="text-lg font-medium text-gray-900">
                                                12
                                            </div>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-5 py-3">
                            <div class="text-sm">
                                <a href="#" class="font-medium text-indigo-600 hover:text-indigo-500">
                                    Process payments
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Total Earnings -->
                    <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-200">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-purple-100 rounded-md p-3">
                                    <svg class="h-6 w-6 text-purple-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">
                                            Total Platform Earnings
                                        </dt>
                                        <dd>
                                            <div class="text-lg font-medium text-gray-900">
                                                KES 1,452,300
                                            </div>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-5 py-3">
                            <div class="text-sm">
                                <a href="#" class="font-medium text-indigo-600 hover:text-indigo-500">
                                    View financial reports
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity Table -->
                <div class="mt-8">
                    <h4 class="text-base font-medium text-gray-900 mb-4">Recent Admin Activity</h4>
                    <div class="overflow-x-auto">
                        <div class="align-middle inline-block min-w-full">
                            <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Action
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Description
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Target
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Date
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <!-- Activity Row 1 -->
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    Approved
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">Writer verification</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">James Smith</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                Today at 10:30 AM
                                            </td>
                                        </tr>
                                        
                                        <!-- Activity Row 2 -->
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                    Released
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">Payment processed</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">KES 25,400 to Maria Juan</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                Yesterday at 3:45 PM
                                            </td>
                                        </tr>
                                        
                                        <!-- Activity Row 3 -->
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                    Warning
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">Issued warning</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">Robert Johnson - Late delivery</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                Mar 21, 2025
                                            </td>
                                        </tr>
                                        
                                        <!-- Activity Row 4 -->
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                    Suspended
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">Account suspension</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">Michael Davis - Policy violation</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                Mar 19, 2025
                                            </td>
                                        </tr>
                                        
                                        <!-- Activity Row 5 -->
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                                    Modified
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">Updated system settings</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">Commission rates adjusted</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                Mar 17, 2025
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 flex justify-end">
                        <a href="#" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            View All Activity
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // File: public/js/admin-profile.js

document.addEventListener('DOMContentLoaded', function() {
    // Profile photo preview functionality
    const avatarInput = document.getElementById('avatar');
    const currentAvatar = document.querySelector('.profile-avatar-preview');
    
    if (avatarInput && currentAvatar) {
        avatarInput.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                // Only process image files
                if (!file.type.match('image.*')) {
                    alert('Please select an image file');
                    return;
                }
                
                // Create a preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    currentAvatar.src = e.target.result;
                };
                reader.readAsDataURL(file);
                
                // Show file name
                const fileNameElement = document.querySelector('.avatar-filename');
                if (fileNameElement) {
                    fileNameElement.textContent = file.name;
                }
            }
        });
    }
    
    // Responsive design adjustments
    function adjustForMobile() {
        const windowWidth = window.innerWidth;
        const statsCards = document.querySelectorAll('.stat-card');
        const activityTable = document.querySelector('.activity-table');
        
        if (windowWidth < 640) {
            // Mobile adjustments
            statsCards.forEach(card => {
                card.classList.add('flex-col', 'items-center', 'text-center');
                const iconContainer = card.querySelector('.icon-container');
                if (iconContainer) {
                    iconContainer.classList.add('mb-3');
                }
            });
            
            if (activityTable) {
                activityTable.classList.add('table-compact');
            }
        } else {
            // Desktop adjustments
            statsCards.forEach(card => {
                card.classList.remove('flex-col', 'items-center', 'text-center');
                const iconContainer = card.querySelector('.icon-container');
                if (iconContainer) {
                    iconContainer.classList.remove('mb-3');
                }
            });
            
            if (activityTable) {
                activityTable.classList.remove('table-compact');
            }
        }
    }
    
    // Execute on page load and resize
    adjustForMobile();
    window.addEventListener('resize', adjustForMobile);
    
    // Animation for statistics counters
    function animateCounters() {
        const counters = document.querySelectorAll('.counter-value');
        
        counters.forEach(counter => {
            const target = parseInt(counter.getAttribute('data-target'));
            const duration = 1500; // milliseconds
            const step = Math.ceil(target / (duration / 16)); // 60fps
            
            let count = 0;
            const updateCounter = () => {
                count += step;
                if (count < target) {
                    counter.textContent = count;
                    requestAnimationFrame(updateCounter);
                } else {
                    counter.textContent = target;
                }
            };
            
            updateCounter();
        });
    }
    
    // If the element is visible in viewport, start animation
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCounters();
                observer.disconnect();
            }
        });
    });
    
    const statsSection = document.querySelector('.stats-section');
    if (statsSection) {
        observer.observe(statsSection);
    }
    
    // Password strength indicator
    const passwordInput = document.getElementById('password');
    const strengthIndicator = document.getElementById('password-strength');
    
    if (passwordInput && strengthIndicator) {
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            
            // Calculate password strength
            if (password.length >= 8) strength += 1;
            if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength += 1;
            if (password.match(/\d/)) strength += 1;
            if (password.match(/[^a-zA-Z\d]/)) strength += 1;
            
            // Update strength indicator
            let strengthClass = '';
            let strengthText = '';
            
            switch (strength) {
                case 0:
                case 1:
                    strengthClass = 'bg-red-500';
                    strengthText = 'Weak';
                    break;
                case 2:
                    strengthClass = 'bg-yellow-500';
                    strengthText = 'Fair';
                    break;
                case 3:
                    strengthClass = 'bg-blue-500';
                    strengthText = 'Good';
                    break;
                case 4:
                    strengthClass = 'bg-green-500';
                    strengthText = 'Strong';
                    break;
            }
            
            // Remove all existing classes and add the new one
            strengthIndicator.className = 'w-full h-2 mt-1 rounded-full ' + strengthClass;
            
            // Update text
            const strengthTextElement = document.getElementById('password-strength-text');
            if (strengthTextElement) {
                strengthTextElement.textContent = strengthText;
                strengthTextElement.className = 'text-xs mt-1 ' + strengthClass.replace('bg-', 'text-');
            }
        });
    }
    
    // Initialize any tooltips
    const tooltipTriggers = document.querySelectorAll('[data-tooltip]');
    tooltipTriggers.forEach(trigger => {
        trigger.addEventListener('mouseenter', function() {
            const tooltipText = this.getAttribute('data-tooltip');
            
            // Create tooltip element
            const tooltip = document.createElement('div');
            tooltip.className = 'absolute z-10 px-3 py-2 text-xs font-medium text-white bg-gray-900 rounded-lg shadow-sm tooltip';
            tooltip.textContent = tooltipText;
            tooltip.style.bottom = 'calc(100% + 5px)';
            tooltip.style.left = '50%';
            tooltip.style.transform = 'translateX(-50%)';
            
            this.style.position = 'relative';
            this.appendChild(tooltip);
        });
        
        trigger.addEventListener('mouseleave', function() {
            const tooltip = this.querySelector('.tooltip');
            if (tooltip) {
                tooltip.remove();
            }
        });
    });
});
</script>


@endsection