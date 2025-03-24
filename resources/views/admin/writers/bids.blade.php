@extends('layouts.admin')

@section('title', 'Order Bids')

@section('styles')
<style>
    .preferred-badge {
        position: absolute;
        top: -10px;
        right: -10px;
        z-index: 10;
    }
    
    .writer-avatar {
        width: 50px;
        height: 50px;
        object-fit: cover;
    }
    
    .bid-card {
        transition: all 0.3s ease;
        border-left: 4px solid transparent;
    }
    
    .bid-card:hover {
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        transform: translateY(-2px);
    }
    
    .bid-card.top-preferred {
        border-left: 4px solid #10B981;
    }
    
    .bid-card.preferred {
        border-left: 4px solid #3B82F6;
    }
    
    .bid-card.regular {
        border-left: 4px solid #9CA3AF;
    }
    
    .preference-meter {
        width: 100%;
        height: 8px;
        background-color: #E5E7EB;
        border-radius: 9999px;
        overflow: hidden;
    }
    
    .preference-meter div {
        height: 100%;
        border-radius: 9999px;
    }
    
    .progress-ring {
        transform: rotate(-90deg);
    }
    
    .progress-ring__circle {
        transition: stroke-dashoffset 0.5s;
        transform-origin: center;
        stroke-width: 5;
        fill: transparent;
    }
    
    .score-circle {
        height: 70px;
        width: 70px;
        position: relative;
    }
    
    .score-value {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 1.25rem;
        font-weight: bold;
    }
    
    .bid-details-section {
        transition: max-height 0.3s ease;
        max-height: 0;
        overflow: hidden;
    }
    
    .bid-details-section.expanded {
        max-height: 1000px;
    }
    
    @media (max-width: 640px) {
        .bid-cards-container {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Bids for Order #{{ $order->id }}</h1>
                <p class="mt-1 text-sm text-gray-600">{{ $order->title }}</p>
            </div>
            <div>
                <a href="{{ route('admin.orders.show', $order->id) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                    </svg>
                    Back to Order
                </a>
            </div>
        </div>

        <!-- Order Details Card -->
        <div class="mt-6 bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6">
                <div class="flex flex-col md:flex-row md:justify-between md:items-center">
                    <div>
                        <h2 class="text-lg font-medium text-gray-900">Order Details</h2>
                        <p class="mt-1 text-sm text-gray-600">{{ $order->description ?? substr($order->instructions, 0, 100) . '...' }}</p>
                    </div>
                    <div class="mt-4 md:mt-0">
                        <div class="flex space-x-2">
                            <span class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                ${{ number_format($order->price, 2) }}
                            </span>
                            <span class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                {{ $order->deadline->format('M d, Y') }}
                            </span>
                            <span class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                                {{ $order->discipline }}
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6 border-t border-gray-200 pt-4">
                    <dl class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Pages/Words</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $order->task_size }} pages</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Service Type</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $order->type_of_service }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    @if($order->status == 'available') bg-green-100 text-green-800 
                                    @elseif(in_array($order->status, ['confirmed', 'in_progress'])) bg-blue-100 text-blue-800 
                                    @elseif($order->status == 'completed') bg-purple-100 text-purple-800 
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Bids Statistics -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-5">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    Total Bids
                                </dt>
                                <dd>
                                    <div class="text-lg font-medium text-gray-900">
                                        {{ $bids->count() }}
                                    </div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    Average Bid
                                </dt>
                                <dd>
                                    <div class="text-lg font-medium text-gray-900">
                                        ${{ $bids->count() > 0 ? number_format($bids->avg('amount'), 2) : '0.00' }}
                                    </div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    Fastest Delivery
                                </dt>
                                <dd>
                                    <div class="text-lg font-medium text-gray-900">
                                        @if($bids->count() > 0)
                                            @php
                                                $fastestBid = $bids->sortBy('delivery_time')->first();
                                                $now = \Carbon\Carbon::now();
                                                $days = $now->diffInDays($fastestBid->delivery_time);
                                            @endphp
                                            {{ $days }} days
                                        @else
                                            N/A
                                        @endif
                                    </div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Writer Bids Section -->
        <div class="mt-8">
            <h2 class="text-lg leading-6 font-medium text-gray-900">Writer Bids</h2>
            
            @if($bids->count() > 0)
                <div class="mt-4 grid grid-cols-1 gap-6 bid-cards-container">
                    @foreach($bids as $index => $bid)
                        @php
                            $badgeClass = $index === 0 ? 'top-preferred' : ($bid->preference_score >= 70 ? 'preferred' : 'regular');
                            $scoreColor = $index === 0 ? '#10B981' : ($bid->preference_score >= 70 ? '#3B82F6' : '#9CA3AF');
                            $circumference = 2 * 3.14159 * 30;
                            $scoreOffset = $circumference - ($bid->preference_score / 100) * $circumference;
                        @endphp
                        
                        <div class="relative bg-white rounded-lg shadow-md overflow-hidden bid-card {{ $badgeClass }}" data-bid-id="{{ $bid->id }}">
                            @if($index === 0)
                                <div class="preferred-badge">
                                    <span class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-green-100 text-green-800 shadow-sm">
                                        <svg class="-ml-1 mr-1.5 h-4 w-4 text-green-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        Top Match
                                    </span>
                                </div>
                            @elseif($bid->preference_score >= 70)
                                <div class="preferred-badge">
                                    <span class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-blue-100 text-blue-800 shadow-sm">
                                        <svg class="-ml-1 mr-1.5 h-4 w-4 text-blue-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                        Preferred
                                    </span>
                                </div>
                            @endif
                            
                            <div class="p-6">
                                <div class="flex flex-col md:flex-row">
                                    <!-- Writer info and bid details -->
                                    <div class="flex-1">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                @if($bid->user->profile_picture)
                                                    <img class="h-12 w-12 rounded-full writer-avatar" src="{{ asset($bid->user->profile_picture) }}" alt="{{ $bid->user->name }}">
                                                @else
                                                    <div class="h-12 w-12 rounded-full bg-gradient-to-r from-purple-500 to-indigo-600 flex items-center justify-center">
                                                        <span class="text-white text-lg font-bold">{{ substr($bid->user->name, 0, 1) }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="ml-4">
                                                <h3 class="text-lg font-medium text-gray-900 flex items-center">
                                                    {{ $bid->user->name }}
                                                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $bid->user->rating >= 4 ? 'green' : ($bid->user->rating >= 3 ? 'blue' : 'gray') }}-100 text-{{ $bid->user->rating >= 4 ? 'green' : ($bid->user->rating >= 3 ? 'blue' : 'gray') }}-800">
                                                        {{ number_format($bid->user->rating, 1) }} <svg class="ml-0.5 h-3 w-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                        </svg>
                                                    </span>
                                                </h3>
                                                <div class="flex flex-wrap items-center text-sm text-gray-600 mt-1">
                                                    <span class="flex items-center mr-3">
                                                        <svg class="mr-1 h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                                                        </svg>
                                                        {{ $bid->years_in_system }} years
                                                    </span>
                                                    <span class="flex items-center mr-3">
                                                        <svg class="mr-1 h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                            <path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z" />
                                                        </svg>
                                                        {{ $bid->completed_orders }} orders
                                                    </span>
                                                    @if($bid->has_discipline_experience)
                                                        <span class="flex items-center text-green-600">
                                                            <svg class="mr-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                            </svg>
                                                            {{ $order->discipline }} experience
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-3">
                                            <div class="bg-gray-50 rounded-md p-3">
                                                <div class="text-sm text-gray-500">Bid Amount</div>
                                                <div class="font-medium text-gray-900">${{ number_format($bid->amount, 2) }}</div>
                                            </div>
                                            <div class="bg-gray-50 rounded-md p-3">
                                                <div class="text-sm text-gray-500">Delivery Time</div>
                                                <div class="font-medium text-gray-900">{{ $bid->delivery_time->format('M d, Y') }}</div>
                                            </div>
                                            <div class="bg-gray-50 rounded-md p-3">
                                                <div class="text-sm text-gray-500">Days to Deliver</div>
                                                <div class="font-medium text-gray-900">{{ \Carbon\Carbon::now()->diffInDays($bid->delivery_time) }} days</div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Preference score visualization -->
                                    <div class="mt-6 md:mt-0 md:ml-6 flex flex-col items-center">
                                        <div class="score-circle">
                                            <svg class="progress-ring" width="70" height="70">
                                                <circle class="progress-ring__circle" stroke="#E5E7EB" r="30" cx="35" cy="35" />
                                                <circle class="progress-ring__circle" stroke="{{ $scoreColor }}" r="30" cx="35" cy="35" stroke-dasharray="{{ $circumference }}" stroke-dashoffset="{{ $scoreOffset }}" />
                                            </svg>
                                            <div class="score-value">{{ round($bid->preference_score) }}</div>
                                        </div>
                                        <div class="text-sm text-gray-600 mt-1">Match Score</div>
                                    </div>
                                </div>
                                
                                <!-- Expand/Collapse toggle -->
                                <div class="mt-4 text-right">
                                    <button type="button" class="text-sm text-indigo-600 hover:text-indigo-900 font-medium toggle-details" data-bid-id="{{ $bid->id }}">
                                        View Details
                                        <svg class="inline-block ml-1 h-4 w-4 transform transition-transform duration-200 toggle-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </div>
                                
                                <!-- Expandable details section -->
                                <div class="bid-details-section mt-4 pt-4 border-t border-gray-200" id="bid-details-{{ $bid->id }}">
                                    @if($bid->cover_letter)
                                        <div class="mb-4">
                                            <h4 class="text-sm font-medium text-gray-900">Cover Letter</h4>
                                            <div class="mt-1 text-sm text-gray-600 bg-gray-50 rounded-md p-3">
                                                {{ $bid->cover_letter }}
                                            </div>
                                        </div>
                                    @endif
                                    
                                    <div class="mb-4">
                                        <h4 class="text-sm font-medium text-gray-900">Writer Specialty</h4>
                                        <div class="mt-1 text-sm text-gray-600">
                                            {{ $bid->user->specialization ?? $bid->user->writerProfile->areas_of_expertise ?? 'Not specified' }}
                                        </div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <h4 class="text-sm font-medium text-gray-900">Education Level</h4>
                                        <div class="mt-1 text-sm text-gray-600">
                                            {{ $bid->user->writerProfile->education_level ?? 'Not specified' }}
                                        </div>
                                    </div>
                                    
                                    <div class="flex flex-wrap gap-2 mt-4">
                                        <form action="{{ route('admin.orders.accept-bid', ['id' => $order->id, 'bidId' => $bid->id]) }}" method="POST" class="inline-block">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                                Accept Bid
                                            </button>
                                        </form>
                                        
                                        <button type="button" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 reject-bid-btn" data-bid-id="{{ $bid->id }}" data-writer-name="{{ $bid->user->name }}">
                                            Reject Bid
                                        </button>
                                        
                                        <a href="{{ route('admin.writers.show', $bid->user_id) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            View Writer Profile
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="mt-4 bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="p-6 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No bids yet</h3>
                        <p class="mt-1 text-sm text-gray-500">No writers have placed bids on this order yet.</p>
                        <div class="mt-6">
                            <button type="button" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" onclick="document.getElementById('inviteWriterModal').classList.remove('hidden')">
                                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 016 6H2a6 6 0 016-6zM16 7a1 1 0 10-2 0v1h-1a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1V7z" />
                                </svg>
                                Invite Writers
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Available Writers Section -->
        <div class="mt-8">
            <div class="flex justify-between items-center">
                <h2 class="text-lg leading-6 font-medium text-gray-900">Available Writers</h2>
                <button type="button" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" onclick="document.getElementById('inviteWriterModal').classList.remove('hidden')">
                    <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 016 6H2a6 6 0 016-6zM16 7a1 1 0 10-2 0v1h-1a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1V7z" />
                    </svg>
                    Invite Writers
                </button>
            </div>
            
            <div class="mt-4 bg-white shadow overflow-hidden sm:rounded-md">
                <ul class="divide-y divide-gray-200">
                    @forelse($availableWriters as $writer)
                        <li>
                            <div class="px-4 py-4 sm:px-6 flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        @if($writer->profile_picture)
                                            <img class="h-10 w-10 rounded-full" src="{{ asset($writer->profile_picture) }}" alt="{{ $writer->name }}">
                                        @else
                                            <div class="h-10 w-10 rounded-full bg-gradient-to-r from-gray-400 to-gray-600 flex items-center justify-center">
                                                <span class="text-white font-bold">{{ substr($writer->name, 0, 1) }}</span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $writer->name }}</div>
                                        <div class="text-sm text-gray-500">
                                            {{ $writer->writerProfile->title ?? $writer->specialization ?? 'General Writer' }}
                                            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-{{ $writer->rating >= 4 ? 'green' : ($writer->rating >= 3 ? 'blue' : 'gray') }}-100 text-{{ $writer->rating >= 4 ? 'green' : ($writer->rating >= 3 ? 'blue' : 'gray') }}-800">
                                                {{ number_format($writer->rating, 1) }} <svg class="ml-0.5 h-3 w-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                </svg>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex space-x-2">
                                    <button type="button" class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 invite-writer-btn" data-writer-id="{{ $writer->id }}" data-writer-name="{{ $writer->name }}">
                                        Invite
                                    </button>
                                    
                                    <form action="{{ route('admin.orders.assign', $order->id) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="writer_id" value="{{ $writer->id }}">
                                        <input type="hidden" name="from_bids" value="1">
                                        <button type="submit" class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                            Assign
                                        </button>
                                    </form>
                                    
                                    <a href="{{ route('admin.writers.show', $writer->id) }}" class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 text-xs font-medium rounded shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        Profile
                                    </a>
                                </div>
                            </div>
                        </li>
                    @empty
                        <li class="px-4 py-5 sm:px-6 text-center">
                            <p class="text-sm text-gray-500">No available writers found.</p>
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Invite Writer Modal -->
<div id="inviteWriterModal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
            <div>
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100">
                    <svg class="h-6 w-6 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <div class="mt-3 text-center sm:mt-5">
                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                        Invite Writer
                    </h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-500">
                            Send an invitation to a writer to bid on this order.
                        </p>
                    </div>
                </div>
            </div>
            
            <form action="{{ route('admin.orders.invite-writer', $order->id) }}" method="POST" class="mt-5 sm:mt-6">
                @csrf
                <div>
                    <label for="writer_id" class="block text-sm font-medium text-gray-700">Select Writer</label>
                    <select id="writer_id" name="writer_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                        <option value="">Select a writer</option>
                        @foreach($availableWriters as $writer)
                            <option value="{{ $writer->id }}">{{ $writer->name }} ({{ number_format($writer->rating, 1) }} ★)</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="mt-4">
                    <label for="message" class="block text-sm font-medium text-gray-700">Invitation Message (Optional)</label>
                    <textarea id="message" name="message" rows="4" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Enter your message to the writer...">I'd like to invite you to bid on this order based on your expertise and experience. Please review the order details and submit your bid if you're interested.</textarea>
                </div>
                
                <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:col-start-2 sm:text-sm">
                        Send Invitation
                    </button>
                    <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:col-start-1 sm:text-sm" onclick="document.getElementById('inviteWriterModal').classList.add('hidden')">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Bid Modal -->
