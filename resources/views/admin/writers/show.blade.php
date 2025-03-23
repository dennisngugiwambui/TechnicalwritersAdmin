@extends('admin.app')

@section('title', 'Writer Profile')

@section('page-title', 'Writer Profile')

@push('styles')
<style>
    .stat-card {
        transition: all 0.2s ease-in-out;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }
    .profile-tab-active {
        color: #4B5563;
        border-bottom: 2px solid #10B981;
    }
    .profile-tab {
        cursor: pointer;
        padding-bottom: 0.5rem;
        color: #9CA3AF;
        border-bottom: 2px solid transparent;
    }
    .profile-tab:hover {
        color: #374151;
    }
    .progress-ring__circle {
        stroke-dasharray: 400, 400;
        transition: stroke-dashoffset 0.35s;
        transform: rotate(-90deg);
        transform-origin: 50% 50%;
    }
</style>
@endpush

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Profile Header -->
    <div class="bg-white shadow-sm rounded-lg overflow-hidden mb-6">
        <div class="bg-gradient-to-r from-green-500 to-emerald-600 h-32 md:h-48"></div>
        <div class="px-6 py-4 md:flex md:items-center md:justify-between relative">
            <!-- Profile Picture and Name -->
            <div class="flex items-center md:block">
                <div class="absolute -top-16 border-4 border-white rounded-full overflow-hidden shadow-lg">
                    @if($writer->profile_picture)
                        <img src="{{ asset($writer->profile_picture) }}" alt="{{ $writer->name }}" class="h-32 w-32 object-cover">
                    @else
                        <div class="h-32 w-32 bg-gradient-to-br from-green-400 to-emerald-600 flex items-center justify-center text-white text-4xl font-bold">
                            {{ strtoupper(substr($writer->name, 0, 1)) }}
                        </div>
                    @endif
                </div>
                <div class="ml-6 md:ml-0 md:mt-16 mb-2">
                    <h1 class="text-2xl font-bold text-gray-800">{{ $writer->name }}</h1>
                    <p class="text-gray-600">{{ $writer->email }}</p>
                    @if($writer->writerProfile && $writer->writerProfile->title)
                        <p class="text-gray-600">{{ $writer->writerProfile->title }}</p>
                    @endif
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2 mt-4 md:mt-0">
                @if($writer->status == 'active')
                    <form action="{{ route('admin.writers.suspend', $writer->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="py-2 px-4 bg-yellow-500 hover:bg-yellow-600 text-white rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-opacity-50 text-sm">
                            <i class="fas fa-ban mr-2"></i> Suspend Account
                        </button>
                    </form>
                @else
                    <form action="{{ route('admin.writers.activate', $writer->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="py-2 px-4 bg-green-500 hover:bg-green-600 text-white rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50 text-sm">
                            <i class="fas fa-check-circle mr-2"></i> Activate Account
                        </button>
                    </form>
                @endif
                
                <a href="{{ route('admin.messages.create', ['recipient_type' => 'writer', 'recipient_id' => $writer->id]) }}" class="inline-flex items-center justify-center py-2 px-4 bg-indigo-500 hover:bg-indigo-600 text-white rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-opacity-50 text-sm">
                    <i class="fas fa-envelope mr-2"></i> Send Message
                </a>
                
                <a href="{{ route('admin.writers.edit', $writer->id) }}" class="inline-flex items-center justify-center py-2 px-4 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-opacity-50 text-sm">
                    <i class="fas fa-edit mr-2"></i> Edit Profile
                </a>
            </div>
        </div>
        
        <!-- Status Badge -->
        <div class="absolute top-4 right-4">
            @if($writer->status == 'active')
                <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium">Active</span>
            @elseif($writer->status == 'suspended')
                <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm font-medium">Suspended</span>
            @elseif($writer->status == 'pending')
                <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-medium">Pending Verification</span>
            @else
                <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm font-medium">{{ ucfirst($writer->status) }}</span>
            @endif
        </div>
    </div>
    
    <!-- Statistics Overview Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <!-- Total Orders -->
        <div class="bg-white shadow-sm rounded-lg overflow-hidden stat-card border-l-4 border-blue-500">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-100 rounded-md p-3">
                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Orders</dt>
                            <dd>
                                <div class="text-lg font-semibold text-gray-900">{{ $stats['total_orders'] }}</div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <a href="{{ route('admin.orders.index', ['writer_id' => $writer->id]) }}" class="font-medium text-blue-600 hover:text-blue-500">View all orders</a>
                </div>
            </div>
        </div>
        
        <!-- Completed Orders -->
        <div class="bg-white shadow-sm rounded-lg overflow-hidden stat-card border-l-4 border-green-500">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-100 rounded-md p-3">
                        <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Completed Orders</dt>
                            <dd>
                                <div class="text-lg font-semibold text-gray-900">{{ $stats['completed_orders'] }}</div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <a href="{{ route('admin.orders.index', ['writer_id' => $writer->id, 'status' => 'completed']) }}" class="font-medium text-green-600 hover:text-green-500">View completed orders</a>
                </div>
            </div>
        </div>
        
        <!-- In Progress Orders -->
        <div class="bg-white shadow-sm rounded-lg overflow-hidden stat-card border-l-4 border-yellow-500">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-yellow-100 rounded-md p-3">
                        <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">In Progress</dt>
                            <dd>
                                <div class="text-lg font-semibold text-gray-900">{{ $stats['in_progress'] }}</div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <a href="{{ route('admin.orders.index', ['writer_id' => $writer->id, 'status' => 'in_progress']) }}" class="font-medium text-yellow-600 hover:text-yellow-500">View in-progress orders</a>
                </div>
            </div>
        </div>
        
        <!-- Total Earnings -->
        <div class="bg-white shadow-sm rounded-lg overflow-hidden stat-card border-l-4 border-purple-500">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-purple-100 rounded-md p-3">
                        <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Earnings</dt>
                            <dd>
                                <div class="text-lg font-semibold text-gray-900">${{ number_format($stats['total_earnings'], 2) }}</div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <a href="{{ route('admin.finance.transactions', ['user_id' => $writer->id]) }}" class="font-medium text-purple-600 hover:text-purple-500">View financial details</a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Main Content Tabs -->
    <div x-data="{ activeTab: 'profile' }">
        <!-- Tab Navigation -->
        <div class="border-b border-gray-200 mb-6">
            <nav class="-mb-px flex space-x-6" aria-label="Tabs">
                <a @click.prevent="activeTab = 'profile'" href="#profile" :class="{'profile-tab-active': activeTab === 'profile'}" class="profile-tab">
                    <span class="text-sm font-medium">Profile Info</span>
                </a>
                <a @click.prevent="activeTab = 'orders'" href="#orders" :class="{'profile-tab-active': activeTab === 'orders'}" class="profile-tab">
                    <span class="text-sm font-medium">Orders</span>
                </a>
                <a @click.prevent="activeTab = 'finances'" href="#finances" :class="{'profile-tab-active': activeTab === 'finances'}" class="profile-tab">
                    <span class="text-sm font-medium">Finances</span>
                </a>
                <a @click.prevent="activeTab = 'activity'" href="#activity" :class="{'profile-tab-active': activeTab === 'activity'}" class="profile-tab">
                    <span class="text-sm font-medium">Activity</span>
                </a>
            </nav>
        </div>
        
        <!-- Profile Tab Content -->
        <div x-show="activeTab === 'profile'" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Writer Info Card -->
            <div class="lg:col-span-2">
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Writer Information</h3>
                    </div>
                    <div class="p-6">
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-6">
                            <div class="col-span-1">
                                <dt class="text-sm font-medium text-gray-500">Full Name</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $writer->name }}</dd>
                            </div>
                            
                            <div class="col-span-1">
                                <dt class="text-sm font-medium text-gray-500">Email Address</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $writer->email }}</dd>
                            </div>
                            
                            <div class="col-span-1">
                                <dt class="text-sm font-medium text-gray-500">Phone Number</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $writer->phone ?? 'Not provided' }}</dd>
                            </div>
                            
                            <div class="col-span-1">
                                <dt class="text-sm font-medium text-gray-500">Join Date</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $writer->created_at->format('M d, Y') }}</dd>
                            </div>
                            
                            <div class="col-span-1">
                                <dt class="text-sm font-medium text-gray-500">Account Status</dt>
                                <dd class="mt-1 text-sm">
                                    @if($writer->status == 'active')
                                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium">Active</span>
                                    @elseif($writer->status == 'suspended')
                                        <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-medium">Suspended</span>
                                    @elseif($writer->status == 'pending')
                                        <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-medium">Pending Verification</span>
                                    @else
                                        <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded-full text-xs font-medium">{{ ucfirst($writer->status) }}</span>
                                    @endif
                                </dd>
                            </div>
                            
                            <div class="col-span-1">
                                <dt class="text-sm font-medium text-gray-500">Last Active</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $writer->last_active_at ? $writer->last_active_at->diffForHumans() : 'Never' }}</dd>
                            </div>
                            
                            @if($writer->writerProfile)
                                <div class="col-span-2">
                                    <dt class="text-sm font-medium text-gray-500">Title/Position</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $writer->writerProfile->title ?? 'Not specified' }}</dd>
                                </div>
                                
                                <div class="col-span-1">
                                    <dt class="text-sm font-medium text-gray-500">Education Level</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $writer->writerProfile->education_level ?? 'Not specified' }}</dd>
                                </div>
                                
                                <div class="col-span-1">
                                    <dt class="text-sm font-medium text-gray-500">Experience (Years)</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $writer->writerProfile->experience_years ?? 'Not specified' }}</dd>
                                </div>
                                
                                <div class="col-span-2">
                                    <dt class="text-sm font-medium text-gray-500">Areas of Expertise</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        @if($writer->writerProfile->areas_of_expertise)
                                            <div class="flex flex-wrap gap-2">
                                                @foreach(explode(',', $writer->writerProfile->areas_of_expertise) as $expertise)
                                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">{{ trim($expertise) }}</span>
                                                @endforeach
                                            </div>
                                        @else
                                            Not specified
                                        @endif
                                    </dd>
                                </div>
                                
                                <div class="col-span-2">
                                    <dt class="text-sm font-medium text-gray-500">Bio</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $writer->writerProfile->bio ?? 'No bio available' }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </div>
            </div>
            
            <!-- Performance & Balance Cards -->
            <div class="space-y-6">
                <!-- Performance Card -->
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Performance Overview</h3>
                    </div>
                    <div class="p-6">
                        <div class="flex justify-center mb-4">
                            @php
                                $completionRate = $stats['total_orders'] > 0 
                                    ? round(($stats['completed_orders'] / $stats['total_orders']) * 100) 
                                    : 0;
                                $circumference = 50 * 2 * pi();
                                $strokeDashoffset = $circumference - ($completionRate / 100) * $circumference;
                            @endphp
                            
                            <div class="relative inline-block">
                                <svg class="transform -rotate-90 w-32 h-32">
                                    <circle class="text-gray-200" stroke-width="10" stroke="currentColor" fill="transparent" r="50" cx="65" cy="65" />
                                    <circle 
                                        class="text-green-500 progress-ring__circle" 
                                        stroke-width="10" 
                                        stroke-dasharray="{{ $circumference }} {{ $circumference }}"
                                        style="stroke-dashoffset: {{ $strokeDashoffset }};"
                                        stroke-linecap="round" 
                                        stroke="currentColor" 
                                        fill="transparent" 
                                        r="50" 
                                        cx="65" 
                                        cy="65" 
                                    />
                                </svg>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <span class="text-2xl font-bold text-gray-700">{{ $completionRate }}%</span>
                                </div>
                            </div>
                        </div>
                        
                        <h4 class="text-center text-sm font-medium text-gray-600 mb-4">Completion Rate</h4>
                        
                        <div class="space-y-4">
                            <div>
                                <div class="flex justify-between mb-1">
                                    <span class="text-xs text-gray-500">On-Time Delivery</span>
                                    <span class="text-xs font-medium text-gray-700">88%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-1.5">
                                    <div class="bg-blue-500 h-1.5 rounded-full" style="width: 88%"></div>
                                </div>
                            </div>
                            
                            <div>
                                <div class="flex justify-between mb-1">
                                    <span class="text-xs text-gray-500">Client Satisfaction</span>
                                    <span class="text-xs font-medium text-gray-700">92%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-1.5">
                                    <div class="bg-purple-500 h-1.5 rounded-full" style="width: 92%"></div>
                                </div>
                            </div>
                            
                            <div>
                                <div class="flex justify-between mb-1">
                                    <span class="text-xs text-gray-500">Quality Score</span>
                                    <span class="text-xs font-medium text-gray-700">85%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-1.5">
                                    <div class="bg-yellow-500 h-1.5 rounded-full" style="width: 85%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Balance Card -->
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Financial Summary</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div>
                                <h4 class="text-sm font-medium text-gray-500">Available Balance</h4>
                                <p class="mt-1 text-2xl font-semibold text-gray-900">${{ number_format($stats['available_balance'], 2) }}</p>
                            </div>
                            
                            <div>
                                <h4 class="text-sm font-medium text-gray-500">Pending Withdrawals</h4>
                                <p class="mt-1 text-lg font-medium text-gray-700">${{ number_format($stats['pending_withdrawals'], 2) }}</p>
                            </div>
                            
                            <div class="pt-4 border-t border-gray-200">
                                <a href="{{ route('admin.finance.transactions', ['user_id' => $writer->id]) }}" class="text-sm font-medium text-primary-600 hover:text-primary-500">
                                    View all transactions <span aria-hidden="true">&rarr;</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Disciplines Card -->
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Top Disciplines</h3>
                    </div>
                    <div class="p-6">
                        @if(count($ordersByDiscipline) > 0)
                            <div class="space-y-4">
                                @foreach($ordersByDiscipline as $discipline)
                                    <div>
                                        <div class="flex justify-between mb-1">
                                            <span class="text-xs text-gray-500">{{ $discipline->discipline }}</span>
                                            <span class="text-xs font-medium text-gray-700">{{ $discipline->count }} orders</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-1.5">
                                            @php
                                                $percentage = min(100, ($discipline->count / $stats['total_orders']) * 100);
                                            @endphp
                                            <div class="bg-indigo-500 h-1.5 rounded-full" style="width: {{ $percentage }}%"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-500">No discipline data available.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Orders Tab Content -->
        <div x-show="activeTab === 'orders'" class="space-y-6">
            <!-- Order Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white shadow-sm rounded-lg overflow-hidden p-6">
                    <h3 class="text-sm font-medium text-gray-500">Total Orders</h3>
                    <p class="mt-2 text-3xl font-bold text-gray-900">{{ $stats['total_orders'] }}</p>
                </div>
                
                <div class="bg-white shadow-sm rounded-lg overflow-hidden p-6">
                    <h3 class="text-sm font-medium text-gray-500">Completed</h3>
                    <p class="mt-2 text-3xl font-bold text-green-600">{{ $stats['completed_orders'] }}</p>
                </div>
                
                <div class="bg-white shadow-sm rounded-lg overflow-hidden p-6">
                    <h3 class="text-sm font-medium text-gray-500">In Progress</h3>
                    <p class="mt-2 text-3xl font-bold text-yellow-600">{{ $stats['in_progress'] }}</p>
                </div>
                
                <div class="bg-white shadow-sm rounded-lg overflow-hidden p-6">
                    <h3 class="text-sm font-medium text-gray-500">Revisions/Disputes</h3>
                    <p class="mt-2 text-3xl font-bold text-red-600">{{ $stats['revision_orders'] + $stats['dispute_orders'] }}</p>
                </div>
            </div>
            
            <!-- Recent Orders Table -->
            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900">Recent Orders</h3>
                    <a href="{{ route('admin.orders.index', ['writer_id' => $writer->id]) }}" class="text-sm font-medium text-primary-600 hover:text-primary-500">
                        View all
                    </a>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deadline</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($writer->ordersAsWriter()->latest()->limit(5)->get() as $order)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        #{{ $order->id }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 max-w-xs truncate">
                                        {{ $order->title }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <span class="{{ now()->gt($order->deadline) ? 'text-red-600' : 'text-gray-600' }}">
                                            {{ $order->deadline->format('M d, Y, g:i A') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if(in_array($order->status, ['completed', 'paid', 'finished']))
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                        @elseif(in_array($order->status, ['confirmed', 'in_progress', 'done', 'delivered']))
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                        @elseif($order->status == 'revision')
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                Revision
                                            </span>
                                        @elseif($order->status == 'dispute')
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                Dispute
                                            </span>
                                        @else
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        ${{ number_format($order->price, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('admin.orders.show', $order->id) }}" class="text-primary-600 hover:text-primary-900">View</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                        No orders found for this writer.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Finances Tab Content -->
        <div x-show="activeTab === 'finances'" class="space-y-6">
            <!-- Financial Overview Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white shadow-sm rounded-lg overflow-hidden p-6">
                    <h3 class="text-sm font-medium text-gray-500">Total Earnings</h3>
                    <p class="mt-2 text-3xl font-bold text-gray-900">${{ number_format($stats['total_earnings'], 2) }}</p>
                    <p class="mt-1 text-sm text-gray-500">Lifetime earnings from completed orders</p>
                </div>
                
                <div class="bg-white shadow-sm rounded-lg overflow-hidden p-6">
                    <h3 class="text-sm font-medium text-gray-500">Available Balance</h3>
                    <p class="mt-2 text-3xl font-bold text-green-600">${{ number_format($stats['available_balance'], 2) }}</p>
                    <p class="mt-1 text-sm text-gray-500">Amount available for withdrawal</p>
                </div>
                
                <div class="bg-white shadow-sm rounded-lg overflow-hidden p-6">
                    <h3 class="text-sm font-medium text-gray-500">Pending Withdrawals</h3>
                    <p class="mt-2 text-3xl font-bold text-yellow-600">${{ number_format($stats['pending_withdrawals'], 2) }}</p>
                    <p class="mt-1 text-sm text-gray-500">Withdrawal requests in process</p>
                </div>
            </div>
            
            <!-- Recent Transactions -->
            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900">Recent Transactions</h3>
                    <a href="{{ route('admin.finance.transactions', ['user_id' => $writer->id]) }}" class="text-sm font-medium text-primary-600 hover:text-primary-500">
                        View all
                    </a>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($writer->financialTransactions()->latest()->limit(5)->get() as $transaction)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $transaction->created_at->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 max-w-xs truncate">
                                        {{ $transaction->description }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($transaction->transaction_type == 'order_payment')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                Order Payment
                                            </span>
                                        @elseif($transaction->transaction_type == 'withdrawal')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                Withdrawal
                                            </span>
                                        @elseif($transaction->transaction_type == 'bonus')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Bonus
                                            </span>
                                        @elseif($transaction->transaction_type == 'penalty')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                Penalty
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                {{ ucfirst($transaction->transaction_type) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($transaction->status == 'completed')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Completed
                                            </span>
                                        @elseif($transaction->status == 'pending')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                Pending
                                            </span>
                                        @elseif($transaction->status == 'failed')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                Failed
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                {{ ucfirst($transaction->status) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-right">
                                        @if(in_array($transaction->transaction_type, ['withdrawal', 'penalty']))
                                            <span class="text-red-600">-${{ number_format($transaction->amount, 2) }}</span>
                                        @else
                                            <span class="text-green-600">+${{ number_format($transaction->amount, 2) }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                                        No transactions found for this writer.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Process Payment Section -->
            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Process Payment</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Bonus Payment -->
                        <div class="bg-green-50 p-4 rounded-lg">
                            <h4 class="text-base font-medium text-gray-900 mb-2">Add Bonus</h4>
                            <p class="text-sm text-gray-500 mb-4">Reward the writer with a bonus payment</p>
                            
                            <form action="{{ route('admin.finance.bonus.store') }}" method="POST" class="space-y-4">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ $writer->id }}">
                                
                                <div>
                                    <label for="bonus_amount" class="block text-sm font-medium text-gray-700">Amount ($)</label>
                                    <input type="number" min="0.01" step="0.01" name="amount" id="bonus_amount" class="mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required>
                                </div>
                                
                                <div>
                                    <label for="bonus_description" class="block text-sm font-medium text-gray-700">Description</label>
                                    <input type="text" name="description" id="bonus_description" class="mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required>
                                </div>
                                
                                <div>
                                    <button type="submit" class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                        Add Bonus
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Apply Penalty -->
                        <div class="bg-red-50 p-4 rounded-lg">
                            <h4 class="text-base font-medium text-gray-900 mb-2">Apply Penalty</h4>
                            <p class="text-sm text-gray-500 mb-4">Deduct funds for policy violations or quality issues</p>
                            
                            <form action="{{ route('admin.finance.penalty.store') }}" method="POST" class="space-y-4">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ $writer->id }}">
                                
                                <div>
                                    <label for="penalty_amount" class="block text-sm font-medium text-gray-700">Amount ($)</label>
                                    <input type="number" min="0.01" step="0.01" name="amount" id="penalty_amount" class="mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required>
                                </div>
                                
                                <div>
                                    <label for="penalty_description" class="block text-sm font-medium text-gray-700">Reason</label>
                                    <input type="text" name="description" id="penalty_description" class="mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required>
                                </div>
                                
                                <div>
                                    <button type="submit" class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                        Apply Penalty
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Activity Tab Content -->
        <div x-show="activeTab === 'activity'" class="space-y-6">
            <!-- Activity Timeline -->
            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Recent Activity</h3>
                </div>
                
                <div class="p-6 relative">
                    <!-- Timeline Line -->
                    <div class="absolute top-6 bottom-0 left-12 md:left-14 w-px bg-gray-200"></div>
                    
                    <ul class="space-y-6">
                        @forelse($recentActivity as $activity)
                            <li class="relative pl-10 md:pl-12">
                                <!-- Activity Dot -->
                                <div class="absolute left-9 md:left-11 -translate-x-1/2 -translate-y-0 w-6 h-6 flex items-center justify-center rounded-full 
                                    @if($activity['type'] == 'order')
                                        bg-blue-100
                                    @elseif($activity['type'] == 'transaction')
                                        @if(in_array($activity['data']->transaction_type, ['withdrawal', 'penalty']))
                                            bg-red-100
                                        @else
                                            bg-green-100
                                        @endif
                                    @else
                                        bg-gray-100
                                    @endif
                                    z-10">
                                    @if($activity['type'] == 'order')
                                        <i class="fas fa-clipboard-list text-xs 
                                            @if(in_array($activity['data']->status, ['completed', 'paid', 'finished']))
                                                text-green-600
                                            @elseif(in_array($activity['data']->status, ['confirmed', 'in_progress', 'done', 'delivered']))
                                                text-blue-600
                                            @elseif($activity['data']->status == 'revision')
                                                text-yellow-600
                                            @elseif($activity['data']->status == 'dispute')
                                                text-red-600
                                            @else
                                                text-gray-600
                                            @endif
                                        "></i>
                                    @elseif($activity['type'] == 'transaction')
                                        @if($activity['data']->transaction_type == 'order_payment')
                                            <i class="fas fa-money-bill-wave text-xs text-green-600"></i>
                                        @elseif($activity['data']->transaction_type == 'withdrawal')
                                            <i class="fas fa-money-bill-wave text-xs text-red-600"></i>
                                        @elseif($activity['data']->transaction_type == 'bonus')
                                            <i class="fas fa-gift text-xs text-green-600"></i>
                                        @elseif($activity['data']->transaction_type == 'penalty')
                                            <i class="fas fa-exclamation-triangle text-xs text-red-600"></i>
                                        @else
                                            <i class="fas fa-exchange-alt text-xs text-gray-600"></i>
                                        @endif
                                    @endif
                                </div>
                                
                                <!-- Activity Content -->
                                <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h4 class="text-sm font-medium text-gray-900">
                                                @if($activity['type'] == 'order')
                                                    Order #{{ $activity['data']->id }} {{ ucfirst($activity['data']->status) }}
                                                @elseif($activity['type'] == 'transaction')
                                                    @if($activity['data']->transaction_type == 'order_payment')
                                                        Payment Received
                                                    @elseif($activity['data']->transaction_type == 'withdrawal')
                                                        Withdrawal {{ ucfirst($activity['data']->status) }}
                                                    @elseif($activity['data']->transaction_type == 'bonus')
                                                        Bonus Payment
                                                    @elseif($activity['data']->transaction_type == 'penalty')
                                                        Penalty Applied
                                                    @else
                                                        {{ ucfirst($activity['data']->transaction_type) }}
                                                    @endif
                                                @endif
                                            </h4>
                                            <p class="text-xs text-gray-500">{{ $activity['date']->format('M d, Y, g:i A') }}</p>
                                        </div>
                                        
                                        @if($activity['type'] == 'transaction')
                                            <div class="text-sm font-medium 
                                                @if(in_array($activity['data']->transaction_type, ['withdrawal', 'penalty']))
                                                    text-red-600
                                                @else
                                                    text-green-600
                                                @endif">
                                                @if(in_array($activity['data']->transaction_type, ['withdrawal', 'penalty']))
                                                    -${{ number_format($activity['data']->amount, 2) }}
                                                @else
                                                    +${{ number_format($activity['data']->amount, 2) }}
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <div class="mt-2">
                                        @if($activity['type'] == 'order')
                                            <p class="text-sm text-gray-600">{{ Str::limit($activity['data']->title, 100) }}</p>
                                            <div class="mt-2 flex justify-between">
                                                <span class="text-xs text-gray-500">Price: ${{ number_format($activity['data']->price, 2) }}</span>
                                                <a href="{{ route('admin.orders.show', $activity['data']->id) }}" class="text-xs text-primary-600 hover:text-primary-900">View Details</a>
                                            </div>
                                        @elseif($activity['type'] == 'transaction')
                                            <p class="text-sm text-gray-600">{{ $activity['data']->description }}</p>
                                            @if($activity['data']->order_id)
                                                <div class="mt-2 flex justify-between">
                                                    <span class="text-xs text-gray-500">Related to Order #{{ $activity['data']->order_id }}</span>
                                                    <a href="{{ route('admin.orders.show', $activity['data']->order_id) }}" class="text-xs text-primary-600 hover:text-primary-900">View Order</a>
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </li>
                        @empty
                            <li class="text-center py-4 text-gray-500">
                                No recent activity found for this writer.
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>
            
            <!-- Login History -->
            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Login History</h3>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Address</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Device</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Browser</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <!-- Replace with actual login history data if available -->
                            @if(isset($loginHistory) && count($loginHistory) > 0)
                                @foreach($loginHistory as $login)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $login->created_at->format('M d, Y, g:i A') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $login->ip_address }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $login->device ?? 'Unknown' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $login->browser ?? 'Unknown' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @if($login->successful)
                                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    Successful
                                                </span>
                                            @else
                                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                    Failed
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                                        No login history available.
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endsection