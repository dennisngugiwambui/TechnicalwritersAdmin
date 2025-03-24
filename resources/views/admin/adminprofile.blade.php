<!-- resources/views/admin/adminprofile.blade.php -->
@extends('admin.app')

@section('title', 'Admin Profile')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-semibold text-gray-900">Admin Profile</h1>
            <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium">
                <i class="fas fa-shield-alt mr-1"></i> Administrator
            </span>
        </div>
        
        <!-- Success Message -->
        @if(session('success'))
        <div id="successAlert" class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 mt-4 rounded-lg shadow-sm transform transition-all duration-500 ease-in-out" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-700 font-medium">{{ session('success') }}</p>
                </div>
                <div class="ml-auto pl-3">
                    <div class="-mx-1.5 -my-1.5">
                        <button @click="show = false" class="inline-flex rounded-md p-1.5 text-green-500 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200">
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
            <div class="bg-white rounded-xl shadow-lg overflow-hidden lg:col-span-1 transform transition-all duration-300 hover:shadow-xl">
                <div class="bg-gradient-to-r from-blue-500 to-indigo-600 px-6 py-10 text-center">
                    <div class="relative inline-block">
                        @if($user->avatar)
                            <img class="h-32 w-32 rounded-full object-cover mx-auto ring-4 ring-white border-4 border-white shadow-inner" src="{{ asset('storage/avatars/' . $user->avatar) }}" alt="{{ $user->name }}">
                        @else
                            <div class="h-32 w-32 rounded-full bg-white flex items-center justify-center mx-auto ring-4 ring-white shadow-lg">
                                <span class="text-4xl font-bold text-indigo-600">{{ substr($user->name, 0, 1) }}</span>
                            </div>
                        @endif
                        <span class="absolute bottom-1 right-2 h-5 w-5 rounded-full bg-green-400 border-2 border-white animate-pulse"></span>
                    </div>
                    <h3 class="mt-4 text-2xl font-bold text-white">{{ $user->name }}</h3>
                    <div class="mt-1 text-sm font-medium text-indigo-100">System Administrator</div>
                </div>
                
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex items-center py-2 px-3 bg-gray-50 rounded-lg">
                            <svg class="h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M14.243 5.757a6 6 0 10-.986 9.284 1 1 0 111.087 1.678A8 8 0 1118 10a3 3 0 01-4.8 2.401A4 4 0 1114 10a1 1 0 102 0c0-1.537-.586-3.07-1.757-4.243zM12 10a2 2 0 10-4 0 2 2 0 004 0z" clip-rule="evenodd" />
                            </svg>
                            <span class="ml-3 text-sm text-gray-700 font-medium">{{ $user->email }}</span>
                        </div>
                        
                        <div class="flex items-center py-2 px-3 bg-gray-50 rounded-lg">
                            <svg class="h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" />
                            </svg>
                            <span class="ml-3 text-sm text-gray-700 font-medium">{{ $user->phone ?? 'No phone number' }}</span>
                        </div>
                        
                        <div class="flex items-center py-2 px-3 bg-gray-50 rounded-lg">
                            <svg class="h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd" />
                            </svg>
                            <span class="ml-3 text-sm text-gray-700 font-medium">Member since {{ $user->created_at->format('M Y') }}</span>
                        </div>
                    </div>
                    
                    <div class="mt-6">
                        <div class="rounded-xl bg-gradient-to-br from-blue-50 to-indigo-50 p-4 border border-blue-100">
                            <div class="text-sm">
                                <h4 class="font-medium text-indigo-700 mb-2">About</h4>
                                <div class="text-gray-700 prose prose-sm">
                                    {{ $user->bio ?? 'No bio information added yet. You can add a personal bio in the profile settings to help other team members know more about you.' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tabs and Forms -->
            <div class="bg-white rounded-xl shadow-lg lg:col-span-2 transform transition-all duration-300 hover:shadow-xl" x-data="{ activeTab: 'profile' }">
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex">
                        <button @click="activeTab = 'profile'" :class="{'border-indigo-500 text-indigo-600 font-bold': activeTab === 'profile', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'profile'}" class="w-1/2 py-4 px-1 text-center border-b-2 font-medium text-sm transition-all duration-200">
                            <i class="fas fa-user-circle mr-2"></i> Profile Information
                        </button>
                        <button @click="activeTab = 'security'" :class="{'border-indigo-500 text-indigo-600 font-bold': activeTab === 'security', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'security'}" class="w-1/2 py-4 px-1 text-center border-b-2 font-medium text-sm transition-all duration-200">
                            <i class="fas fa-shield-alt mr-2"></i> Security Settings
                        </button>
                    </nav>
                </div>
                
                <div class="p-6" x-show="activeTab === 'profile'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                        <i class="fas fa-user-edit text-indigo-500 mr-2"></i> Profile Information
                    </h3>
                    <p class="mt-1 text-sm text-gray-600">Update your account's profile information and contact details.</p>
                    
                    <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
                        @csrf
                        @method('PUT')
                        
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition-colors duration-200">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                            <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition-colors duration-200">
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                            <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition-colors duration-200">
                            @error('phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <label for="bio" class="block text-sm font-medium text-gray-700">Bio</label>
                            <textarea name="bio" id="bio" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition-colors duration-200">{{ old('bio', $user->bio) }}</textarea>
                            <p class="mt-1 text-xs text-gray-500">Brief description for your profile. URLs are hyperlinked.</p>
                            @error('bio')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <label class="block text-sm font-medium text-gray-700">Profile Photo</label>
                            <div class="mt-1 flex items-center">
                                @if($user->avatar)
                                    <div class="relative inline-block mr-3">
                                        <img class="h-12 w-12 rounded-full object-cover border border-gray-200" src="{{ asset('storage/avatars/' . $user->avatar) }}" alt="{{ $user->name }}">
                                    </div>
                                @else
                                    <div class="h-12 w-12 rounded-full bg-indigo-100 flex items-center justify-center mr-3 text-indigo-700">
                                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                @endif
                                <div class="flex">
                                    <label for="avatar" class="cursor-pointer bg-white py-2 px-3 border border-gray-300 rounded-md shadow-sm text-sm leading-4 font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200 flex items-center">
                                        <i class="fas fa-upload mr-2 text-indigo-600"></i> Select Photo
                                        <input id="avatar" name="avatar" type="file" class="sr-only" accept="image/jpeg,image/png,image/jpg">
                                    </label>
                                    @if($user->avatar)
                                    <button type="button" class="ml-2 bg-red-50 py-2 px-3 border border-red-300 rounded-md shadow-sm text-sm leading-4 font-medium text-red-700 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                    @endif
                                </div>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">JPG, PNG or JPEG. Max 2MB.</p>
                            <div id="avatar-preview" class="mt-2 hidden">
                                <p class="text-xs text-indigo-600 font-medium avatar-filename"></p>
                            </div>
                            @error('avatar')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                                <i class="fas fa-save mr-2"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
                
                <div class="p-6" x-show="activeTab === 'security'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" style="display: none;">
                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                        <i class="fas fa-lock text-indigo-500 mr-2"></i> Security Settings
                    </h3>
                    <p class="mt-1 text-sm text-gray-600">Update your password and manage security preferences.</p>
                    
                    <form method="POST" action="{{ route('admin.profile.password') }}" class="mt-6 space-y-6">
                        @csrf
                        @method('PUT')
                        
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <label for="current_password" class="block text-sm font-medium text-gray-700">Current Password</label>
                            <div class="relative">
                                <input type="password" name="current_password" id="current_password" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition-colors duration-200">
                                <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 cursor-pointer password-toggle" data-target="current_password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            @error('current_password')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <label for="password" class="block text-sm font-medium text-gray-700">New Password</label>
                            <div class="relative">
                                <input type="password" name="password" id="password" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition-colors duration-200">
                                <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 cursor-pointer password-toggle" data-target="password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="mt-1">
                                <div id="password-strength" class="w-full h-2 rounded-full bg-gray-200"></div>
                                <p id="password-strength-text" class="text-xs mt-1 text-gray-500">Password strength</p>
                            </div>
                            @error('password')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                            <div class="relative">
                                <input type="password" name="password_confirmation" id="password_confirmation" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition-colors duration-200">
                                <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 cursor-pointer password-toggle" data-target="password_confirmation">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                                <i class="fas fa-key mr-2"></i> Update Password
                            </button>
                        </div>
                    </form>
                    
                    <hr class="my-8 border-gray-200">
                    
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 flex items-center">
                            <i class="fas fa-mobile-alt text-indigo-500 mr-2"></i> Two-Factor Authentication
                        </h3>
                        <p class="mt-1 text-sm text-gray-600">Add additional security to your account using two-factor authentication.</p>
                        
                        <div class="mt-4 p-4 bg-gray-50 rounded-lg border border-gray-100">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 bg-indigo-100 p-2 rounded-md">
                                    <svg class="h-6 w-6 text-indigo-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3 flex-1">
                                    <p class="text-sm font-medium text-gray-700">You have not enabled two-factor authentication.</p>
                                    <p class="mt-1 text-xs text-gray-500">When two-factor authentication is enabled, you will be prompted for a secure, random token during authentication.</p>
                                    <div class="mt-3">
                                        <button type="button" class="inline-flex items-center px-3 py-1.5 border border-indigo-300 shadow-sm text-xs font-medium rounded text-indigo-700 bg-indigo-50 hover:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                                            <i class="fas fa-plus-circle mr-1.5"></i> Enable
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <hr class="my-8 border-gray-200">
                    
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 flex items-center">
                            <i class="fas fa-globe text-indigo-500 mr-2"></i> Browser Sessions
                        </h3>
                        <p class="mt-1 text-sm text-gray-600">Manage and log out your active sessions on other browsers and devices.</p>
                        
                        <div class="mt-5 space-y-4">
                            <div class="p-4 bg-gray-50 rounded-lg border border-gray-100">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="bg-indigo-100 p-2 rounded-md">
                                            <svg class="h-6 w-6 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-medium text-gray-700">{{ request()->header('User-Agent') }}</div>
                                            <div class="text-xs text-gray-500">
                                                <span class="font-semibold">Current device</span> - {{ request()->ip() }}
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <div class="h-1.5 w-1.5 bg-green-500 rounded-full mr-1.5 animate-pulse"></div> Active
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-6">
                            <form method="POST" action="{{ route('admin.profile.logout-devices') }}">
                                @csrf
                                <!-- Hidden field for current password that will be shown in a modal -->
                                <input type="hidden" id="modal_current_password" name="current_password">
                                
                                <button type="button" onclick="showLogoutDevicesModal()" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200">
                                    <i class="fas fa-sign-out-alt mr-2"></i> Log Out Other Browser Sessions
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Admin Stats -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden lg:col-span-3 transform transition-all duration-300 hover:shadow-xl">
                <div class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-indigo-500 to-blue-600">
                    <h3 class="text-lg leading-6 font-medium text-white flex items-center">
                        <i class="fas fa-chart-line mr-2"></i> Admin Activity Summary
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                        <!-- Managed Writers -->
                        <div class="bg-white overflow-hidden shadow-md rounded-xl border border-indigo-100 transition-all duration-300 hover:shadow-lg transform hover:-translate-y-1">
                            <div class="p-5">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 bg-indigo-100 rounded-xl p-3">
                                        <svg class="h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </div>
                                    <div class="ml-5 w-0 flex-1">
                                        <dl>
                                            <dt class="text-sm font-medium text-gray-500 truncate">
                                                Managed Writers
                                            </dt>
                                            <dd>
                                                <div class="text-xl font-bold text-gray-900 counter-value" data-target="{{ $stats['managed_writers'] ?? 45 }}">
                                                    {{ $stats['managed_writers'] ?? 45 }}
                                                </div>
                                            </dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gradient-to-r from-indigo-50 to-blue-50 px-5 py-3">
                                <div class="text-sm">
                                    <a href="{{ route('admin.writers.index') }}" class="font-medium text-indigo-600 hover:text-indigo-500 flex items-center transition-colors duration-200">
                                        <span>View all writers</span>
                                        <svg class="ml-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Completed Orders -->
                        <div class="bg-white overflow-hidden shadow-md rounded-xl border border-green-100 transition-all duration-300 hover:shadow-lg transform hover:-translate-y-1">
                            <div class="p-5">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 bg-green-100 rounded-xl p-3">
                                        <svg class="h-8 w-8 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                        </svg>
                                    </div>
                                    <div class="ml-5 w-0 flex-1">
                                        <dl>
                                            <dt class="text-sm font-medium text-gray-500 truncate">
                                                Completed Orders
                                            </dt>
                                            <dd>
                                                <div class="text-xl font-bold text-gray-900 counter-value" data-target="{{ $stats['completed_orders'] ?? 189 }}">
                                                    {{ $stats['completed_orders'] ?? 189 }}
                                                </div>
                                            </dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gradient-to-r from-green-50 to-teal-50 px-5 py-3">
                                <div class="text-sm">
                                    <a href="{{ route('admin.orders.index', ['status' => 'completed']) }}" class="font-medium text-green-600 hover:text-green-500 flex items-center transition-colors duration-200">
                                        <span>View completed</span>
                                        <svg class="ml-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Pending Payments -->
                        <div class="bg-white overflow-hidden shadow-md rounded-xl border border-yellow-100 transition-all duration-300 hover:shadow-lg transform hover:-translate-y-1">
                            <div class="p-5">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 bg-yellow-100 rounded-xl p-3">
                                        <svg class="h-8 w-8 text-yellow-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div class="ml-5 w-0 flex-1">
                                        <dl>
                                            <dt class="text-sm font-medium text-gray-500 truncate">
                                                Pending Payments
                                            </dt>
                                            <dd>
                                                <div class="text-xl font-bold text-gray-900 counter-value" data-target="{{ $stats['pending_payments'] ?? 12 }}">
                                                    {{ $stats['pending_payments'] ?? 12 }}
                                                </div>
                                            </dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gradient-to-r from-yellow-50 to-amber-50 px-5 py-3">
                                <div class="text-sm">
                                    <a href="{{ route('admin.finance.withdrawals') }}" class="font-medium text-yellow-600 hover:text-yellow-500 flex items-center transition-colors duration-200">
                                        <span>Process payments</span>
                                        <svg class="ml-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Total Earnings -->
                        <div class="bg-white overflow-hidden shadow-md rounded-xl border border-purple-100 transition-all duration-300 hover:shadow-lg transform hover:-translate-y-1">
                            <div class="p-5">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 bg-purple-100 rounded-xl p-3">
                                        <svg class="h-8 w-8 text-purple-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </div>
                                    <div class="ml-5 w-0 flex-1">
                                        <dl>
                                            <dt class="text-sm font-medium text-gray-500 truncate">
                                                Total Platform Earnings
                                            </dt>
                                            <dd>
                                                <div class="text-xl font-bold text-gray-900">
                                                    KES {{ number_format($stats['total_earnings'] ?? 1452300) }}
                                                </div>
                                            </dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gradient-to-r from-purple-50 to-pink-50 px-5 py-3">
                                <div class="text-sm">
                                    <a href="{{ route('admin.finance.reports') }}" class="font-medium text-purple-600 hover:text-purple-500 flex items-center transition-colors duration-200">
                                        <span>View financial reports</span>
                                        <svg class="ml-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity Table -->
                    <div class="mt-8">
                        <h4 class="text-base font-medium text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-history text-indigo-500 mr-2"></i> Recent Admin Activity
                        </h4>
                        <div class="overflow-x-auto">
                            <div class="align-middle inline-block min-w-full">
                                <div class="shadow-md overflow-hidden border border-gray-200 sm:rounded-lg">
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
                                            @if(isset($recentActivities) && $recentActivities->count() > 0)
                                                @foreach($recentActivities as $activity)
                                                <tr class="hover:bg-gray-50 transition-colors duration-200">
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $activity->badge_class }}">
                                                            {{ $activity->activity_label }}
                                                        </span>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <div class="text-sm text-gray-900">{{ $activity->description }}</div>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <div class="text-sm text-gray-900">
                                                            @if($activity->target_type && $activity->target_id)
                                                                {{ class_basename($activity->target_type) }} #{{ $activity->target_id }}
                                                            @else
                                                                System
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {{ $activity->formatted_date }}
                                                    </td>
                                                </tr>
                                                @endforeach
                                            @else
                                                <!-- Sample Data -->
                                                <tr class="hover:bg-gray-50 transition-colors duration-200">
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
                                                
                                                <tr class="hover:bg-gray-50 transition-colors duration-200">
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
                                                
                                                <tr class="hover:bg-gray-50 transition-colors duration-200">
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
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 flex justify-end">
                            <a href="#" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-200">
                                <i class="fas fa-list mr-2 text-indigo-500"></i> View All Activity
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Logout Other Devices Modal -->
<div id="logoutDevicesModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden" x-data="{ show: false }" x-show="show" x-on:keydown.escape.window="show = false">
    <div class="bg-white rounded-lg max-w-md w-full p-6 transform transition-all duration-300" 
         x-show="show"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">Log Out Other Browser Sessions</h3>
            <button type="button" onclick="hideLogoutDevicesModal()" class="text-gray-400 hover:text-gray-500">
                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        
        <p class="text-sm text-gray-600 mb-4">
            Please enter your password to confirm you would like to log out of your other browser sessions across all of your devices.
        </p>
        
        <div class="mb-4">
            <label for="password_confirmation_modal" class="block text-sm font-medium text-gray-700">Password</label>
            <input type="password" id="password_confirmation_modal" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            <p id="password_confirmation_error" class="mt-1 text-sm text-red-600 hidden">The password is incorrect.</p>
        </div>
        
        <div class="flex justify-end space-x-3">
            <button type="button" onclick="hideLogoutDevicesModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Cancel
            </button>
            <button type="button" onclick="submitLogoutDevices()" class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                Log Out Other Browser Sessions
            </button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Profile photo preview functionality
        const avatarInput = document.getElementById('avatar');
        const avatarPreview = document.getElementById('avatar-preview');
        const fileNameElement = document.querySelector('.avatar-filename');
        
        if (avatarInput) {
            avatarInput.addEventListener('change', function(event) {
                const file = event.target.files[0];
                if (file) {
                    // Only process image files
                    if (!file.type.match('image.*')) {
                        alert('Please select an image file (JPG, PNG, or JPEG)');
                        return;
                    }
                    
                    // Show file name
                    if (fileNameElement) {
                        fileNameElement.textContent = file.name;
                    }
                    
                    // Show preview container
                    if (avatarPreview) {
                        avatarPreview.classList.remove('hidden');
                    }
                }
            });
        }
        
        // Password toggle functionality
        const passwordToggles = document.querySelectorAll('.password-toggle');
        passwordToggles.forEach(toggle => {
            toggle.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const inputField = document.getElementById(targetId);
                
                if (inputField.type === 'password') {
                    inputField.type = 'text';
                    this.innerHTML = '<i class="fas fa-eye-slash"></i>';
                } else {
                    inputField.type = 'password';
                    this.innerHTML = '<i class="fas fa-eye"></i>';
                }
            });
        });
        
        // Password strength indicator
        const passwordInput = document.getElementById('password');
        const strengthIndicator = document.getElementById('password-strength');
        const strengthTextElement = document.getElementById('password-strength-text');
        
        if (passwordInput && strengthIndicator && strengthTextElement) {
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
                strengthTextElement.textContent = password.length > 0 ? strengthText : 'Password strength';
                strengthTextElement.className = 'text-xs mt-1 ' + (password.length > 0 ? strengthClass.replace('bg-', 'text-') : 'text-gray-500');
            });
        }
        
        // Animation for statistics counters
        function animateCounters() {
            const counters = document.querySelectorAll('.counter-value');
            
            counters.forEach(counter => {
                const target = parseInt(counter.getAttribute('data-target'));
                const duration = 1500; // milliseconds
                const steps = 50; // Number of steps
                const stepValue = target / steps;
                
                let current = 0;
                const timer = setInterval(() => {
                    current += stepValue;
                    if (current >= target) {
                        clearInterval(timer);
                        counter.textContent = target;
                    } else {
                        counter.textContent = Math.round(current);
                    }
                }, duration / steps);
            });
        }
        
        // If the element is visible in viewport, start animation
        const stats = document.querySelector('.stats-section');
        if (stats) {
            if (window.IntersectionObserver) {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            animateCounters();
                            observer.disconnect();
                        }
                    });
                });
                observer.observe(stats);
            } else {
                // Fallback for browsers that don't support IntersectionObserver
                animateCounters();
            }
        } else {
            // If no stats section, animate anyway for demo purposes
            animateCounters();
        }
    });
    
    // Modal functions for logout other devices
    function showLogoutDevicesModal() {
        const modal = document.getElementById('logoutDevicesModal');
        modal.classList.remove('hidden');
        document.getElementById('password_confirmation_modal').focus();
        
        // If using Alpine.js
        if (typeof Alpine !== 'undefined') {
            Alpine.store('modalOpen', true);
        }
    }
    
    function hideLogoutDevicesModal() {
        const modal = document.getElementById('logoutDevicesModal');
        modal.classList.add('hidden');
        document.getElementById('password_confirmation_modal').value = '';
        document.getElementById('password_confirmation_error').classList.add('hidden');
        
        // If using Alpine.js
        if (typeof Alpine !== 'undefined') {
            Alpine.store('modalOpen', false);
        }
    }
    
    function submitLogoutDevices() {
        const password = document.getElementById('password_confirmation_modal').value;
        if (!password) {
            document.getElementById('password_confirmation_error').classList.remove('hidden');
            document.getElementById('password_confirmation_error').textContent = 'Please enter your password.';
            return;
        }
        
        // Set the password value in the hidden form field
        document.getElementById('modal_current_password').value = password;
        
        // Submit the form
        document.querySelector('form[action*="logout-devices"]').submit();
    }
</script>

@endsection