<div id="rejectBidModal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
            <div>
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                    <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
                <div class="mt-3 text-center sm:mt-5">
                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="reject-modal-title">
                        Reject Bid
                    </h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-500" id="reject-modal-writer">
                            You are about to reject the bid from <span class="font-medium">Writer Name</span>. 
                        </p>
                    </div>
                </div>
            </div>
            
            <form id="reject-bid-form" action="" method="POST" class="mt-5 sm:mt-6">
                @csrf
                <div>
                    <label for="rejection_reason" class="block text-sm font-medium text-gray-700">Reason for Rejection (Optional)</label>
                    <textarea id="rejection_reason" name="rejection_reason" rows="4" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm" placeholder="Please provide a reason for rejecting this bid..."></textarea>
                </div>
                
                <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:col-start-2 sm:text-sm">
                        Reject Bid
                    </button>
                    <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:col-start-1 sm:text-sm" onclick="document.getElementById('rejectBidModal').classList.add('hidden')">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Create Bid Modal -->
<div id="createBidModal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
            <div>
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                    <svg class="h-6 w-6 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                </div>
                <div class="mt-3 text-center sm:mt-5">
                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="create-bid-modal-title">
                        Create Bid for Writer
                    </h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-500">
                            Create a bid on behalf of a writer for this order.
                        </p>
                    </div>
                </div>
            </div>
            
            <form action="{{ route('admin.orders.create-bid', $order->id) }}" method="POST" class="mt-5 sm:mt-6">
                @csrf
                <div>
                    <label for="bid_writer_id" class="block text-sm font-medium text-gray-700">Select Writer</label>
                    <select id="bid_writer_id" name="writer_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" required>
                        <option value="">Select a writer</option>
                        @foreach($availableWriters as $writer)
                            <option value="{{ $writer->id }}">{{ $writer->name }} ({{ number_format($writer->rating, 1) }} ★)</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="mt-4">
                    <label for="amount" class="block text-sm font-medium text-gray-700">Bid Amount ($)</label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">$</span>
                        </div>
                        <input type="number" name="amount" id="amount" step="0.01" min="0" value="{{ $order->price }}" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-7 pr-12 sm:text-sm border-gray-300 rounded-md" placeholder="0.00" required>
                    </div>
                </div>
                
                <div class="mt-4">
                    <label for="delivery_time" class="block text-sm font-medium text-gray-700">Delivery Time</label>
                    <input type="datetime-local" name="delivery_time" id="delivery_time" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" value="{{ $order->deadline->format('Y-m-d\TH:i') }}" required>
                </div>
                
                <div class="mt-4">
                    <label for="cover_letter" class="block text-sm font-medium text-gray-700">Cover Letter</label>
                    <textarea id="cover_letter" name="cover_letter" rows="4" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Enter cover letter...">I am interested in working on this order. I have experience in this subject area and can deliver quality work by the deadline.</textarea>
                </div>
                
                <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:col-start-2 sm:text-sm">
                        Create Bid
                    </button>
                    <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:col-start-1 sm:text-sm" onclick="document.getElementById('createBidModal').classList.add('hidden')">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle bid details
        document.querySelectorAll('.toggle-details').forEach(function(button) {
            button.addEventListener('click', function() {
                const bidId = this.getAttribute('data-bid-id');
                const detailsSection = document.getElementById('bid-details-' + bidId);
                const toggleIcon = this.querySelector('.toggle-icon');
                
                if (detailsSection.classList.contains('expanded')) {
                    detailsSection.classList.remove('expanded');
                    toggleIcon.classList.remove('rotate-180');
                    this.textContent = 'View Details ';
                    this.appendChild(toggleIcon);
                } else {
                    detailsSection.classList.add('expanded');
                    toggleIcon.classList.add('rotate-180');
                    this.textContent = 'Hide Details ';
                    this.appendChild(toggleIcon);
                }
            });
        });
        
        // Handle invite writer button clicks
        document.querySelectorAll('.invite-writer-btn').forEach(function(button) {
            button.addEventListener('click', function() {
                const writerId = this.getAttribute('data-writer-id');
                const writerName = this.getAttribute('data-writer-name');
                
                // Set the selected writer in the modal
                document.getElementById('writer_id').value = writerId;
                
                // Show the modal
                document.getElementById('inviteWriterModal').classList.remove('hidden');
            });
        });
        
        // Handle reject bid button clicks
        document.querySelectorAll('.reject-bid-btn').forEach(function(button) {
            button.addEventListener('click', function() {
                const bidId = this.getAttribute('data-bid-id');
                const writerName = this.getAttribute('data-writer-name');
                
                // Update the form action
                document.getElementById('reject-bid-form').action = "{{ route('admin.orders.reject-bid', ['id' => $order->id, 'bidId' => '']) }}" + bidId;
                
                // Update the writer name in the modal
                document.getElementById('reject-modal-writer').innerHTML = 'You are about to reject the bid from <span class="font-medium">' + writerName + '</span>.';
                
                // Show the modal
                document.getElementById('rejectBidModal').classList.remove('hidden');
            });
        });
        
        // Add button to show create bid modal
        const createBidButton = document.createElement('button');
        createBidButton.className = 'ml-3 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500';
        createBidButton.innerHTML = `
            <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
            </svg>
            Create Bid
        `;
        createBidButton.addEventListener('click', function() {
            document.getElementById('createBidModal').classList.remove('hidden');
        });
        
        // Find the invite writers button and add this button after it
        const inviteButton = document.querySelector('button[onclick="document.getElementById(\'inviteWriterModal\').classList.remove(\'hidden\')"]');
        if (inviteButton && inviteButton.parentNode) {
            inviteButton.parentNode.appendChild(createBidButton);
        }
        
        // Close modals when clicking on backdrop
        document.querySelectorAll('#inviteWriterModal, #rejectBidModal, #createBidModal').forEach(function(modal) {
            modal.addEventListener('click', function(event) {
                if (event.target === this) {
                    this.classList.add('hidden');
                }
            });
        });
        
        // Escape key to close modals
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                document.querySelectorAll('#inviteWriterModal, #rejectBidModal, #createBidModal').forEach(function(modal) {
                    modal.classList.add('hidden');
                });
            }
        });
    });
</script>
@endsection