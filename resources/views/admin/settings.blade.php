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
                @method('PUT')
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="site_name" class="block text-sm font-medium text-gray-700">Site Name</label>
                        <input type="text" name="site_name" id="site_name" class="mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" value="{{ env('APP_NAME', 'TechnicalwritersAdmin') }}">
                        <p class="mt-1 text-xs text-gray-500">Current value from .env: {{ env('APP_NAME', 'TechnicalwritersAdmin') }}</p>
                    </div>
                    
                    <div>
                        <label for="site_url" class="block text-sm font-medium text-gray-700">Site URL</label>
                        <input type="url" name="site_url" id="site_url" class="mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" value="{{ env('APP_URL', 'https://support.technicalwriters.co.ke') }}">
                    </div>
                    
                    <div>
                        <label for="site_email" class="block text-sm font-medium text-gray-700">Site Email</label>
                        <input type="email" name="site_email" id="site_email" class="mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" value="{{ Cache::get('setting_site_email', 'support@technicalwriters.co.ke') }}">
                    </div>
                    
                    <div>
                        <label for="support_email" class="block text-sm font-medium text-gray-700">Support Email</label>
                        <input type="email" name="support_email" id="support_email" class="mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" value="{{ Cache::get('setting_support_email', 'help@technicalwriters.co.ke') }}">
                    </div>
                    
                    <div>
                        <label for="support_phone" class="block text-sm font-medium text-gray-700">Support Phone</label>
                        <input type="text" name="support_phone" id="support_phone" class="mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" value="{{ Cache::get('setting_support_phone', '+254708374149') }}">
                    </div>
                    
                    <div>
                        <label for="notification_email" class="block text-sm font-medium text-gray-700">Notification Email</label>
                        <input type="email" name="notification_email" id="notification_email" class="mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" value="{{ Cache::get('setting_notification_email', 'notifications@technicalwriters.co.ke') }}">
                        <p class="mt-1 text-xs text-gray-500">All system notifications will be sent to this email address.</p>
                    </div>
                </div>
                
                <div>
                    <label for="company_address" class="block text-sm font-medium text-gray-700">Company Address</label>
                    <textarea name="company_address" id="company_address" rows="3" class="mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">{{ Cache::get('setting_company_address', 'Nairobi, Kenya') }}</textarea>
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
                                    M-Pesa integration is configured through environment variables. The current settings are shown below.
                                    Callback URLs should be set to <strong>https://support.technicalwriters.co.ke/api/mpesa/callback</strong> in your Safaricom developer account.
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <form method="POST" action="{{ route('admin.settings.update-mpesa') }}" class="space-y-6">
                        @csrf
                        @method('PUT')
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label for="mpesa_environment" class="block text-sm font-medium text-gray-700">Environment</label>
                                <select id="mpesa_environment" name="mpesa_environment" class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                                    <option value="sandbox" {{ config('mpesa.environment', 'sandbox') === 'sandbox' ? 'selected' : '' }}>Sandbox (Testing)</option>
                                    <option value="production" {{ config('mpesa.environment', 'sandbox') === 'production' ? 'selected' : '' }}>Production (Live)</option>
                                </select>
                            </div>
                            
                            <div>
                                <label for="mpesa_shortcode" class="block text-sm font-medium text-gray-700">Business Shortcode</label>
                                <input type="text" id="mpesa_shortcode" name="mpesa_shortcode" class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm" value="{{ config('mpesa.shortcode', '600979') }}">
                            </div>
                            
                            <div>
                                <label for="mpesa_consumer_key" class="block text-sm font-medium text-gray-700">Consumer Key</label>
                                <input type="text" id="mpesa_consumer_key" name="mpesa_consumer_key" class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm" value="{{ config('mpesa.consumer_key', 'HxKzmQueIV2rNL8ag4Y8c0A1dEHxYnkQdWOMvFY5tSBhBDdF') }}">
                            </div>
                            
                            <div>
                                <label for="mpesa_consumer_secret" class="block text-sm font-medium text-gray-700">Consumer Secret</label>
                                <input type="password" id="mpesa_consumer_secret" name="mpesa_consumer_secret" class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm" value="{{ config('mpesa.consumer_secret', 'GcC6pGSTLioUNb4XbGto7AHLh6T6ToZW7aBeDBGdGGeg1KVGUVpDE0AuuRylIGTt') }}">
                            </div>
                            
                            <div>
                                <label for="mpesa_initiator" class="block text-sm font-medium text-gray-700">Initiator Name</label>
                                <input type="text" id="mpesa_initiator" name="mpesa_initiator" class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm" value="{{ config('mpesa.b2c_initiator', 'testapi') }}">
                            </div>
                            
                            <div>
                                <label for="mpesa_security_credential" class="block text-sm font-medium text-gray-700">Security Credential</label>
                                <textarea id="mpesa_security_credential" name="mpesa_security_credential" rows="2" class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm text-xs">{{ config('mpesa.security_credential', 'IAJVUHDGj0yDU3aop/WI9oSPhkW3DVlh7EAt3iRyymTZhljpzCNnI/xFKZNooOf8PUFgjmEOihUnB24adZDOv3Ri0Citk60LgMQnib0gjsoc9WnkHmGYqGtNivWE20jyIDUtEKLlPr3snV4d/H54uwSRVcsATEQPNl5n3+EGgJFIKQzZbhxDaftMnxQNGoIHF9+77tfIFzvhYQen352F4D0SmiqQ91TbVc2Jdfx/wd4HEdTBU7S6ALWfuCCqWICHMqCnpCi+Y/ow2JRjGYHdfgmcY8pP5oyH25uQk1RpWV744aj2UROjDrxTnE7a6tDN6G/dA21MXKaIsWJT/JyyXg==') }}</textarea>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label for="mpesa_callback_url" class="block text-sm font-medium text-gray-700">Callback URL</label>
                                <input type="url" id="mpesa_callback_url" name="mpesa_callback_url" class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm" value="{{ config('mpesa.callback_url', 'https://support.technicalwriters.co.ke/api/mpesa/callback') }}">
                            </div>
                            
                            <div>
                                <label for="mpesa_timeout_url" class="block text-sm font-medium text-gray-700">Timeout URL</label>
                                <input type="url" id="mpesa_timeout_url" name="mpesa_timeout_url" class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm" value="{{ config('mpesa.timeout_url', 'https://support.technicalwriters.co.ke/api/mpesa/timeout') }}">
                            </div>
                            
                            <div>
                                <label for="mpesa_result_url" class="block text-sm font-medium text-gray-700">Result URL</label>
                                <input type="url" id="mpesa_result_url" name="mpesa_result_url" class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm" value="{{ config('mpesa.result_url', 'https://support.technicalwriters.co.ke/api/mpesa/result') }}">
                            </div>
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                Update M-Pesa Settings
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Exchange Rate Settings -->
                <div>
                    <h4 class="text-base font-medium text-gray-900 mb-4">Currency Exchange Rate</h4>
                    
                    <form method="POST" action="{{ route('admin.settings.update-exchange-rate') }}" class="space-y-6">
                        @csrf
                        @method('PUT')
                        <div class="max-w-md">
                            <label for="exchange_rate" class="block text-sm font-medium text-gray-700">USD to KES Exchange Rate</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">1 USD =</span>
                                </div>
                                <input type="number" step="0.01" min="1" name="exchange_rate" id="exchange_rate" class="focus:ring-primary-500 focus:border-primary-500 block w-full pl-16 pr-12 sm:text-sm border-gray-300 rounded-md" value="{{ $exchangeRate ?? 140.00 }}">
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
                    <!-- Order Assignment Template -->
                    <div x-data="{ expanded: false }" class="border border-gray-200 rounded-md overflow-hidden">
                        <button @click="expanded = !expanded" class="w-full px-4 py-3 flex items-center justify-between bg-gray-50 hover:bg-gray-100 transition-colors duration-150 focus:outline-none">
                            <div class="flex items-center">
                                <i class="fas fa-envelope-open-text text-primary-500 mr-2"></i>
                                <span class="font-medium">Order Assignment Notification</span>
                            </div>
                            <i class="fas" :class="{'fa-chevron-down': !expanded, 'fa-chevron-up': expanded}"></i>
                        </button>
                        
                        <div x-show="expanded" class="p-4 border-t border-gray-200">
                            <form method="POST" action="{{ route('admin.settings.update-email-template') }}" class="space-y-4">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="template_key" value="order_assignment">
                                
                                <div>
                                    <label for="subject_order_assignment" class="block text-sm font-medium text-gray-700">Email Subject</label>
                                    <input type="text" id="subject_order_assignment" name="subject" class="mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" value="{{ $emailTemplates['order_assignment']['subject'] ?? 'New Order Assignment: [Order_ID] - [Order_Title]' }}">
                                </div>
                                
                                <div>
                                    <label for="body_order_assignment" class="block text-sm font-medium text-gray-700">Email Body</label>
                                    <textarea id="body_order_assignment" name="body" rows="10" class="mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">{{ $emailTemplates['order_assignment']['body'] ?? "Hello [Writer_Name],

You have been assigned a new order.

Order ID: [Order_ID]
Title: [Order_Title]
Deadline: [Order_Deadline]
Payment: $[Order_Price]

Please log in to your dashboard to view the full details and accept the assignment.

Best regards,
Technical Writers Team" }}</textarea>
                                </div>
                                
                                <div class="bg-gray-50 p-3 rounded-md">
                                    <h5 class="text-sm font-medium text-gray-700 mb-2">Available Variables</h5>
                                    <div class="flex flex-wrap gap-2">
                                        <span class="px-2 py-1 text-xs rounded-md bg-gray-200 text-gray-700">[Writer_Name]</span>
                                        <span class="px-2 py-1 text-xs rounded-md bg-gray-200 text-gray-700">[Order_ID]</span>
                                        <span class="px-2 py-1 text-xs rounded-md bg-gray-200 text-gray-700">[Order_Title]</span>
                                        <span class="px-2 py-1 text-xs rounded-md bg-gray-200 text-gray-700">[Order_Deadline]</span>
                                        <span class="px-2 py-1 text-xs rounded-md bg-gray-200 text-gray-700">[Order_Price]</span>
                                        <span class="px-2 py-1 text-xs rounded-md bg-gray-200 text-gray-700">[Writer_Dashboard_URL]</span>
                                    </div>
                                </div>
                                
                                <div class="flex justify-end">
                                    <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                        Save Template
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Revision Request Template -->
                    <div x-data="{ expanded: false }" class="border border-gray-200 rounded-md overflow-hidden">
                        <button @click="expanded = !expanded" class="w-full px-4 py-3 flex items-center justify-between bg-gray-50 hover:bg-gray-100 transition-colors duration-150 focus:outline-none">
                            <div class="flex items-center">
                                <i class="fas fa-sync-alt text-orange-500 mr-2"></i>
                                <span class="font-medium">Revision Request Notification</span>
                            </div>
                            <i class="fas" :class="{'fa-chevron-down': !expanded, 'fa-chevron-up': expanded}"></i>
                        </button>
                        
                        <div x-show="expanded" class="p-4 border-t border-gray-200">
                            <form method="POST" action="{{ route('admin.settings.update-email-template') }}" class="space-y-4">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="template_key" value="revision_request">
                                
                                <div>
                                    <label for="subject_revision" class="block text-sm font-medium text-gray-700">Email Subject</label>
                                    <input type="text" id="subject_revision" name="subject" class="mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" value="{{ $emailTemplates['revision_request']['subject'] ?? 'Revision Requested: [Order_ID] - [Order_Title]' }}">
                                </div>
                                
                                <div>
                                    <label for="body_revision" class="block text-sm font-medium text-gray-700">Email Body</label>
                                    <textarea id="body_revision" name="body" rows="10" class="mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">{{ $emailTemplates['revision_request']['body'] ?? "Hello [Writer_Name],

A revision has been requested for your order.

Order ID: [Order_ID]
Title: [Order_Title]
Revision Comments: [Revision_Comments]

Please log in to your dashboard to view the full details and submit the revised work by [Revision_Deadline].

Best regards,
Technical Writers Team" }}</textarea>
                                </div>
                                
                                <div class="bg-gray-50 p-3 rounded-md">
                                    <h5 class="text-sm font-medium text-gray-700 mb-2">Available Variables</h5>
                                    <div class="flex flex-wrap gap-2">
                                        <span class="px-2 py-1 text-xs rounded-md bg-gray-200 text-gray-700">[Writer_Name]</span>
                                        <span class="px-2 py-1 text-xs rounded-md bg-gray-200 text-gray-700">[Order_ID]</span>
                                        <span class="px-2 py-1 text-xs rounded-md bg-gray-200 text-gray-700">[Order_Title]</span>
                                        <span class="px-2 py-1 text-xs rounded-md bg-gray-200 text-gray-700">[Revision_Comments]</span>
                                        <span class="px-2 py-1 text-xs rounded-md bg-gray-200 text-gray-700">[Revision_Deadline]</span>
                                        <span class="px-2 py-1 text-xs rounded-md bg-gray-200 text-gray-700">[Writer_Dashboard_URL]</span>
                                    </div>
                                </div>
                                
                                <div class="flex justify-end">
                                    <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                        Save Template
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Order Completion Template -->
                    <div x-data="{ expanded: false }" class="border border-gray-200 rounded-md overflow-hidden">
                        <button @click="expanded = !expanded" class="w-full px-4 py-3 flex items-center justify-between bg-gray-50 hover:bg-gray-100 transition-colors duration-150 focus:outline-none">
                            <div class="flex items-center">
                                <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                <span class="font-medium">Order Completion Notification</span>
                            </div>
                            <i class="fas" :class="{'fa-chevron-down': !expanded, 'fa-chevron-up': expanded}"></i>
                        </button>
                        
                        <div x-show="expanded" class="p-4 border-t border-gray-200">
                            <form method="POST" action="{{ route('admin.settings.update-email-template') }}" class="space-y-4">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="template_key" value="order_completion">
                                
                                <div>
                                    <label for="subject_completion" class="block text-sm font-medium text-gray-700">Email Subject</label>
                                    <input type="text" id="subject_completion" name="subject" class="mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" value="{{ $emailTemplates['order_completion']['subject'] ?? 'Order Completed: [Order_ID] - [Order_Title]' }}">
                                </div>
                                
                                <div>
                                    <label for="body_completion" class="block text-sm font-medium text-gray-700">Email Body</label>
                                    <textarea id="body_completion" name="body" rows="10" class="mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">{{ $emailTemplates['order_completion']['body'] ?? "Hello [Writer_Name],

Congratulations! Your work on the following order has been completed and approved.

Order ID: [Order_ID]
Title: [Order_Title]
Payment Amount: $[Order_Price]

Your payment will be processed according to our payment schedule. You can view your payment details in your dashboard.

Thank you for your excellent work!

Best regards,
Technical Writers Team" }}</textarea>
                                </div>
                                
                                <div class="bg-gray-50 p-3 rounded-md">
                                    <h5 class="text-sm font-medium text-gray-700 mb-2">Available Variables</h5>
                                    <div class="flex flex-wrap gap-2">
                                        <span class="px-2 py-1 text-xs rounded-md bg-gray-200 text-gray-700">[Writer_Name]</span>
                                        <span class="px-2 py-1 text-xs rounded-md bg-gray-200 text-gray-700">[Order_ID]</span>
                                        <span class="px-2 py-1 text-xs rounded-md bg-gray-200 text-gray-700">[Order_Title]</span>
                                        <span class="px-2 py-1 text-xs rounded-md bg-gray-200 text-gray-700">[Order_Price]</span>
                                        <span class="px-2 py-1 text-xs rounded-md bg-gray-200 text-gray-700">[Writer_Dashboard_URL]</span>
                                    </div>
                                </div>
                                
                                <div class="flex justify-end">
                                    <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                        Save Template
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Payment Notification Template -->
                    <div x-data="{ expanded: false }" class="border border-gray-200 rounded-md overflow-hidden">
                        <button @click="expanded = !expanded" class="w-full px-4 py-3 flex items-center justify-between bg-gray-50 hover:bg-gray-100 transition-colors duration-150 focus:outline-none">
                            <div class="flex items-center">
                                <i class="fas fa-money-bill-wave text-green-500 mr-2"></i>
                                <span class="font-medium">Payment Notification</span>
                            </div>
                            <i class="fas" :class="{'fa-chevron-down': !expanded, 'fa-chevron-up': expanded}"></i>
                        </button>
                        
                        <div x-show="expanded" class="p-4 border-t border-gray-200">
                            <form method="POST" action="{{ route('admin.settings.update-email-template') }}" class="space-y-4">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="template_key" value="payment_notification">
                                
                                <div>
                                    <label for="subject_payment" class="block text-sm font-medium text-gray-700">Email Subject</label>
                                    <input type="text" id="subject_payment" name="subject" class="mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" value="{{ $emailTemplates['payment_notification']['subject'] ?? 'Payment Processed: $[Payment_Amount]' }}">
                                </div>
                                
                                <div>
                                    <label for="body_payment" class="block text-sm font-medium text-gray-700">Email Body</label>
                                    <textarea id="body_payment" name="body" rows="10" class="mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">{{ $emailTemplates['payment_notification']['body'] ?? "Hello [Writer_Name],

We're pleased to inform you that your payment has been processed.

Payment Details:
- Amount: $[Payment_Amount]
- Transaction ID: [Transaction_ID]
- Payment Method: [Payment_Method]
- Date: [Payment_Date]

This payment covers the following completed orders:
[Order_List]

If you have any questions about this payment, please contact our support team.

Best regards,
Technical Writers Finance Team" }}</textarea>
                                </div>
                                
                                <div class="bg-gray-50 p-3 rounded-md">
                                    <h5 class="text-sm font-medium text-gray-700 mb-2">Available Variables</h5>
                                    <div class="flex flex-wrap gap-2">
                                        <span class="px-2 py-1 text-xs rounded-md bg-gray-200 text-gray-700">[Writer_Name]</span>
                                        <span class="px-2 py-1 text-xs rounded-md bg-gray-200 text-gray-700">[Payment_Amount]</span>
                                        <span class="px-2 py-1 text-xs rounded-md bg-gray-200 text-gray-700">[Transaction_ID]</span>
                                        <span class="px-2 py-1 text-xs rounded-md bg-gray-200 text-gray-700">[Payment_Method]</span>
                                        <span class="px-2 py-1 text-xs rounded-md bg-gray-200 text-gray-700">[Payment_Date]</span>
                                        <span class="px-2 py-1 text-xs rounded-md bg-gray-200 text-gray-700">[Order_List]</span>
                                        <span class="px-2 py-1 text-xs rounded-md bg-gray-200 text-gray-700">[Writer_Dashboard_URL]</span>
                                    </div>
                                </div>
                                
                                <div class="flex justify-end">
                                    <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                        Save Template
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Account Suspension Template -->
                    <div x-data="{ expanded: false }" class="border border-gray-200 rounded-md overflow-hidden">
                        <button @click="expanded = !expanded" class="w-full px-4 py-3 flex items-center justify-between bg-gray-50 hover:bg-gray-100 transition-colors duration-150 focus:outline-none">
                            <div class="flex items-center">
                                <i class="fas fa-user-slash text-red-500 mr-2"></i>
                                <span class="font-medium">Account Suspension Notification</span>
                            </div>
                            <i class="fas" :class="{'fa-chevron-down': !expanded, 'fa-chevron-up': expanded}"></i>
                        </button>
                        
                        <div x-show="expanded" class="p-4 border-t border-gray-200">
                            <form method="POST" action="{{ route('admin.settings.update-email-template') }}" class="space-y-4">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="template_key" value="account_suspension">
                                
                                <div>
                                    <label for="subject_suspension" class="block text-sm font-medium text-gray-700">Email Subject</label>
                                    <input type="text" id="subject_suspension" name="subject" class="mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" value="{{ $emailTemplates['account_suspension']['subject'] ?? 'Account Temporarily Suspended' }}">
                                </div>
                                
                                <div>
                                    <label for="body_suspension" class="block text-sm font-medium text-gray-700">Email Body</label>
                                    <textarea id="body_suspension" name="body" rows="10" class="mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">{{ $emailTemplates['account_suspension']['body'] ?? "Hello [Writer_Name],

We regret to inform you that your account has been temporarily suspended.

Reason for suspension: [Suspension_Reason]

During this suspension period, you will not be able to access your account or receive new orders. Any pending orders will be reassigned to other writers.

If you believe this suspension was made in error or you would like to discuss this further, please contact our support team at [Support_Email].

Best regards,
Technical Writers Team" }}</textarea>
                                </div>
                                
                                <div class="bg-gray-50 p-3 rounded-md">
                                    <h5 class="text-sm font-medium text-gray-700 mb-2">Available Variables</h5>
                                    <div class="flex flex-wrap gap-2">
                                        <span class="px-2 py-1 text-xs rounded-md bg-gray-200 text-gray-700">[Writer_Name]</span>
                                        <span class="px-2 py-1 text-xs rounded-md bg-gray-200 text-gray-700">[Suspension_Reason]</span>
                                        <span class="px-2 py-1 text-xs rounded-md bg-gray-200 text-gray-700">[Suspension_Duration]</span>
                                        <span class="px-2 py-1 text-xs rounded-md bg-gray-200 text-gray-700">[Support_Email]</span>
                                    </div>
                                </div>
                                
                                <div class="flex justify-end">
                                    <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                        Save Template
                                    </button>
                                </div>
                            </form>
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
                
                <button type="button" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500" @click="$dispatch('open-modal', 'add-admin-modal')">
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
                                    <button class="text-primary-600 hover:text-primary-900 mr-3" @click="$dispatch('open-modal', {id: 'edit-admin-modal', admin: {{ json_encode($admin) }}})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    @if($admin->id != auth()->id())
                                    <button class="text-red-600 hover:text-red-900" @click="$dispatch('open-modal', {id: 'delete-admin-modal', admin: {{ json_encode($admin) }}})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endif
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
                                <form method="POST" action="{{ route('admin.settings.toggle-maintenance') }}">
                                    @csrf
                                    @method('POST')
                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white {{ app()->isDownForMaintenance() ? 'bg-green-600 hover:bg-green-700' : 'bg-indigo-600 hover:bg-indigo-700' }} focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        {{ app()->isDownForMaintenance() ? 'Exit Maintenance Mode' : 'Enable Maintenance Mode' }}
                                    </button>
                                </form>
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 p-4 rounded-md">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h5 class="text-sm font-medium text-gray-900">Database Backup</h5>
                                    <p class="text-xs text-gray-500 mt-1">Create a backup of your database.</p>
                                </div>
                                <form method="POST" action="{{ route('admin.settings.create-backup') }}">
                                    @csrf
                                    @method('POST')
                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        Create Backup
                                    </button>
                                </form>
                            </div>
                            
                            @if(isset($backups) && count($backups) > 0)
                                <div class="mt-3">
                                    <h6 class="text-xs font-medium text-gray-700 mb-2">Recent Backups</h6>
                                    <ul class="text-xs text-gray-600 space-y-1">
                                        @foreach($backups as $backup)
                                            <li class="flex justify-between items-center">
                                                <span>{{ $backup['date'] }} ({{ $backup['size'] }})</span>
                                                <a href="{{ route('admin.settings.download-backup', ['filename' => $backup['filename']]) }}" class="text-primary-600 hover:text-primary-900">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                        
                        <div class="bg-gray-50 p-4 rounded-md">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h5 class="text-sm font-medium text-gray-900">Clear Cache</h5>
                                    <p class="text-xs text-gray-500 mt-1">Clear application cache, routes, views, and config.</p>
                                </div>
                                <form method="POST" action="{{ route('admin.settings.clear-cache') }}">
                                    @csrf
                                    @method('POST')
                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        Clear Cache
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add Admin Modal -->
    <div x-data="{ open: false }" 
         @open-modal.window="if($event.detail === 'add-admin-modal') open = true" 
         @keydown.escape.window="open = false"
         x-show="open" 
         class="fixed inset-0 z-50 overflow-y-auto" 
         x-cloak>
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div x-show="open" @click="open = false" class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <!-- Modal panel -->
        
            <div x-show="open" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-primary-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fas fa-user-plus text-primary-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Add New Admin User
                            </h3>
                            <div class="mt-4">
                                <form id="add-admin-form" method="POST" action="{{ route('admin.users.store') }}">
                                    @csrf
                                    <div class="space-y-4">
                                        <div>
                                            <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                                            <input type="text" name="name" id="name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm" required>
                                        </div>
                                        
                                        <div>
                                            <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                                            <input type="email" name="email" id="email" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm" required>
                                        </div>
                                        
                                        <div>
                                            <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                                            <input type="text" name="phone" id="phone" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                                        </div>
                                        
                                        <div>
                                            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                                            <input type="password" name="password" id="password" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm" required>
                                        </div>
                                        
                                        <div>
                                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                                            <input type="password" name="password_confirmation" id="password_confirmation" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm" required>
                                        </div>
                                        
                                        <div>
                                            <label for="role" class="block text-sm font-medium text-gray-700">Admin Role</label>
                                            <select name="role" id="role" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                                                <option value="admin">General Admin</option>
                                                <option value="super_admin">Super Admin</option>
                                            </select>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" form="add-admin-form" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary-600 text-base font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Add Admin
                    </button>
                    <button @click="open = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Edit Admin Modal -->
    <div x-data="{ 
            open: false, 
            admin: null,
            init() {
                this.$watch('open', value => {
                    if (value && this.admin) {
                        this.$nextTick(() => {
                            document.getElementById('edit_name').value = this.admin.name;
                            document.getElementById('edit_email').value = this.admin.email;
                            document.getElementById('edit_phone').value = this.admin.phone || '';
                            document.getElementById('edit_role').value = this.admin.role || 'admin';
                            document.getElementById('edit_status').value = this.admin.status || 'active';
                        });
                    }
                });
            }
        }" 
         @open-modal.window="if($event.detail.id === 'edit-admin-modal') { admin = $event.detail.admin; open = true; }" 
         @keydown.escape.window="open = false"
         x-show="open" 
         class="fixed inset-0 z-50 overflow-y-auto" 
         x-cloak>
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div x-show="open" @click="open = false" class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <!-- Modal panel -->
            <div x-show="open" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-primary-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fas fa-user-edit text-primary-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Edit Admin User
                            </h3>
                            <div class="mt-4">
                                <form x-if="admin" id="edit-admin-form" :action="`{{ route('admin.users.update', '') }}/${admin?.id}`" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="space-y-4">
                                        <div>
                                            <label for="edit_name" class="block text-sm font-medium text-gray-700">Name</label>
                                            <input type="text" name="name" id="edit_name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm" required>
                                        </div>
                                        
                                        <div>
                                            <label for="edit_email" class="block text-sm font-medium text-gray-700">Email Address</label>
                                            <input type="email" name="email" id="edit_email" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm" required>
                                        </div>
                                        
                                        <div>
                                            <label for="edit_phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                                            <input type="text" name="phone" id="edit_phone" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                                        </div>
                                        
                                        <div>
                                            <label for="edit_password" class="block text-sm font-medium text-gray-700">New Password (leave blank to keep current)</label>
                                            <input type="password" name="password" id="edit_password" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                                        </div>
                                        
                                        <div>
                                            <label for="edit_role" class="block text-sm font-medium text-gray-700">Admin Role</label>
                                            <select name="role" id="edit_role" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                                                <option value="admin">General Admin</option>
                                                <option value="super_admin">Super Admin</option>
                                            </select>
                                        </div>
                                        
                                        <div>
                                            <label for="edit_status" class="block text-sm font-medium text-gray-700">Status</label>
                                            <select name="status" id="edit_status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                                                <option value="active">Active</option>
                                                <option value="inactive">Inactive</option>
                                                <option value="suspended">Suspended</option>
                                            </select>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" form="edit-admin-form" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary-600 text-base font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Update Admin
                    </button>
                    <button @click="open = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Delete Admin Modal -->
    <div x-data="{ 
            open: false, 
            admin: null
        }" 
         @open-modal.window="if($event.detail.id === 'delete-admin-modal') { admin = $event.detail.admin; open = true; }" 
         @keydown.escape.window="open = false"
         x-show="open" 
         class="fixed inset-0 z-50 overflow-y-auto" 
         x-cloak>
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div x-show="open" @click="open = false" class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <!-- Modal panel -->
            <div x-show="open" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fas fa-exclamation-triangle text-red-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Delete Admin User
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Are you sure you want to delete this admin user? This action cannot be undone.
                                </p>
                                <p class="mt-2 text-sm font-medium text-gray-900" x-text="`Name: ${admin?.name}`"></p>
                                <p class="text-sm text-gray-700" x-text="`Email: ${admin?.email}`"></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <form x-if="admin" :action="`{{ route('admin.users.destroy', '') }}/${admin?.id}`" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Delete
                        </button>
                    </form>
                    <button @click="open = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    @if(session('success'))
    <div x-data="{ show: true }" 
         x-init="setTimeout(() => show = false, 5000)" 
         x-show="show"
         class="fixed bottom-4 right-4 bg-green-50 p-4 rounded-md shadow-lg border-l-4 border-green-400 z-50">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-check-circle text-green-400"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-green-800">
                    {{ session('success') }}
                </p>
            </div>
            <div class="ml-auto pl-3">
                <div class="-mx-1.5 -my-1.5">
                    <button @click="show = false" class="inline-flex rounded-md p-1.5 text-green-500 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        <span class="sr-only">Dismiss</span>
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
    
    @if(session('error'))
    <div x-data="{ show: true }" 
         x-init="setTimeout(() => show = false, 5000)" 
         x-show="show"
         class="fixed bottom-4 right-4 bg-red-50 p-4 rounded-md shadow-lg border-l-4 border-red-400 z-50">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-circle text-red-400"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-red-800">
                    {{ session('error') }}
                </p>
            </div>
            <div class="ml-auto pl-3">
                <div class="-mx-1.5 -my-1.5">
                    <button @click="show = false" class="inline-flex rounded-md p-1.5 text-red-500 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        <span class="sr-only">Dismiss</span>
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
                                 
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