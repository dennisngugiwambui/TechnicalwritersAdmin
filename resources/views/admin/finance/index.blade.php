@extends('admin.app')

@section('title', 'Finance Dashboard')

@section('page-title', 'Finance Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6">
        <!-- Total Revenue Card -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-indigo-100 rounded-full p-3">
                        <svg class="h-6 w-6 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Revenue</dt>
                            <dd class="flex items-baseline">
                                <div class="text-xl font-semibold text-gray-900">$</div>
                                <div class="ml-2 flex items-baseline text-sm font-semibold {{ $revenueChange >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    <svg class="self-center flex-shrink-0 h-4 w-4 {{ $revenueChange >= 0 ? 'text-green-500' : 'text-red-500' }}" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M{{ $revenueChange >= 0 ? '10 17a.75.75 0 01-.75-.75V5.612L5.29 9.77a.75.75 0 01-1.08-1.04l5.25-5.5a.75.75 0 011.08 0l5.25 5.5a.75.75 0 11-1.08 1.04l-3.96-4.158V16.25A.75.75 0 0110 17z' : '10 3a.75.75 0 01.75.75v10.638l3.96-4.158a.75.75 0 111.08 1.04l-5.25 5.5a.75.75 0 01-1.08 0l-5.25-5.5a.75.75 0 111.08-1.04l3.96 4.158V3.75A.75.75 0 0110 3z' }}" clip-rule="evenodd" />
                                    </svg>
                                    <span>{{ abs($revenueChange) }}%</span>
                                </div>
                            </dd>
                            <dd class="text-xs text-gray-500 mt-1">Compared to last month</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Writer Payments Card -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-100 rounded-full p-3">
                        <svg class="h-6 w-6 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Writer Payments</dt>
                            <dd class="flex items-baseline">
                                <div class="text-xl font-semibold text-gray-900">${{ number_format($writerPayments, 2) }}</div>
                            </dd>
                            <dd class="text-xs text-gray-500 mt-1">Total paid this month</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Payments Card -->
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
                            <dd class="flex items-baseline">
                                <div class="text-xl font-semibold text-gray-900">${{ number_format($pendingPayments, 2) }}</div>
                            </dd>
                            <dd class="text-xs text-gray-500 mt-1">Awaiting processing</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-yellow-50 px-5 py-3">
                <div class="text-sm">
                    <a href="{{ route('admin.finance.payments', ['status' => 'pending']) }}" class="font-medium text-yellow-700 hover:text-yellow-900">
                        View pending payments <span aria-hidden="true">&rarr;</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Profit Margin Card -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-100 rounded-full p-3">
                        <svg class="h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Profit Margin</dt>
                            <dd class="flex items-baseline">
                                <div class="text-xl font-semibold text-gray-900">{{ number_format($profitMargin, 1) }}%</div>
                                <div class="ml-2 flex items-baseline text-sm font-semibold {{ $marginChange >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    <svg class="self-center flex-shrink-0 h-4 w-4 {{ $marginChange >= 0 ? 'text-green-500' : 'text-red-500' }}" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M{{ $marginChange >= 0 ? '10 17a.75.75 0 01-.75-.75V5.612L5.29 9.77a.75.75 0 01-1.08-1.04l5.25-5.5a.75.75 0 011.08 0l5.25 5.5a.75.75 0 11-1.08 1.04l-3.96-4.158V16.25A.75.75 0 0110 17z' : '10 3a.75.75 0 01.75.75v10.638l3.96-4.158a.75.75 0 111.08 1.04l-5.25 5.5a.75.75 0 01-1.08 0l-5.25-5.5a.75.75 0 111.08-1.04l3.96 4.158V3.75A.75.75 0 0110 3z' }}" clip-rule="evenodd" />
                                    </svg>
                                    <span>{{ abs($marginChange) }}%</span>
                                </div>
                            </dd>
                            <dd class="text-xs text-gray-500 mt-1">Compared to last month</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue Chart -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-6 py-5 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Revenue Overview</h3>
            <div class="flex items-center">
                <div class="relative mr-2">
                    <select id="chartPeriod" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md">
                        <option value="30">Last 30 Days</option>
                        <option value="90">Last 90 Days</option>
                        <option value="180">Last 6 Months</option>
                        <option value="365">Last Year</option>
                    </select>
                </div>
                <div>
                    <div class="flex rounded-md shadow-sm">
                        <button type="button" class="relative inline-flex items-center px-4 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:z-10 focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-primary-500">
                            Revenue
                        </button>
                        <button type="button" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-gray-50 text-sm font-medium text-gray-700 hover:bg-gray-100 focus:z-10 focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-primary-500">
                            Profit
                        </button>
                        <button type="button" class="relative inline-flex items-center px-4 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:z-10 focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-primary-500">
                            Orders
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="p-6">
            <div class="h-80">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-6 py-5 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Recent Transactions</h3>
            <a href="{{ route('admin.finance.transactions') }}" class="text-sm font-medium text-primary-600 hover:text-primary-500">
                View all transactions
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Transaction ID
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Date
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Description
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Type
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Amount
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($recentTransactions as $transaction)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                #{{ $transaction->id }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $transaction->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">
                                {{ $transaction->description }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $transaction->transaction_type == 'payment' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                    {{ ucfirst($transaction->transaction_type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium {{ $transaction->transaction_type == 'payment' ? 'text-red-600' : 'text-green-600' }}">
                                {{ $transaction->transaction_type == 'payment' ? '-' : '+' }}${{ number_format($transaction->amount, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
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
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-sm font-medium text-gray-500">
                                No recent transactions
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('revenueChart').getContext('2d');
    
    // Sample data - would be replaced with real data from your controller
    const revenueData = {
        labels: @json($chartLabels),
        datasets: [
            {
                label: 'Revenue',
                backgroundColor: 'rgba(79, 70, 229, 0.1)',
                borderColor: 'rgba(79, 70, 229, 1)',
                borderWidth: 2,
                pointBackgroundColor: 'rgba(79, 70, 229, 1)',
                pointRadius: 3,
                data: @json($revenueData),
                tension: 0.4
            },
            {
                label: 'Writer Payments',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                borderColor: 'rgba(16, 185, 129, 1)',
                borderWidth: 2,
                pointBackgroundColor: 'rgba(16, 185, 129, 1)',
                pointRadius: 3,
                data: @json($paymentData),
                tension: 0.4
            }
        ]
    };
    
    const revenueChart = new Chart(ctx, {
        type: 'line',
        data: revenueData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    grid: {
                        display: false
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value;
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': $' + context.raw;
                        }
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });
    
    // Event listener for chart period change
    document.getElementById('chartPeriod').addEventListener('change', function() {
        const period = this.value;
        
        // In a real implementation, you'd make an AJAX call to get new data
        fetch(`/admin/finance/chart-data?period=${period}`)
            .then(response => response.json())
            .then(data => {
                revenueChart.data.labels = data.labels;
                revenueChart.data.datasets[0].data = data.revenue;
                revenueChart.data.datasets[1].data = data.payments;
                revenueChart.update();
            });
    });
});
</script>
@endpush