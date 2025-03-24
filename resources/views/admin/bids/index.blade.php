@extends('admin.app')

@section('title', 'Available Orders and Bids')

@section('page-title', 'Available Orders and Bids')

@section('content')
<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <div class="p-6 border-b border-gray-200">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Available Orders</h2>
            <div class="flex space-x-2">
                <form action="{{ route('admin.bids') }}" method="GET" class="flex">
                    <input type="text" name="search" value="{{ request('search') }}" 
                        placeholder="Search orders..." 
                        class="rounded-l-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50">
                    <button type="submit" class="px-4 py-2 bg-primary-500 text-white rounded-r-md hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
                <a href="" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fas fa-sync-alt mr-2"></i> Scrape Orders
                </a>
            </div>
        </div>
    </div>

    @if($orders->isEmpty())
        <div class="p-10 text-center">
            <p class="text-gray-500">No available orders found. Check back later or scrape new orders.</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Order ID
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Title
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Type
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Price
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Deadline
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Bids
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($orders as $order)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            #{{ $order->id }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ Str::limit($order->title, 50) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $order->type_of_service }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            ${{ number_format($order->price, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $order->deadline->format('M d, Y, g:i a') }}
                            <br>
                            <span class="text-xs {{ now()->gt($order->deadline) ? 'text-red-500' : (now()->diffInHours($order->deadline) < 24 ? 'text-yellow-500' : 'text-green-500') }}">
                                {{ now()->gt($order->deadline) ? 'Expired' : $order->deadline->diffForHumans() }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                {{ $order->bids->count() }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('admin.bids.show', $order->id) }}" class="text-primary-600 hover:text-primary-900 mr-3">
                                <i class="fas fa-eye mr-1"></i> View Bids
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $orders->appends(request()->query())->links() }}
        </div>
    </div>
    @endif
</div>

@if(session('toast'))
<div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" 
    class="fixed top-4 right-4 z-50 bg-green-50 border-l-4 border-green-500 p-4 rounded shadow-lg flex items-start max-w-sm transform transition-transform duration-300 ease-in-out">
    <div class="text-green-500 mr-3">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
    </div>
    <div>
        <p class="font-medium text-green-800">{{ session('toast.title') }}</p>
        <p class="text-sm text-green-700 mt-1">{{ session('toast.message') }}</p>
    </div>
    <button @click="show = false" class="ml-auto text-green-500 hover:text-green-700">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
    </button>
</div>
@endif

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Any additional JavaScript for the bids page
    });
</script>
@endpush