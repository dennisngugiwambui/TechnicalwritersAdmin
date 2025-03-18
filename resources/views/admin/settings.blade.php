@extends('admin.app')


@section('content')
    <!-- System Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-users text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total Users</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_users'] ?? 0 }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-user-shield text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Admin Users</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_admins'] ?? 0 }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <i class="fas fa-user-edit text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Writers</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_writers'] ?? 0 }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <i class="fas fa-clipboard-list text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total Orders</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_orders'] ?? 0 }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Settings Tabs -->
    <div x-data="{ activeTab: window.location.hash ? window.location.hash.replace('#', '') : 'general' }" 
         x-init="$watch('activeTab', value => { window.location.hash = value })">
        <!-- Tab Navigation -->
        <div class="mb-6 border-b border-gray-200">
            <nav class="-mb-px flex flex-wrap" aria-label="Tabs">
                <button @click="activeTab = 'general'" 
                        :class="{'border-primary-500 text-primary-600': activeTab === 'general', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'general'}" 
                        class="whitespace-nowrap py-4 px-4 border-b-2 font-medium text-sm">
                    <i class="fas fa-cog mr-2"></i> General Settings
                </button>
                
                <button @click="activeTab = 'payment'" 
                        :class="{'border-primary-500 text-primary-600': activeTab === 'payment', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'payment'}" 
                        class="whitespace-nowrap py-4 px-4 border-b-2 font-medium text-sm">
                    <i class="fas fa-money-bill-wave mr-2"></i> Payment Settings
                </button>
                
                <button @click="activeTab = 'email'" 
                        :class="{'border-primary-500 text-primary-600': activeTab === 'email', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'email'}" 
                        class="whitespace-nowrap py-4 px-4 border-b-2 font-medium text-sm">
                    <i class="fas fa-envelope mr-2"></i> Email Templates
                </button>
                
                <button @click="activeTab = 'users'" 
                        :class="{'border-primary-500 text-primary-600': activeTab === 'users', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'users'}" 
                        class="whitespace-nowrap py-4 px-4 border-b-2 font-medium text-sm">
                    <i class="fas fa-user-shield mr-2"></i> Admin Users
                </button>
                
                <button @click="activeTab = 'system'" 
                        :class="{'border-primary-500 text-primary-600': activeTab === 'system', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'system'}" 
                        class="whitespace-nowrap py-4 px-4 border-b-2 font-medium text-sm">
                    <i class="fas fa-server mr-2"></i> System Info
                </button>
            </nav>
        </div>
        
        <!-- General Settings Tab -->
        <div x-show="activeTab === 'general'" class="bg-white shadow-sm rounded-lg overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">General Settings</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Update the basic configuration of your application.
                </p>
            </div>
            
            <form method="POST" action="{{ route('admin.settings.update') }}" class="p-6 space-y-6">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="site_name" class="block text-sm font-medium text-gray-700">Site Name</label>
                        <input type="text" name="site_name" id="site_name" class="mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" value="{{ Cache::get('setting_site_name', config('app.name')) }}">
                    </div>
                    
                    <div>
                        <label for="site_email" class="block text-sm font-medium text-gray-700">Site Email</label>
                        <input type="email" name="site_email" id="site_email" class="mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" value="{{ Cache::get('setting_site_email') }}">
                    </div>
                    
                    <div>
                        <label for="support_email" class="block text-sm font-medium text-gray-700">Support Email</label>
                        <input type="email" name="support_email" id="support_email" class="mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" value="{{ Cache::get('setting_support_email') }}">
                    </div>
                    
                    <div>
                        <label for="support_phone" class="block text-sm font-medium text-gray-700">Support Phone</label>
                        <input type="text" name="support_phone" id="support_phone" class="mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" value="{{ Cache::get('setting_support_phone') }}">
                    </div>
                </div>
                
                <div>
                    <label for="notification_email" class="block text-sm font-medium text-gray-700">Notification Email</label>
                    <input type="email" name="notification_email" id="notification_email" class="mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" value="{{ Cache::get('setting_notification_email') }}">
                    <p class="mt-1 text-sm text-gray-500">All system notifications will be sent to this email address.</p>
                </div>
                
                <div class="pt-5">
                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                            Save Settings
                        </button>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Payment Settings Tab -->
        <div x-show="activeTab === 'payment'" class="bg-white shadow-sm rounded-lg overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Payment Settings</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Configure payment gateways and currency settings.
                </p>
            </div>
            
            <div class="p-6">
                <!-- M-Pesa Settings -->
                <div class="mb-8">
                    <h4 class="text-base font-medium text-gray-900 mb-4 flex items-center">
                        <img src="{{ asset('images/mpesa-logo.png') }}" alt="M-Pesa" class="h-6 mr-2"> 
                        M-Pesa Configuration
                    </h4>
                    
                    <div class="bg-gray-50 p-4 rounded-md mb-6">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 pt-0.5">
                                <i class="fas fa-info-circle text-primary-500"></i>
                            </div>
                            <div class="ml-3 flex-1">
                                <p class="text-sm text-gray-700">
                                    M-Pesa integration is configured through environment variables. Some settings can be changed below, but for full configuration changes, please contact your system administrator.
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Environment</label>
                            <div class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-gray-100 rounded-md shadow-sm text-sm">
                                <span class="inline-flex items-center">
                                    <span class="w-3 h-3 inline-block rounded-full mr-2 
                                        {{ config('mpesa.environment', 'sandbox') === 'sandbox' ? 'bg-yellow-500' : 'bg-green-500' }}"></span>
                                    {{ config('mpesa.environment', 'sandbox') === 'sandbox' ? 'Sandbox (Testing)' : 'Production (Live)' }}
                                </span>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Business Shortcode</label>
                            <div class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-gray-100 rounded-md shadow-sm text-sm">
                                {{ config('mpesa.shortcode', 'Not Configured') }}
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">B2C Shortcode</label>
                            <div class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-gray-100 rounded-md shadow-sm text-sm">
                                {{ config('mpesa.b2c_shortcode', 'Not Configured') }}
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Initiator Name</label>
                            <div class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-gray-100 rounded-md shadow-sm text-sm">
                                {{ config('mpesa.b2c_initiator', 'Not Configured') }}
                            </div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Callback URL</label>
                            <div class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-gray-100 rounded-md shadow-sm text-sm text-gray-500 truncate" title="{{ config('mpesa.callback_url', 'Not Configured') }}">
                                {{ config('mpesa.callback_url', 'Not Configured') }}
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Timeout URL</label>
                            <div class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-gray-100 rounded-md shadow-sm text-sm text-gray-500 truncate" title="{{ config('mpesa.timeout_url', 'Not Configured') }}">
                                {{ config('mpesa.timeout_url', 'Not Configured') }}
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Result URL</label>
                            <div class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-gray-100 rounded-md shadow-sm text-sm text-gray-500 truncate" title="{{ config('mpesa.result_url', 'Not Configured') }}">
                                {{ config('mpesa.result_url', 'Not Configured') }}
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Exchange Rate Settings -->
                <div>
                    <h4 class="text-base font-medium text-gray-900 mb-4">Currency Exchange Rate</h4>
                    
                    <form method="POST" action="{{ route('admin.settings.update-exchange-rate') }}" class="space-y-6">
                        @csrf
                        <div class="max-w-md">
                            <label for="exchange_rate" class="block text-sm font-medium text-gray-700">USD to KES Exchange Rate</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">1 USD =</span>
                                </div>
                                <input type="number" step="0.01" min="1" name="exchange_rate" id="exchange_rate" class="focus:ring-primary-500 focus:border-primary-500 block w-full pl-16 pr-12 sm:text-sm border-gray-300 rounded-md" value="{{ $exchangeRate }}">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">KES</span>
                                </div>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">
                                This rate will be used for converting USD to KES when processing M-Pesa payments.
                            </p>
                        </div>
                        
                        <div>
                            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                Update Exchange Rate
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Email Templates Tab -->
        <div x-show="activeTab === 'email'" class="bg-white shadow-sm rounded-lg overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Email Templates</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Configure the email templates sent to writers and clients.
                </p>
            </div>
            
            <div class="p-6">
                <!-- Email Templates List -->
                <div class="space-y-6">
                    <div x-data="{ expanded: false }" class="border border-gray-200 rounded-md overflow-hidden">
                        <button @click="expanded = !expanded" class="w-full px-4 py-3 flex items-center justify-between bg-gray-50 hover:bg-gray-100 transition-colors duration-150 focus:outline-none">
                            <div class="flex items-center">
                                <i class="fas fa-envelope-open-text text-primary-500 mr-2"></i>
                                <span class="font-medium">Order Assignment Notification</span>
                            </div>
                            <i class="fas" :class="{'fa-chevron-down': !expanded, 'fa-chevron-up': expanded}"></i>
                        </button>
                        
                        <div x-show="expanded" class="p-4 border-t border-gray-200">
                            <form class="space-y-4">
                                <div>
                                    <label for="subject_order_assignment" class="block text-sm font-medium text-gray-700">Email Subject</label>
                                    <input type="text" id="subject_order_assignment" name="subject_order_assignment" class="mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" value="New Order Assignment: [Order_ID] - [Order_Title]">
                                </div>
                                
                                <div>
                                    <label for="body_order_assignment" class="block text-sm font-medium text-gray-700">Email Body</label>
                                    <textarea id="body_order_assignment" name="body_order_assignment" rows="6" class="mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">Hello [Writer_Name],

