@extends('admin.app')

@section('title', 'Order Bids')

@section('page-title', 'Order Bids - #' . $order->id)

@section('content')
<div class="bg-white rounded-lg shadow-sm overflow-hidden mb-6">
    <div class="p-6 border-b border-gray-200">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Order Details</h2>
            <a href="{{ route('admin.bids') }}" class="text-primary-600 hover:text-primary-900">
                <i class="fas fa-arrow-left mr-1"></i> Back to Available Orders
            </a>
        </div>
    </div>
    
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="text-lg font-medium text-gray-900">Basic Information</h3>
                <div class="mt-4 space-y-4">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Order ID:</span>
                        <span class="text-gray-800 font-medium">#{{ $order->id }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Title:</span>
                        <span class="text-gray-800">{{ $order->title }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Type of Service:</span>
                        <span class="text-gray-800">{{ $order->type_of_service }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Discipline:</span>
                        <span class="text-gray-800">{{ $order->discipline }}</span>
                    </div>
                </div>
            </div>
            
            <div>
                <h3 class="text-lg font-medium text-gray-900">Timing & Pricing</h3>
                <div class="mt-4 space-y-4">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Price:</span>
                        <span class="text-gray-800 font-medium">${{ number_format($order->price, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Task Size:</span>
                        <span class="text-gray-800">{{ $order->task_size ?? 'N/A' }} pages</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Deadline:</span>
                        <span class="text-gray-800">{{ $order->deadline->format('M d, Y, g:i a') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Time Remaining:</span>
                        <span class="{{ now()->gt($order->deadline) ? 'text-red-600 font-bold' : (now()->diffInHours($order->deadline) < 24 ? 'text-yellow-600 font-medium' : 'text-green-600') }}">
                            {{ now()->gt($order->deadline) ? 'Expired' : $order->deadline->diffForHumans() }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-8">
            <h3 class="text-lg font-medium text-gray-900">Instructions</h3>
            <div class="mt-4 p-4 bg-gray-50 rounded-md border border-gray-200">
                <p class="text-gray-800 whitespace-pre-line">{{ $order->instructions }}</p>
            </div>
        </div>
        
        @if($order->files->count() > 0)
        <div class="mt-8">
            <h3 class="text-lg font-medium text-gray-900">Files</h3>
            <div class="mt-4 space-y-2">
                @foreach($order->files as $file)
                <div class="flex items-center p-2 bg-gray-50 rounded-md border border-gray-200">
                    <i class="fas fa-file text-gray-500 mr-2"></i>
                    <span class="text-gray-800">{{ $file->name }}</span>
                    <span class="text-xs text-gray-500 ml-2">({{ number_format($file->size / 1024, 2) }} KB)</span>
                    <a href="{{ route('files.download', $file->id) }}" class="ml-auto text-primary-600 hover:text-primary-900">
                        <i class="fas fa-download"></i>
                    </a>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm overflow-hidden mb-6">
    <div class="p-6 border-b border-gray-200">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Writer Bids ({{ $order->bids->count() }})</h2>
            <div class="flex space-x-2">
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                        <i class="fas fa-filter mr-1"></i> Filter Writers
                    </button>
                    <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-64 bg-white rounded-md shadow-lg z-10">
                        <div class="p-2">
                            <input type="text" id="writerSearch" placeholder="Search by name or ID..." class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    @if($order->bids->isEmpty())
    <div class="p-10 text-center">
        <p class="text-gray-500">No bids have been placed for this order yet.</p>
    </div>
    @else
    <div id="writersContainer">
        @foreach($order->bids as $bid)
        <div class="writer-item p-6 border-b border-gray-200 hover:bg-gray-50">
            <div class="flex flex-col md:flex-row md:items-center">
                <div class="flex-1">
                    <div class="flex items-center">
                        <div class="h-10 w-10 rounded-full bg-primary-100 flex items-center justify-center text-primary-600 font-semibold mr-3">
                            {{ $bid->user && $bid->user->name ? strtoupper(substr($bid->user->name, 0, 1)) : '?' }}
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">{{ $bid->user->name ?? 'Unknown Writer' }}</h3>
                            <p class="text-sm text-gray-500">Writer ID: {{ $bid->user && $bid->user->writerProfile ? $bid->user->writerProfile->writer_id : '#' . ($bid->user_id ?? 'N/A') }}</p>
                        </div>
                    </div>
                    
                    <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                        <div>
                            <span class="text-sm text-gray-500">Completed Orders</span>
                            <p class="text-lg font-medium text-gray-900">
                                @if($bid->user && $bid->user->writerProfile)
                                    {{ $bid->user->writerProfile->jobs_completed ?? 0 }}
                                @else
                                    {{ $bid->user ? $bid->user->ordersAsWriter()->whereIn('status', ['completed', 'paid', 'finished'])->count() : 0 }}
                                @endif
                            </p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Rating</span>
                            <p class="text-lg font-medium text-gray-900">
                                @if($bid->user && $bid->user->writerProfile && isset($bid->user->writerProfile->rating))
                                    <span class="text-yellow-500">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= round($bid->user->writerProfile->rating))
                                                <i class="fas fa-star"></i>
                                            @else
                                                <i class="far fa-star"></i>
                                            @endif
                                        @endfor
                                    </span>
                                    <span class="text-gray-700 ml-1">{{ number_format($bid->user->writerProfile->rating, 1) }}</span>
                                @else
                                    <span class="text-gray-400">No ratings</span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Bid Date</span>
                            <p class="text-lg font-medium text-gray-900">{{ $bid->created_at->format('M d, Y') }}</p>
                        </div>
                    </div>
                    
                    @if($bid->cover_letter)
                    <div class="mt-4 p-3 bg-gray-50 rounded-md border border-gray-200">
                        <p class="text-sm text-gray-700">{{ $bid->cover_letter }}</p>
                    </div>
                    @endif
                </div>
                
                <div class="mt-6 md:mt-0 md:ml-6 flex flex-col items-end">
                    @if($bid->user)
                    <a href="{{ route('admin.writers.show', $bid->user_id) }}" class="text-primary-600 hover:text-primary-900 mb-3">
                        <i class="fas fa-user mr-1"></i> View Profile
                    </a>
                    <a href="{{ route('admin.bids.assign', [$order->id, $bid->user_id]) }}" 
                       onclick="return confirm('Are you sure you want to assign this order to {{ $bid->user->name ?? 'this writer' }}?')"
                       class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        <i class="fas fa-check mr-1"></i> Assign Order
                    </a>
                    @else
                    <span class="text-gray-400">Writer account unavailable</span>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <div class="p-6 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-800">Assign to Another Writer</h2>
    </div>
    
    <div class="p-6">
        <div class="mb-4">
            <label for="writerDirectSearch" class="block text-sm font-medium text-gray-700">Search Writers</label>
            <div class="mt-1 flex">
                <input type="text" id="writerDirectSearch" placeholder="Search by name, email or ID..." 
                    class="flex-1 rounded-l-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50">
                <button id="searchWritersBtn" class="px-4 py-2 bg-primary-500 text-white rounded-r-md hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>
        
        <div id="writersSearchResults" class="mt-4 space-y-4">
            <!-- Results will be populated here -->
        </div>
    </div>
</div>

<!-- First toast notification (Alpine.js version) -->
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

<!-- Second toast notification (Vanilla JS version) -->
@if(session('toast'))
<div id="toast-notification" 
     class="fixed top-4 right-4 z-50 bg-green-50 border-l-4 border-green-500 p-4 rounded shadow-lg flex items-start max-w-sm transform transition-transform duration-300 ease-in-out translate-x-full">
    <div class="text-green-500 mr-3">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
    </div>
    <div>
        <p class="font-medium text-green-800">{{ session('toast.title') }}</p>
        <p class="text-sm text-green-700 mt-1">{{ session('toast.message') }}</p>
    </div>
    <button onclick="closeToast()" class="ml-auto text-green-500 hover:text-green-700">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
    </button>
</div>

<script>
    // Show toast notification
    function showToast() {
        const toast = document.getElementById('toast-notification');
        if (toast) {
            // First make sure it's visible
            toast.classList.remove('hidden');
            
            // Wait a tiny bit to allow a DOM refresh, then animate in
            setTimeout(() => {
                toast.classList.remove('translate-x-full');
            }, 10);
            
            // Auto hide after 5 seconds
            setTimeout(() => {
                closeToast();
            }, 5000);
        }
    }
    
    // Hide toast notification
    function closeToast() {
        const toast = document.getElementById('toast-notification');
        if (toast) {
            toast.classList.add('translate-x-full');
            
            // Remove from DOM after animation completes
            setTimeout(() => {
                toast.classList.add('hidden');
            }, 300);
        }
    }
    
    // Show toast when page loads if session has toast data
    @if(session('toast'))
    document.addEventListener('DOMContentLoaded', function() {
        showToast();
    });
    @endif
</script>
@endif

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const writerSearch = document.getElementById('writerSearch');
        const writerDirectSearch = document.getElementById('writerDirectSearch');
        const searchWritersBtn = document.getElementById('searchWritersBtn');
        const writersContainer = document.getElementById('writersContainer');
        const writersSearchResults = document.getElementById('writersSearchResults');
        
        // Filter bidding writers
        if (writerSearch) {
            writerSearch.addEventListener('input', function() {
                const searchValue = this.value.toLowerCase();
                const writerItems = document.querySelectorAll('.writer-item');
                
                writerItems.forEach(item => {
                    const writerName = item.querySelector('h3').textContent.toLowerCase();
                    const writerId = item.querySelector('p').textContent.toLowerCase();
                    
                    if (writerName.includes(searchValue) || writerId.includes(searchValue)) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        }
        
        // Search for other writers to assign
        if (searchWritersBtn) {
            searchWritersBtn.addEventListener('click', searchWriters);
        }
        
        if (writerDirectSearch) {
            writerDirectSearch.addEventListener('keyup', function(event) {
                if (event.key === 'Enter') {
                    searchWriters();
                }
            });
        }
        
        function searchWriters() {
            const searchValue = writerDirectSearch.value.trim();
            if (searchValue.length < 1) return;
            
            writersSearchResults.innerHTML = `
                <div class="text-center py-4">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-500"></div>
                    <p class="mt-2 text-gray-600">Searching writers...</p>
                </div>
            `;
            
            fetch(`/admin/bids/${{{ $order->id }}}/filter-writers?search=${searchValue}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.writers.data.length === 0) {
                    writersSearchResults.innerHTML = `
                        <div class="text-center py-4">
                            <p class="text-gray-600">No writers found matching "${searchValue}"</p>
                        </div>
                    `;
                    return;
                }
                
                let resultsHtml = '';
                
                data.writers.data.forEach(writer => {
                    const isBidder = data.bidders.includes(writer.id);
                    const assignUrl = `{{ route('admin.bids.assign', [$order->id, '']) }}/${writer.id}`;
                    const profileUrl = `{{ route('admin.writers.show', '') }}/${writer.id}`;
                    const writerProfile = writer.writer_profile;
                    
                    resultsHtml += `
                        <div class="p-4 border rounded-md hover:bg-gray-50">
                            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 rounded-full bg-primary-100 flex items-center justify-center text-primary-600 font-semibold mr-3">
                                        ${writer.name ? writer.name.charAt(0).toUpperCase() : '?'}
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-medium text-gray-900">${writer.name || 'Unknown Writer'}</h3>
                                        <p class="text-sm text-gray-500">Writer ID: ${writerProfile ? writerProfile.writer_id : '#' + writer.id}</p>
                                    </div>
                                </div>
                                
                                <div class="mt-4 md:mt-0 grid grid-cols-2 gap-4">
                                    <div>
                                        <span class="text-sm text-gray-500">Completed</span>
                                        <p class="text-lg font-medium text-gray-900">${writerProfile ? writerProfile.jobs_completed || 0 : 0}</p>
                                    </div>
                                    <div>
                                        <span class="text-sm text-gray-500">Rating</span>
                                        <p class="text-lg font-medium text-gray-900">
                                            ${writerProfile && writerProfile.rating ? 
                                                `<span class="text-yellow-500">${'★'.repeat(Math.round(writerProfile.rating))}${'☆'.repeat(5-Math.round(writerProfile.rating))}</span> ${writerProfile.rating.toFixed(1)}` : 
                                                '<span class="text-gray-400">No ratings</span>'}
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="mt-4 md:mt-0 flex space-x-2">
                                    <a href="${profileUrl}" class="px-3 py-1 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 focus:outline-none">
                                        <i class="fas fa-user mr-1"></i> Profile
                                    </a>
                                    ${isBidder ? 
                                        `<span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-md">Has Bid</span>` : 
                                        `<a href="${assignUrl}" onclick="return confirm('Are you sure you want to assign this order to ${writer.name || 'this writer'}?')" class="px-3 py-1 bg-green-500 text-white rounded-md hover:bg-green-600 focus:outline-none">
                                            <i class="fas fa-check mr-1"></i> Assign
                                        </a>`
                                    }
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                if (data.writers.last_page > 1) {
                    resultsHtml += `
                        <div class="flex justify-center mt-4">
                            <p class="text-gray-500">Showing ${data.writers.from} to ${data.writers.to} of ${data.writers.total} writers</p>
                        </div>
                    `;
                }
                
                writersSearchResults.innerHTML = resultsHtml;
            })
            .catch(error => {
                console.error('Error:', error);
                writersSearchResults.innerHTML = `
                    <div class="text-center py-4">
                        <p class="text-red-600">Error loading writers. Please try again.</p>
                    </div>
                `;
            });
        }
    });
</script>
@endpush