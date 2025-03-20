@extends('admin.app')

@section('title', 'Financial Reports')

@section('page-title', 'Financial Reports')

@section('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.css">
@endsection

@section('content')
<div class="space-y-6">
    <!-- Filter Controls -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <form action="{{ route('admin.finance.reports') }}" method="GET" class="space-y-4 sm:space-y-0 sm:flex sm:items-end sm:space-x-4">
            <div class="w-full sm:w-1/4">
                <label for="period" class="block text-sm font-medium text-gray-700 mb-1">Report Period</label>
                <select id="period" name="period" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                    <option value="daily" {{ $period == 'daily' ? 'selected' : '' }}>Daily</option>
                    <option value="monthly" {{ $period == 'monthly' ? 'selected' : '' }}>Monthly</option>
                    <option value="yearly" {{ $period == 'yearly' ? 'selected' : '' }}>Yearly</option>
                </select>
            </div>
            
            <div class="w-full sm:w-1/4">
                <label for="year" class="block text-sm font-medium text-gray-700 mb-1">Year</label>
                <select id="year" name="year" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                    @for ($i = date('Y'); $i >= date('Y') - 5; $i--)
                        <option value="{{ $i }}" {{ $year == $i ? 'selected' : '' }}>{{ $i }}</option>
                    @endfor
                </select>
            </div>
            
            @if($period == 'daily')
            <div class="w-full sm:w-1/4">
                <label for="month" class="block text-sm font-medium text-gray-700 mb-1">Month</label>
                <select id="month" name="month" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                    @for ($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ $month == $i ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $i, 1)) }}</option>
                    @endfor
                </select>
            </div>
            @endif
            
            <div>
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                    <i class="fas fa-filter mr-2"></i> Generate Report
                </button>
            </div>
        </form>
    </div>
    
    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Revenue Chart -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Revenue Overview</h3>
            </div>
            <div class="p-6">
                <canvas id="revenueChart" height="300"></canvas>
            </div>
        </div>
        
        <!-- Writer Earnings Chart -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Writer Earnings</h3>
            </div>
            <div class="p-6">
                <canvas id="earningsChart" height="300"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Top Writers Table -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Top Writers by Earnings</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rank</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Writer</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order Payments</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bonuses</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Penalties</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Earnings</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($topWriters as $index => $writer)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $index + 1 }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <a href="{{ route('admin.users.show', $writer->id) }}" class="text-primary-600 hover:text-primary-900">
                                {{ $writer->name }}
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            ${{ number_format($writer->payments, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            ${{ number_format($writer->bonuses, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            ${{ number_format($writer->penalties, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            ${{ number_format($writer->total, 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                            No data available for the selected period.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>
<script>
    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    const revenueChart = new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($revenueData['labels']) !!},
            datasets: [
                {
                    label: 'Income',
                    data: {!! json_encode($revenueData['income']) !!},
                    backgroundColor: 'rgba(34, 197, 94, 0.2)',
                    borderColor: 'rgba(34, 197, 94, 1)',
                    borderWidth: 2,
                    tension: 0.1
                },
                {
                    label: 'Outgoing',
                    data: {!! json_encode($revenueData['outgoing']) !!},
                    backgroundColor: 'rgba(239, 68, 68, 0.2)',
                    borderColor: 'rgba(239, 68, 68, 1)',
                    borderWidth: 2,
                    tension: 0.1
                },
                {
                    label: 'Net',
                    data: {!! json_encode($revenueData['net']) !!},
                    backgroundColor: 'rgba(59, 130, 246, 0.2)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 2,
                    tension: 0.1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            tooltips: {
                callbacks: {
                    label: function(tooltipItem, data) {
                        return data.datasets[tooltipItem.datasetIndex].label + ': $' + 
                               parseFloat(tooltipItem.value).toFixed(2);
                    }
                }
            }
        }
    });
    
    // Writer Earnings Chart
    const earningsCtx = document.getElementById('earningsChart').getContext('2d');
    const earningsChart = new Chart(earningsCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($writerEarningsData['labels']) !!},
            datasets: [
                {
                    label: 'Order Payments',
                    data: {!! json_encode($writerEarningsData['payments']) !!},
                    backgroundColor: 'rgba(59, 130, 246, 0.7)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Bonuses',
                    data: {!! json_encode($writerEarningsData['bonuses']) !!},
                    backgroundColor: 'rgba(34, 197, 94, 0.7)',
                    borderColor: 'rgba(34, 197, 94, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Penalties',
                    data: {!! json_encode($writerEarningsData['penalties']) !!},
                    backgroundColor: 'rgba(239, 68, 68, 0.7)',
                    borderColor: 'rgba(239, 68, 68, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                },
                x: {
                    stacked: false
                }
            },
            tooltips: {
                callbacks: {
                    label: function(tooltipItem, data) {
                        return data.datasets[tooltipItem.datasetIndex].label + ': $' + 
                               parseFloat(tooltipItem.value).toFixed(2);
                    }
                }
            }
        }
    });
    
    // Show/hide month selector based on period
    document.getElementById('period').addEventListener('change', function() {
        const monthContainer = document.getElementById('month').parentNode;
        if (this.value === 'daily') {
            monthContainer.style.display = 'block';
        } else {
            monthContainer.style.display = 'none';
        }
    });
</script>
@endsection