You have been assigned a new order.

Order ID: [Order_ID]
Title: [Order_Title]
Deadline: [Order_Deadline]

Please log in to your dashboard to view the full details and accept the assignment.

Best regards,
Technical Writers Team</textarea>
                                </div>
                                
                                <div class="bg-gray-50 p-3 rounded-md">
                                    <h5 class="text-sm font-medium text-gray-700 mb-2">Available Variables</h5>
                                    <div class="flex flex-wrap gap-2">
                                        <span class="px-2 py-1 text-xs rounded-md bg-gray-200 text-gray-700">[Writer_Name]</span>
                                        <span class="px-2 py-1 text-xs rounded-md bg-gray-200 text-gray-700">[Order_ID]</span>
                                        <span class="px-2 py-1 text-xs rounded-md bg-gray-200 text-gray-700">[Order_Title]</span>
                                        <span class="px-2 py-1 text-xs rounded-md bg-gray-200 text-gray-700">[Order_Deadline]</span>
                                        <span class="px-2 py-1 text-xs rounded-md bg-gray-200 text-gray-700">[Order_Price]</span>
                                    </div>
                                </div>
                                
                                <div class="flex justify-end">
                                    <button type="button" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                        Save Template
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <div x-data="{ expanded: false }" class="border border-gray-200 rounded-md overflow-hidden">
                        <button @click="expanded = !expanded" class="w-full px-4 py-3 flex items-center justify-between bg-gray-50 hover:bg-gray-100 transition-colors duration-150 focus:outline-none">
                            <div class="flex items-center">
                                <i class="fas fa-sync-alt text-orange-500 mr-2"></i>
                                <span class="font-medium">Revision Request Notification</span>
                            </div>
                            <i class="fas" :class="{'fa-chevron-down': !expanded, 'fa-chevron-up': expanded}"></i>
                        </button>
                        
                        <div x-show="expanded" class="p-4 border-t border-gray-200">
                            <!-- Template form content -->
                            <form class="space-y-4">
                                <div>
                                    <label for="subject_revision" class="block text-sm font-medium text-gray-700">Email Subject</label>
                                    <input type="text" id="subject_revision" name="subject_revision" class="mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" value="Revision Requested: [Order_ID] - [Order_Title]">
                                </div>
                                
                                <div>
                                    <label for="body_revision" class="block text-sm font-medium text-gray-700">Email Body</label>
                                    <textarea id="body_revision" name="body_revision" rows="6" class="mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">Hello [Writer_Name],

