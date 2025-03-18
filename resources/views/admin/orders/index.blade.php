@extends('admin.app')


@section('page-title', request('status') == 'in_progress' ? 'In Progress Orders' : 
                      (request('status') == 'available' ? 'Available Orders' : 
                      (request('status') == 'revision' ? 'Revision Orders' : 
                      (request('status') == 'completed' ? 'Completed Orders' : 
                      (request('status') == 'dispute' ? 'Disputed Orders' : 
                      (request('status') == 'cancelled' ? 'Cancelled Orders' : 'All Orders'))))))

@section('content')
   <!-- Stats Cards -->
   <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-7 gap-4 mb-6">
       <div class="bg-white rounded-lg shadow-sm overflow-hidden {{ request('status') == 'available' ? 'ring-2 ring-primary-500' : '' }}">
           <div class="p-5">
               <div class="flex items-center">
                   <div class="flex-shrink-0 bg-blue-100 rounded-full p-3">
                       <i class="fas fa-clipboard-list text-blue-600 text-xl"></i>
                   </div>
                   <div class="ml-5 w-0 flex-1">
                       <dl>
                           <dt class="text-sm font-medium text-gray-500 truncate">Available</dt>
                           <dd>
                               <div class="text-lg font-semibold text-gray-900">{{ $orderCounts['available'] ?? 0 }}</div>
                           </dd>
                       </dl>
                   </div>
               </div>
           </div>
           <a href="{{ route('admin.orders.index', ['status' => 'available']) }}" class="bg-blue-50 text-blue-700 hover:bg-blue-100 block px-5 py-3 text-center text-sm font-medium transition-colors duration-200">
               View Available
           </a>
       </div>
       
       <div class="bg-white rounded-lg shadow-sm overflow-hidden {{ request('status') == 'in_progress' ? 'ring-2 ring-primary-500' : '' }}">
           <div class="p-5">
               <div class="flex items-center">
                   <div class="flex-shrink-0 bg-orange-100 rounded-full p-3">
                       <i class="fas fa-clock text-orange-600 text-xl"></i>
                   </div>
                   <div class="ml-5 w-0 flex-1">
                       <dl>
                           <dt class="text-sm font-medium text-gray-500 truncate">In Progress</dt>
                           <dd>
                               <div class="text-lg font-semibold text-gray-900">{{ $orderCounts['in_progress'] ?? 0 }}</div>
                           </dd>
                       </dl>
                   </div>
               </div>
           </div>
           <a href="{{ route('admin.orders.index', ['status' => 'in_progress']) }}" class="bg-orange-50 text-orange-700 hover:bg-orange-100 block px-5 py-3 text-center text-sm font-medium transition-colors duration-200">
               View In Progress
           </a>
       </div>
       
       <div class="bg-white rounded-lg shadow-sm overflow-hidden {{ request('status') == 'revision' ? 'ring-2 ring-primary-500' : '' }}">
           <div class="p-5">
               <div class="flex items-center">
                   <div class="flex-shrink-0 bg-red-100 rounded-full p-3">
                       <i class="fas fa-sync-alt text-red-600 text-xl"></i>
                   </div>
                   <div class="ml-5 w-0 flex-1">
                       <dl>
                           <dt class="text-sm font-medium text-gray-500 truncate">Revision</dt>
                           <dd>
                               <div class="text-lg font-semibold text-gray-900">{{ $orderCounts['revision'] ?? 0 }}</div>
                           </dd>
                       </dl>
                   </div>
               </div>
           </div>
           <a href="{{ route('admin.orders.index', ['status' => 'revision']) }}" class="bg-red-50 text-red-700 hover:bg-red-100 block px-5 py-3 text-center text-sm font-medium transition-colors duration-200">
               View Revisions
           </a>
       </div>
       
       <div class="bg-white rounded-lg shadow-sm overflow-hidden {{ request('status') == 'completed' ? 'ring-2 ring-primary-500' : '' }}">
           <div class="p-5">
               <div class="flex items-center">
                   <div class="flex-shrink-0 bg-green-100 rounded-full p-3">
                       <i class="fas fa-check-circle text-green-600 text-xl"></i>
                   </div>
                   <div class="ml-5 w-0 flex-1">
                       <dl>
                           <dt class="text-sm font-medium text-gray-500 truncate">Completed</dt>
                           <dd>
                               <div class="text-lg font-semibold text-gray-900">{{ $orderCounts['completed'] ?? 0 }}</div>
                           </dd>
                       </dl>
                   </div>
               </div>
           </div>
           <a href="{{ route('admin.orders.index', ['status' => 'completed']) }}" class="bg-green-50 text-green-700 hover:bg-green-100 block px-5 py-3 text-center text-sm font-medium transition-colors duration-200">
               View Completed
           </a>
       </div>
       
       <div class="bg-white rounded-lg shadow-sm overflow-hidden {{ request('status') == 'dispute' ? 'ring-2 ring-primary-500' : '' }}">
           <div class="p-5">
               <div class="flex items-center">
                   <div class="flex-shrink-0 bg-yellow-100 rounded-full p-3">
                       <i class="fas fa-exclamation-triangle text-yellow-600 text-xl"></i>
                   </div>
                   <div class="ml-5 w-0 flex-1">
                       <dl>
                           <dt class="text-sm font-medium text-gray-500 truncate">Disputes</dt>
                           <dd>
                               <div class="text-lg font-semibold text-gray-900">{{ $orderCounts['dispute'] ?? 0 }}</div>
                           </dd>
                       </dl>
                   </div>
               </div>
           </div>
           <a href="{{ route('admin.orders.index', ['status' => 'dispute']) }}" class="bg-yellow-50 text-yellow-700 hover:bg-yellow-100 block px-5 py-3 text-center text-sm font-medium transition-colors duration-200">
               View Disputes
           </a>
       </div>
       
       <div class="bg-white rounded-lg shadow-sm overflow-hidden {{ request('status') == 'cancelled' ? 'ring-2 ring-primary-500' : '' }}">
           <div class="p-5">
               <div class="flex items-center">
                   <div class="flex-shrink-0 bg-gray-100 rounded-full p-3">
                       <i class="fas fa-ban text-gray-600 text-xl"></i>
                   </div>
                   <div class="ml-5 w-0 flex-1">
                       <dl>
                           <dt class="text-sm font-medium text-gray-500 truncate">Cancelled</dt>
                           <dd>
                               <div class="text-lg font-semibold text-gray-900">{{ $orderCounts['cancelled'] ?? 0 }}</div>
                           </dd>
                       </dl>
                   </div>
               </div>
           </div>
           <a href="{{ route('admin.orders.index', ['status' => 'cancelled']) }}" class="bg-gray-50 text-gray-700 hover:bg-gray-100 block px-5 py-3 text-center text-sm font-medium transition-colors duration-200">
               View Cancelled
           </a>
       </div>
       
       <div class="bg-white rounded-lg shadow-sm overflow-hidden {{ request('status') == 'all' || !request('status') ? 'ring-2 ring-primary-500' : '' }}">
           <div class="p-5">
               <div class="flex items-center">
                   <div class="flex-shrink-0 bg-purple-100 rounded-full p-3">
                       <i class="fas fa-list-alt text-purple-600 text-xl"></i>
                   </div>
                   <div class="ml-5 w-0 flex-1">
                       <dl>
                           <dt class="text-sm font-medium text-gray-500 truncate">All Orders</dt>
                           <dd>
                               <div class="text-lg font-semibold text-gray-900">{{ $orderCounts['all'] ?? 0 }}</div>
                           </dd>
                       </dl>
                   </div>
               </div>
           </div>
           <a href="{{ route('admin.orders.index') }}" class="bg-purple-50 text-purple-700 hover:bg-purple-100 block px-5 py-3 text-center text-sm font-medium transition-colors duration-200">
               View All
           </a>
       </div>
   </div>

   <!-- Filter & Search Section -->
   <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
       <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
           <div class="flex-1">
               <form action="{{ route('admin.orders.index') }}" method="GET" class="flex flex-col sm:flex-row gap-4">
                   <input type="hidden" name="status" value="{{ request('status') }}">
                   
                   <div class="flex-1">
                       <label for="search" class="sr-only">Search</label>
                       <div class="relative">
                           <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                               <i class="fas fa-search text-gray-400"></i>
                           </div>
                           <input type="text" id="search" name="search" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm" placeholder="Search by ID or title..." value="{{ request('search') }}">
                       </div>
                   </div>
                   
                   <div class="sm:w-40">
                       <label for="sort" class="sr-only">Sort By</label>
                       <select id="sort" name="sort" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                           <option value="created_desc" {{ request('sort') == 'created_desc' ? 'selected' : '' }}>Newest First</option>
                           <option value="created_asc" {{ request('sort') == 'created_asc' ? 'selected' : '' }}>Oldest First</option>
                           <option value="deadline_asc" {{ request('sort') == 'deadline_asc' ? 'selected' : '' }}>Deadline (Urgent)</option>
                           <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Price (High-Low)</option>
                           <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Price (Low-High)</option>
                       </select>
                   </div>
                   
                   <div class="sm:w-40">
                       <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                           <i class="fas fa-filter mr-2"></i> Filter
                       </button>
                   </div>
               </form>
           </div>
           
           <a href="{{ route('admin.orders.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
               <i class="fas fa-plus mr-2"></i> Create Order
           </a>
       </div>
   </div>

   <!-- Orders List -->
   <div class="bg-white shadow-sm rounded-lg overflow-hidden">
       <div class="p-6 border-b border-gray-200">
           <h3 class="text-lg font-medium text-gray-900">
               {{ request('status') == 'in_progress' ? 'In Progress Orders' : 
                  (request('status') == 'available' ? 'Available Orders' : 
                  (request('status') == 'revision' ? 'Revision Orders' : 
                  (request('status') == 'completed' ? 'Completed Orders' :
                  (request('status') == 'dispute' ? 'Disputed Orders' :
                  (request('status') == 'cancelled' ? 'Cancelled Orders' : 'All Orders'))))) }}
               @if(request('search'))
                   <span class="text-sm text-gray-500 ml-2">Search results for "{{ request('search') }}"</span>
               @endif
           </h3>
       </div>
       
       @if(count($orders) > 0)
           <div class="overflow-x-auto">
               <table class="min-w-full divide-y divide-gray-200">
                   <thead class="bg-gray-50">
                       <tr>
                           <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                               Order ID
                           </th>
                           <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                               Title & Details
                           </th>
                           <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                               Writer
                           </th>
                           <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                               Deadline
                           </th>
                           <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                               Price
                           </th>
                           <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                               Status
                           </th>
                           <th scope="col" class="relative px-6 py-3">
                               <span class="sr-only">Actions</span>
                           </th>
                       </tr>
                   </thead>
                   <tbody class="bg-white divide-y divide-gray-200">
                       @foreach($orders as $order)
                           <tr>
                               <td class="px-6 py-4 whitespace-nowrap">
                                   <div class="text-sm font-medium text-gray-900">#{{ $order->id }}</div>
                                   <div class="text-xs text-gray-500">{{ $order->created_at->format('M d, Y') }}</div>
                               </td>
                               <td class="px-6 py-4">
                                   <div class="text-sm font-medium text-gray-900">{{ Str::limit($order->title, 40) }}</div>
                                   <div class="text-xs text-gray-500">
                                       <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 mr-2">
                                           {{ $order->type_of_service }}
                                       </span>
                                       <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 mr-2">
                                           {{ $order->discipline }}
                                       </span>
                                       <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                           {{ $order->task_size }} pages
                                       </span>
                                   </div>
                               </td>
                               <td class="px-6 py-4 whitespace-nowrap">
                                   @if($order->writer)
                                       <div class="flex items-center">
                                           <div class="flex-shrink-0 h-8 w-8">
                                               @if($order->writer->profile_picture)
                                                   <img class="h-8 w-8 rounded-full object-cover" src="{{ asset($order->writer->profile_picture) }}" alt="{{ $order->writer->name }}">
                                               @else
                                                   <div class="h-8 w-8 rounded-full bg-primary-100 flex items-center justify-center">
                                                       <span class="text-primary-600 font-medium text-sm">{{ strtoupper(substr($order->writer->name, 0, 1)) }}</span>
                                                   </div>
                                               @endif
                                           </div>
                                           <div class="ml-3">
                                               <div class="text-sm font-medium text-gray-900">{{ $order->writer->name }}</div>
                                               <div class="flex items-center text-xs text-gray-500">
                                                   @for($i = 1; $i <= 5; $i++)
                                                       @if($i <= round($order->writer->rating ?? 0))
                                                           <i class="fas fa-star text-yellow-400 text-xs"></i>
                                                       @else
                                                           <i class="far fa-star text-yellow-400 text-xs"></i>
                                                       @endif
                                                   @endfor
                                               </div>
                                           </div>
                                       </div>
                                   @else
                                       <span class="text-gray-500 text-xs italic">Unassigned</span>
                                   @endif
                               </td>
                               <td class="px-6 py-4 whitespace-nowrap">
                                   <div class="text-sm text-gray-900">{{ $order->deadline->format('M d, Y') }}</div>
                                   <div class="text-xs text-gray-500">{{ $order->deadline->format('h:i A') }}</div>
                                   @if($order->deadline->isPast())
                                       <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                           Overdue
                                       </span>
                                   @elseif($order->deadline->diffInHours() < 24)
                                       <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                           Urgent
                                       </span>
                                   @endif
                               </td>
                               <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                   ${{ number_format($order->price, 2) }}
                               </td>
                               <td class="px-6 py-4 whitespace-nowrap">
                                   @if($order->status == 'available')
                                       <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                           Available
                                       </span>
                                   @elseif(in_array($order->status, ['confirmed', 'in_progress', 'done', 'delivered']))
                                       <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800">
                                           {{ ucfirst($order->status) }}
                                       </span>
                                   @elseif($order->status == 'revision')
                                       <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                           Revision
                                       </span>
                                   @elseif(in_array($order->status, ['completed', 'paid', 'finished']))
                                       <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                           {{ ucfirst($order->status) }}
                                       </span>
                                   @elseif($order->status == 'dispute')
                                       <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                           Dispute
                                       </span>
                                   @elseif($order->status == 'cancelled')
                                       <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                           Cancelled
                                       </span>
                                   @else
                                       <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                           {{ ucfirst($order->status) }}
                                       </span>
                                   @endif
                               </td>
                               <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                   <a href="{{ route('admin.orders.show', $order->id) }}" class="text-primary-600 hover:text-primary-900 mr-3">
                                       <i class="fas fa-eye"></i>
                                   </a>
                                   <a href="{{ route('admin.orders.edit', $order->id) }}" class="text-indigo-600 hover:text-indigo-900">
                                       <i class="fas fa-edit"></i>
                                   </a>
                               </td>
                           </tr>
                       @endforeach
                   </tbody>
               </table>
           </div>
           
           <!-- Pagination -->
           <div class="px-6 py-4 border-t border-gray-200">
               {{ $orders->appends(request()->query())->links() }}
           </div>
       @else
           <div class="p-6 text-center">
               <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-gray-100">
                   <i class="fas fa-file-alt text-gray-400"></i>
               </div>
               <h3 class="mt-2 text-sm font-medium text-gray-900">No orders found</h3>
               <p class="mt-1 text-sm text-gray-500">
                   @if(request('search'))
                       No orders match your search criteria. Try different keywords or filters.
                   @elseif(request('status') == 'available')
                       There are no available orders at the moment.
                   @elseif(request('status') == 'in_progress')
                       There are no orders in progress at the moment.
                   @elseif(request('status') == 'revision')
                       There are no orders requiring revision at the moment.
                   @elseif(request('status') == 'completed')
                       There are no completed orders at the moment.
                   @elseif(request('status') == 'dispute')
                       There are no disputed orders at the moment.
                   @elseif(request('status') == 'cancelled')
                       There are no cancelled orders at the moment.
                   @else
                       There are no orders in the system yet.
                   @endif
               </p>
               <div class="mt-6">
                   <a href="{{ route('admin.orders.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                       <i class="fas fa-plus mr-2"></i> Create New Order
                   </a>
               </div>
           </div>
       @endif
   </div>
@endsection

@push('scripts')
<script>
   // Auto-submit form when sort selection changes
   document.addEventListener('DOMContentLoaded', function() {
       document.getElementById('sort').addEventListener('change', function() {
           this.form.submit();
       });
   });
</script>
@endpush