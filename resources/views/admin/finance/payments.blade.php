@extends('admin.app')

@section('title', 'Payment Transactions')

@section('page-title', 'Payment Transactions')

@section('content')
<div class="space-y-6">
    <!-- Filter Controls -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <form action="{{ route('admin.finance.payments') }}" method="GET" class="space-y-4 sm:space-y-0 sm:flex sm:items-end sm:space-x-4">
            <div class="w-full sm:w-1/4">
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select id="status" name="status" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            
            <div class="w-full sm:w-1/4">
                <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                <input type="date" id="date_from" name="date_from" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" value="{{ request('date_from') }}">
            </div>
            
            <div class="w-full sm:w-1/4">
                <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                <input type="date" id="date_to" name="date_to" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" value="{{ request('date_to') }}">
            </div>
            
            <div>
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                    <i class="fas fa-filter mr-2"></i> Filter
                </button>
            </div>
        </form>
    </div>
    
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Pending Payments -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-yellow-100 rounded-full p-3">
                        <svg class="h-6 w-6 text-yellow-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Pending Payments</dt>
                            <dd class="text-xl font-semibold text-gray-900">{{ $pendingCount }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Pending Amount -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-yellow-100 rounded-full p-3">
                        <svg class="h-6 w-6 text-yellow-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Pending Amount</dt>
                            <dd class="text-xl font-semibold text-gray-900">${{ number_format($pendingAmount, 2) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Completed Today -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-100 rounded-full p-3">
                        <svg class="h-6 w-6 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Completed Today</dt>
                            <dd class="text-xl font-semibold text-gray-900">{{ $completedToday }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Completed Amount Today -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-100 rounded-full p-3">
                        <svg class="h-6 w-6 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Amount Today</dt>
                            <dd class="text-xl font-semibold text-gray-900">${{ number_format($completedAmountToday, 2) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Payments Table -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Payment Transactions</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($payments as $payment)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            #{{ $payment->id }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {!! $payment->type_badge !!}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $payment->formatted_amount }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {!! $payment->status_badge !!}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if ($payment->user)
                                <a href="{{ route('admin.users.show', $payment->user->id) }}" class="text-primary-600 hover:text-primary-900">
                                    {{ $payment->user->name }}
                                </a>
                            @else
                                <span class="text-gray-400">System</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if ($payment->order)
                                <a href="{{ route('admin.orders.show', $payment->order->id) }}" class="text-primary-600 hover:text-primary-900">
                                    #{{ $payment->order->id }}
                                </a>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $payment->created_at->format('M d, Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <div class="flex space-x-2">
                                <a href="{{ route('admin.finance.transaction', $payment->id) }}" class="text-indigo-600 hover:text-indigo-900" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                
                                @if ($payment->transaction_type == 'withdrawal' && $payment->status == 'pending')
                                    <button type="button" onclick="confirmTransaction({{ $payment->id }})" class="text-green-600 hover:text-green-900" title="Approve">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    
                                    <button type="button" onclick="rejectTransaction({{ $payment->id }})" class="text-red-600 hover:text-red-900" title="Reject">
                                        <i class="fas fa-times"></i>
                                    </button>
                                @endif
                                
                                @if ($payment->transaction_type == 'withdrawal' && $payment->status == 'processing')
                                    <button type="button" onclick="completeTransaction({{ $payment->id }})" class="text-green-600 hover:text-green-900" title="Mark as Completed">
                                        <i class="fas fa-check-double"></i>
                                    </button>
                                @endif
                                
                                @if ($payment->transaction_type == 'order_payment' && $payment->status == 'completed' && $payment->created_at->diffInDays(now()) < 30)
                                    <button type="button" onclick="initiateRefund({{ $payment->id }})" class="text-yellow-600 hover:text-yellow-900" title="Refund">
                                        <i class="fas fa-undo"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                            No payment transactions found matching your criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $payments->withQueryString()->links() }}
        </div>
    </div>
</div>

<!-- Include the same modals as in the transactions view -->
@include('admin.finance.partials.transaction-modals')

@endsection

@section('scripts')
<script>
    // Include the same JavaScript functions as in the transactions view
    @include('admin.finance.partials.transaction-scripts')
</script>
@endsection