A revision has been requested for your order.

Order ID: [Order_ID]
Title: [Order_Title]
Revision Comments: [Revision_Comments]

Please log in to your dashboard to view the full details and submit the revised work.

Best regards,
Technical Writers Team</textarea>
                                </div>
                                
                                <div class="bg-gray-50 p-3 rounded-md">
                                    <h5 class="text-sm font-medium text-gray-700 mb-2">Available Variables</h5>
                                    <div class="flex flex-wrap gap-2">
                                        <span class="px-2 py-1 text-xs rounded-md bg-gray-200 text-gray-700">[Writer_Name]</span>
                                        <span class="px-2 py-1 text-xs rounded-md bg-gray-200 text-gray-700">[Order_ID]</span>
                                        <span class="px-2 py-1 text-xs rounded-md bg-gray-200 text-gray-700">[Order_Title]</span>
                                        <span class="px-2 py-1 text-xs rounded-md bg-gray-200 text-gray-700">[Revision_Comments]</span>
                                    </div>
                                </div>
                                
                                <div class="flex justify-end">
                                    <button type="button" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                        Save Template
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <div x-data="{ expanded: false }" class="border border-gray-200 rounded-md overflow-hidden">
                        <button @click="expanded = !expanded" class="w-full px-4 py-3 flex items-center justify-between bg-gray-50 hover:bg-gray-100 transition-colors duration-150 focus:outline-none">
                            <div class="flex items-center">
                                <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                <span class="font-medium">Order Completion Notification</span>
                            </div>
                            <i class="fas" :class="{'fa-chevron-down': !expanded, 'fa-chevron-up': expanded}"></i>
                        </button>
                        
                        <div x-show="expanded" class="p-4 border-t border-gray-200">
                            <!-- Template form content similar to above -->
                            <p class="text-sm text-gray-500">Templates for other notifications can be configured in the same way.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Admin Users Tab -->
        <div x-show="activeTab === 'users'" class="bg-white shadow-sm rounded-lg overflow-hidden">
            <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-medium text-gray-900">Admin Users</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Manage users with administrative access.
                    </p>
                </div>
                
                <button type="button" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500" onclick="alert('This feature will be implemented in a future update')">
                    <i class="fas fa-plus mr-2"></i> Add Admin User
                </button>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Active</th>
                            <th scope="col" class="relative px-6 py-3">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($adminUsers ?? [] as $admin)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            @if($admin->profile_picture)
                                                <img class="h-10 w-10 rounded-full object-cover" src="{{ asset($admin->profile_picture) }}" alt="{{ $admin->name }}">
                                            @else
                                                <div class="h-10 w-10 rounded-full bg-primary-100 flex items-center justify-center">
                                                    <span class="text-primary-600 font-medium text-sm">{{ strtoupper(substr($admin->name, 0, 1)) }}</span>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $admin->name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $admin->email }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $admin->phone }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($admin->status == 'active')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Active
                                    </span>
                                 @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                        {{ ucfirst($admin->status) }}
                                    </span>
                                 @endif
                                 </td>
                                 <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $admin->last_active_at ? $admin->last_active_at->diffForHumans() : 'Never' }}
                                 </td>
                                 <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button class="text-primary-600 hover:text-primary-900" onclick="alert('Edit admin feature coming soon')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                 </td>
                                 </tr>
                                 @empty
                                 <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                        No admin users found.
                                    </td>
                                 </tr>
                                 @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        
                                        <!-- System Info Tab -->
                                        <div x-show="activeTab === 'system'" class="bg-white shadow-sm rounded-lg overflow-hidden">
                                            <div class="p-6 border-b border-gray-200">
                                                <h3 class="text-lg font-medium text-gray-900">System Information</h3>
                                                <p class="mt-1 text-sm text-gray-500">
                                                    Overview of your system configuration and environment.
                                                </p>
                                            </div>
                                            
                                            <div class="p-6">
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                    <div>
                                                        <h4 class="text-base font-medium text-gray-900 mb-4">Application</h4>
                                                        
                                                        <dl class="space-y-3">
                                                            <div class="flex justify-between">
                                                                <dt class="text-sm font-medium text-gray-500">Laravel Version</dt>
                                                                <dd class="text-sm text-gray-900">{{ app()->version() }}</dd>
                                                            </div>
                                                            <div class="flex justify-between">
                                                                <dt class="text-sm font-medium text-gray-500">PHP Version</dt>
                                                                <dd class="text-sm text-gray-900">{{ phpversion() }}</dd>
                                                            </div>
                                                            <div class="flex justify-between">
                                                                <dt class="text-sm font-medium text-gray-500">Environment</dt>
                                                                <dd class="text-sm text-gray-900">{{ app()->environment() }}</dd>
                                                            </div>
                                                            <div class="flex justify-between">
                                                                <dt class="text-sm font-medium text-gray-500">Debug Mode</dt>
                                                                <dd class="text-sm text-gray-900">
                                                                    @if(config('app.debug'))
                                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Enabled</span>
                                                                    @else
                                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Disabled</span>
                                                                    @endif
                                                                </dd>
                                                            </div>
                                                            <div class="flex justify-between">
                                                                <dt class="text-sm font-medium text-gray-500">Cache Driver</dt>
                                                                <dd class="text-sm text-gray-900">{{ config('cache.default') }}</dd>
                                                            </div>
                                                            <div class="flex justify-between">
                                                                <dt class="text-sm font-medium text-gray-500">Session Driver</dt>
                                                                <dd class="text-sm text-gray-900">{{ config('session.driver') }}</dd>
                                                            </div>
                                                        </dl>
                                                    </div>
                                                    
                                                    <div>
                                                        <h4 class="text-base font-medium text-gray-900 mb-4">Server</h4>
                                                        
                                                        <dl class="space-y-3">
                                                            <div class="flex justify-between">
                                                                <dt class="text-sm font-medium text-gray-500">Server Software</dt>
                                                                <dd class="text-sm text-gray-900">{{ $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' }}</dd>
                                                            </div>
                                                            <div class="flex justify-between">
                                                                <dt class="text-sm font-medium text-gray-500">Server Protocol</dt>
                                                                <dd class="text-sm text-gray-900">{{ $_SERVER['SERVER_PROTOCOL'] ?? 'Unknown' }}</dd>
                                                            </div>
                                                            <div class="flex justify-between">
                                                                <dt class="text-sm font-medium text-gray-500">HTTP Host</dt>
                                                                <dd class="text-sm text-gray-900">{{ $_SERVER['HTTP_HOST'] ?? 'Unknown' }}</dd>
                                                            </div>
                                                            <div class="flex justify-between">
                                                                <dt class="text-sm font-medium text-gray-500">Server Time</dt>
                                                                <dd class="text-sm text-gray-900">{{ now()->format('Y-m-d H:i:s') }}</dd>
                                                            </div>
                                                            <div class="flex justify-between">
                                                                <dt class="text-sm font-medium text-gray-500">Timezone</dt>
                                                                <dd class="text-sm text-gray-900">{{ config('app.timezone') }}</dd>
                                                            </div>
                                                            <div class="flex justify-between">
                                                                <dt class="text-sm font-medium text-gray-500">Server IP</dt>
                                                                <dd class="text-sm text-gray-900">{{ $_SERVER['SERVER_ADDR'] ?? 'Unknown' }}</dd>
                                                            </div>
                                                        </dl>
                                                    </div>
                                                </div>
                                                
                                                <div class="mt-8">
                                                    <h4 class="text-base font-medium text-gray-900 mb-4">Maintenance & Backups</h4>
                                                    
                                                    <div class="space-y-4">
                                                        <div class="bg-gray-50 p-4 rounded-md">
                                                            <div class="flex items-center justify-between">
                                                                <div>
                                                                    <h5 class="text-sm font-medium text-gray-900">Application Maintenance</h5>
                                                                    <p class="text-xs text-gray-500 mt-1">Put the application into maintenance mode for updates.</p>
                                                                </div>
                                                                <button type="button" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" onclick="alert('Maintenance mode feature coming soon')">
                                                                    Toggle Maintenance Mode
                                                                </button>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="bg-gray-50 p-4 rounded-md">
                                                            <div class="flex items-center justify-between">
                                                                <div>
                                                                    <h5 class="text-sm font-medium text-gray-900">Database Backup</h5>
                                                                    <p class="text-xs text-gray-500 mt-1">Create a backup of your database.</p>
                                                                </div>
                                                                <button type="button" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" onclick="alert('Database backup feature coming soon')">
                                                                    Create Backup
                                                                </button>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="bg-gray-50 p-4 rounded-md">
                                                            <div class="flex items-center justify-between">
                                                                <div>
                                                                    <h5 class="text-sm font-medium text-gray-900">Clear Cache</h5>
                                                                    <p class="text-xs text-gray-500 mt-1">Clear application cache, routes, views, and config.</p>
                                                                </div>
                                                                <button type="button" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" onclick="alert('Cache clearing feature coming soon')">
                                                                    Clear Cache
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                 
                                 <script>
                                    document.addEventListener('DOMContentLoaded', function() {
                                        // Handle tab navigation via URL hash
                                        if (window.location.hash) {
                                            let tab = window.location.hash.substring(1);
                                            // Dispatch a click event on the tab to activate it
                                            let tabButton = document.querySelector(`[x-data] button[x-on\\:click="activeTab = '${tab}'"]`);
                                            if (tabButton) {
                                                tabButton.click();
                                            }
                                        }
                                    });
                                 </script>
                             @endsection
                          