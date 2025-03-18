@extends('admin.app')


@section('content')
    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Available Orders -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-100 rounded-full p-3">
                        <svg class="h-8 w-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Available Orders</dt>
                            <dd>
                                <div class="text-lg font-semibold text-gray-900">{{ $availableOrdersCount ?? 0 }}</div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-blue-50 px-5 py-3">
                <div class="text-sm">
                    <a href="{{ route('admin.orders.index', ['status' => 'available']) }}" class="font-medium text-blue-700 hover:text-blue-900">
                        View all available orders
                        <span aria-hidden="true">&rarr;</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- In Progress Orders -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-orange-100 rounded-full p-3">
                        <svg class="h-8 w-8 text-orange-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">In Progress Orders</dt>
                            <dd>
                                <div class="text-lg font-semibold text-gray-900">{{ $inProgressOrdersCount ?? 0 }}</div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-orange-50 px-5 py-3">
                <div class="text-sm">
                    <a href="{{ route('admin.orders.index', ['status' => 'in_progress']) }}" class="font-medium text-orange-700 hover:text-orange-900">
                        View in progress orders
                        <span aria-hidden="true">&rarr;</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Completed Orders -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-100 rounded-full p-3">
                        <svg class="h-8 w-8 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Completed Orders</dt>
                            <dd>
                                <div class="text-lg font-semibold text-gray-900">{{ $completedOrdersCount ?? 0 }}</div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-green-50 px-5 py-3">
                <div class="text-sm">
                    <a href="{{ route('admin.orders.index', ['status' => 'completed']) }}" class="font-medium text-green-700 hover:text-green-900">
                        View completed orders
                        <span aria-hidden="true">&rarr;</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Total Writers -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-purple-100 rounded-full p-3">
                        <svg class="h-8 w-8 text-purple-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Active Writers</dt>
                            <dd>
                                <div class="text-lg font-semibold text-gray-900">{{ $activeWritersCount ?? 0 }}</div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-purple-50 px-5 py-3">
                <div class="text-sm">
                    <a href="{{ route('admin.writers.index') }}" class="font-medium text-purple-700 hover:text-purple-900">
                        View all writers
                        <span aria-hidden="true">&rarr;</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts & Analytics Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Monthly Orders Chart -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Monthly Orders</h3>
                <div class="flex items-center space-x-2">
                    <select id="orderChartYear" class="text-sm border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        <option value="2025">2025</option>
                        <option value="2024">2024</option>
                        <option value="2023">2023</option>
                    </select>
                </div>
            </div>
            <div class="h-80">
                <canvas id="monthlyOrdersChart"></canvas>
            </div>
        </div>

        <!-- Orders by Category Pie Chart -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Orders by Discipline</h3>
            <div class="h-80">
                <canvas id="categoryPieChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Recent Activity & Top Writers Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Top Writers -->
        <div class="lg:col-span-1 bg-white rounded-lg shadow">
            <div class="px-6 py-5 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Top Writers</h3>
            </div>
            <div class="p-6">
                <ul class="divide-y divide-gray-200">
                    @forelse($topWriters ?? [] as $writer)
                        <li class="py-4 flex items-center">
                            <div class="flex-shrink-0">
                                @if($writer->profile_picture)
                                    <img class="h-10 w-10 rounded-full object-cover" src="{{ asset($writer->profile_picture) }}" alt="{{ $writer->name }}">
                                @else
                                    <div class="h-10 w-10 rounded-full bg-primary-100 flex items-center justify-center text-primary-600 font-semibold">
                                        {{ strtoupper(substr($writer->name, 0, 1)) }}
                                    </div>
                                @endif
                            </div>
                            <div class="ml-3 flex-1">
                                <p class="text-sm font-medium text-gray-900">{{ $writer->name }}</p>
                                <div class="flex items-center">
                                    <div class="flex items-center">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= round($writer->rating ?? 0))
                                                <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                                </svg>
                                            @else
                                                <svg class="w-4 h-4 text-gray-300" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                                </svg>
                                            @endif
                                        @endfor
                                    </div>
                                    <span class="text-xs text-gray-500 ml-1">{{ $writer->completed_orders_count ?? 0 }} completed orders</span>
                                </div>
                            </div>
                            <div class="flex-shrink-0">
                                <a href="{{ route('admin.writers.show', $writer->id ?? 1) }}" class="text-primary-600 hover:text-primary-900">
                                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>
                            </div>
                        </li>
                    @empty
                        <li class="py-4 text-center text-gray-500">No writers found</li>
                    @endforelse
                </ul>
                <div class="mt-4 text-center">
                    <a href="{{ route('admin.writers.index') }}" class="text-sm font-medium text-primary-600 hover:text-primary-500">
                        View all writers
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Orders & Activity -->
        <div class="lg:col-span-2 bg-white rounded-lg shadow">
            <div class="px-6 py-5 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Recent Orders</h3>
            </div>
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
                                Writer
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Deadline
                            </th>
                            <th scope="col" class="relative px-6 py-3">
                                <span class="sr-only">View</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($recentOrders ?? [] as $order)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    #{{ $order->id ?? '123456' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ Str::limit($order->title ?? 'Sample Order Title', 30) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($order->writer ?? null)
                                        {{ $order->writer->name }}
                                    @else
                                        <span class="text-gray-400">Unassigned</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $status = $order->status ?? 'available';
                                        $statusClass = [
                                            'available' => 'bg-blue-100 text-blue-800',
                                            'confirmed' => 'bg-yellow-100 text-yellow-800',
                                            'in_progress' => 'bg-yellow-100 text-yellow-800',
                                            'done' => 'bg-green-100 text-green-800',
                                            'revision' => 'bg-red-100 text-red-800',
                                            'completed' => 'bg-green-100 text-green-800',
                                            'cancelled' => 'bg-gray-100 text-gray-800',
                                        ][$status] ?? 'bg-gray-100 text-gray-800';
                                    @endphp
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                                        {{ ucfirst($status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $order->deadline ?? now()->addDays(3)->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('admin.orders.show', $order->id ?? 1) }}" class="text-primary-600 hover:text-primary-900">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">No recent orders found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-gray-200 bg-gray-50 text-right">
                <a href="{{ route('admin.orders.index') }}" class="text-sm font-medium text-primary-600 hover:text-primary-500">
                    View all orders
                </a>
            </div>
        </div>
    </div>

    <!-- Pending Actions Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Pending Payments -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-5 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">Pending Payments</h3>
                <span class="px-3 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">{{ count($pendingPayments ?? []) }} pending</span>
            </div>
            <div class="p-6">
                @if(!empty($pendingPayments ?? []))
                    <ul class="divide-y divide-gray-200">
                        @foreach($pendingPayments as $payment)
                            <li class="py-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $payment->writer->name ?? 'John Doe' }}</p>
                                        <p class="text-xs text-gray-500">Requested on {{ $payment->created_at ?? now()->subDays(2)->format('M d, Y') }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-bold text-gray-900">${{ $payment->amount ?? '150.00' }}</p>
                                        <a href="{{ route('admin.payments.show', $payment->id ?? 1) }}" class="text-xs text-primary-600 hover:text-primary-900">Process payment</a>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="text-center py-4 text-gray-500">No pending payments</div>
                @endif
            </div>
        </div>

        <!-- Orders Requiring Attention -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-5 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">Orders Requiring Attention</h3>
                <span class="px-3 py-1 text-xs rounded-full bg-red-100 text-red-800">{{ count($attentionOrders ?? []) }} attention needed</span>
            </div>
            <div class="p-6">
                @if(!empty($attentionOrders ?? []))
                    <ul class="divide-y divide-gray-200">
                        @foreach($attentionOrders as $order)
                            <li class="py-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">Order #{{ $order->id ?? '789012' }}</p>
                                        <div class="flex items-center text-xs text-gray-500">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $order->status == 'revision' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                {{ ucfirst($order->status ?? 'revision') }}
                                            </span>
                                            <span class="ml-2">Deadline: {{ $order->deadline ?? now()->addDay()->format('M d, Y') }}</span>
                                        </div>
                                    </div>
                                    <a href="{{ route('admin.orders.show', $order->id ?? 1) }}" class="text-primary-600 hover:text-primary-900">
                                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="text-center py-4 text-gray-500">No orders requiring attention</div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
<script>
    // Monthly Orders Chart
    const monthlyOrdersCtx = document.getElementById('monthlyOrdersChart').getContext('2d');
    const monthlyOrdersChart = new Chart(monthlyOrdersCtx, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [
                {
                    label: 'Available',
                    backgroundColor: '#93c5fd',
                    data: [15, 20, 25, 22, 18, 24, 28, 30, 22, 25, 18, 20],
                    barPercentage: 0.6,
                    categoryPercentage: 0.7,
                },
                {
                    label: 'Completed',
                    backgroundColor: '#86efac',
                    data: [10, 15, 18, 14, 12, 19, 22, 25, 18, 21, 14, 16],
                    barPercentage: 0.6,
                    categoryPercentage: 0.7,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });

    // Category Pie Chart
    const categoryPieCtx = document.getElementById('categoryPieChart').getContext('2d');
    const categoryPieChart = new Chart(categoryPieCtx, {
        type: 'doughnut',
        data: {
            labels: ['Programming', 'Research Paper', 'Essay', 'Case Study', 'Technical Writing', 'Other'],
            datasets: [{
                data: [30, 25, 15, 10, 15, 5],
                backgroundColor: [
                    '#93c5fd', // blue-300
                    '#c4b5fd', // purple-300
                    '#fcd34d', // amber-300
                    '#86efac', // green-300
                    '#fdba74', // orange-300
                    '#d1d5db', // gray-300
                ],
                borderWidth: 1,
                borderColor: '#ffffff',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const total = context.dataset.data.reduce((acc, data) => acc + data, 0);
                            const percentage = Math.round((value / total) * 100);
                            return `${label}: ${percentage}% (${value})`;
                        }
                    }
                }
            },
            cutout: '60%'
        }
    });

    // Update chart when year selection changes
    document.getElementById('orderChartYear').addEventListener('change', function() {
        // In a real application, you would fetch data for the selected year
        // For this example, we'll just update with random data
        const year = this.value;
        
        // Generate random data for demonstration
        const newAvailableData = Array.from({length: 12}, () => Math.floor(Math.random() * 30) + 10);
        const newCompletedData = Array.from({length: 12}, () => Math.floor(Math.random() * 25) + 5);
        
        // Update chart data
        monthlyOrdersChart.data.datasets[0].data = newAvailableData;
        monthlyOrdersChart.data.datasets[1].data = newCompletedData;
        monthlyOrdersChart.update();
    });
</script>
@endpush