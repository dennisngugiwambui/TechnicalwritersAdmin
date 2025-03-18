@extends('admin.app')


@section('content')
    <!-- Filter & Search Section -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="flex-1">
                <form action="{{ route('admin.writers.index') }}" method="GET" class="flex flex-col sm:flex-row gap-4">
                    <div class="flex-1">
                        <label for="search" class="sr-only">Search</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <input type="text" id="search" name="search" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm" placeholder="Search by name, email or ID..." value="{{ request('search') }}">
                        </div>
                    </div>
                    
                    <div class="sm:w-40">
                        <select name="status" id="status" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                            <option value="banned" {{ request('status') == 'banned' ? 'selected' : '' }}>Banned</option>
                        </select>
                    </div>
                    
                    <div class="sm:w-52">
                        <select name="verification" id="verification" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                            <option value="">All Verification Status</option>
                            <option value="verified" {{ request('verification') == 'verified' ? 'selected' : '' }}>Verified</option>
                            <option value="pending" {{ request('verification') == 'pending' ? 'selected' : '' }}>Pending Verification</option>
                            <option value="failed" {{ request('verification') == 'failed' ? 'selected' : '' }}>Failed Verification</option>
                        </select>
                    </div>
                    
                    <div class="sm:w-40">
                        <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                            <i class="fas fa-filter mr-2"></i> Filter
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="flex items-center space-x-2">
                <span class="text-sm text-gray-500">View:</span>
                <div class="flex border border-gray-300 rounded-md overflow-hidden">
                    <a href="{{ request()->fullUrlWithQuery(['view' => 'grid']) }}" class="px-3 py-1.5 text-sm {{ request('view', 'grid') == 'grid' ? 'bg-primary-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}">
                        <i class="fas fa-th-large"></i>
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['view' => 'list']) }}" class="px-3 py-1.5 text-sm {{ request('view') == 'list' ? 'bg-primary-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}">
                        <i class="fas fa-list"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Writers Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-user-check text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Active Writers</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['active'] ?? 0 }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <i class="fas fa-user-clock text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Pending Verification</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['pending'] ?? 0 }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-red-100 text-red-600">
                    <i class="fas fa-user-slash text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Suspended Writers</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['suspended'] ?? 0 }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-clock text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Avg. Rating</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($stats['avg_rating'] ?? 0, 1) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Writers List -->
    @if(count($writers) > 0)
        @if(request('view') == 'list')
            <!-- List View -->
            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Writer</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID & Contact</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Performance</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($writers as $writer)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                @if($writer->profile_picture)
                                                    <img class="h-10 w-10 rounded-full object-cover" src="{{ asset($writer->profile_picture) }}" alt="{{ $writer->name }}">
                                                @else
                                                    <div class="h-10 w-10 rounded-full bg-primary-100 flex items-center justify-center">
                                                        <span class="text-primary-600 font-medium text-sm">{{ strtoupper(substr($writer->name, 0, 1)) }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $writer->name }}</div>
                                                <div class="text-sm text-gray-500">{{ $writer->writerProfile->specialization ?? 'No specialization' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $writer->writerProfile->writer_id ?? 'N/A' }}</div>
                                        <div class="text-sm text-gray-500">{{ $writer->email }}</div>
                                        <div class="text-sm text-gray-500">{{ $writer->phone }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center mb-1">
                                            <div class="mr-2 flex">
                                                @for($i = 1; $i <= 5; $i++)
                                                    @if($i <= round($writer->rating ?? 0))
                                                        <i class="fas fa-star text-yellow-400 text-xs"></i>
                                                    @else
                                                        <i class="far fa-star text-yellow-400 text-xs"></i>
                                                    @endif
                                                @endfor
                                            </div>
                                            <span class="text-sm text-gray-500">{{ number_format($writer->rating ?? 0, 1) }}</span>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $writer->writerProfile->jobs_completed ?? 0 }} orders completed
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            ${{ number_format($writer->writerProfile->earnings ?? 0, 2) }} earned
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="mb-2">
                                            @if($writer->status == 'active')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                            @elseif($writer->status == 'pending')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                            @elseif($writer->status == 'suspended' || $writer->is_suspended == 'yes')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Suspended</span>
                                            @elseif($writer->status == 'banned')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Banned</span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">{{ ucfirst($writer->status) }}</span>
                                            @endif
                                        </div>
                                        <div>
                                            @if($writer->writerProfile && $writer->writerProfile->id_verification_status == 'verified')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                    <i class="fas fa-check-circle mr-1"></i> Verified
                                                </span>
                                            @elseif($writer->writerProfile && $writer->writerProfile->id_verification_status == 'pending')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                    <i class="fas fa-clock mr-1"></i> Pending Verification
                                                </span>
                                            @elseif($writer->writerProfile && $writer->writerProfile->id_verification_status == 'rejected')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                    <i class="fas fa-times-circle mr-1"></i> Verification Failed
                                                </span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                    <i class="fas fa-question-circle mr-1"></i> Not Verified
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end space-x-2">
                                            <a href="{{ route('admin.writers.show', $writer->id) }}" class="text-primary-600 hover:text-primary-900">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            @if($writer->status != 'suspended' && $writer->is_suspended != 'yes')
                                                <form action="{{ route('admin.writers.suspend', $writer->id) }}" method="POST" class="inline-block">
                                                    @csrf
                                                    <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to suspend this writer?')">
                                                        <i class="fas fa-ban"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <form action="{{ route('admin.writers.activate', $writer->id) }}" method="POST" class="inline-block">
                                                    @csrf
                                                    <button type="submit" class="text-green-600 hover:text-green-900" onclick="return confirm('Are you sure you want to activate this writer?')">
                                                        <i class="fas fa-check-circle"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            
                                            @if($writer->writerProfile && $writer->writerProfile->id_verification_status == 'pending')
                                                <form action="{{ route('admin.writers.verify', $writer->id) }}" method="POST" class="inline-block">
                                                    @csrf
                                                    <button type="submit" class="text-blue-600 hover:text-blue-900" onclick="return confirm('Are you sure you want to verify this writer?')">
                                                        <i class="fas fa-user-check"></i>
                                                    </button>
                                                </form>
                                                
                                                <form action="{{ route('admin.writers.reject', $writer->id) }}" method="POST" class="inline-block">
                                                    @csrf
                                                    <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to reject this writer\'s verification?')">
                                                        <i class="fas fa-user-times"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <!-- Grid View -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($writers as $writer)
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-14 w-14">
                                    @if($writer->profile_picture)
                                        <img class="h-14 w-14 rounded-full object-cover" src="{{ asset($writer->profile_picture) }}" alt="{{ $writer->name }}">
                                    @else
                                        <div class="h-14 w-14 rounded-full bg-primary-100 flex items-center justify-center">
                                            <span class="text-primary-600 font-medium text-lg">{{ strtoupper(substr($writer->name, 0, 1)) }}</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="ml-4 flex-1 min-w-0">
                                    <div class="text-sm font-medium text-gray-900 truncate">{{ $writer->name }}</div>
                                    <div class="text-sm text-gray-500 truncate">{{ $writer->writerProfile->writer_id ?? 'ID: N/A' }}</div>
                                    <div class="mt-1 flex">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= round($writer->rating ?? 0))
                                                <i class="fas fa-star text-yellow-400 text-xs"></i>
                                            @else
                                                <i class="far fa-star text-yellow-400 text-xs"></i>
                                            @endif
                                        @endfor
                                        <span class="ml-1 text-xs text-gray-500">{{ number_format($writer->rating ?? 0, 1) }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-4 grid grid-cols-2 gap-4">
                                <div>
                                    <span class="block text-xs text-gray-500">Completed</span>
                                    <span class="block text-sm font-medium text-gray-900">{{ $writer->writerProfile->jobs_completed ?? 0 }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs text-gray-500">Earnings</span>
                                    <span class="block text-sm font-medium text-gray-900">${{ number_format($writer->writerProfile->earnings ?? 0, 2) }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs text-gray-500">Status</span>
                                    <span class="block">
                                        @if($writer->status == 'active')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                        @elseif($writer->status == 'pending')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                        @elseif($writer->status == 'suspended' || $writer->is_suspended == 'yes')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Suspended</span>
                                        @elseif($writer->status == 'banned')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Banned</span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">{{ ucfirst($writer->status) }}</span>
                                        @endif
                                    </span>
                                </div>
                                <div>
                                    <span class="block text-xs text-gray-500">Verification</span>
                                    <span class="block">
                                        @if($writer->writerProfile && $writer->writerProfile->id_verification_status == 'verified')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Verified</span>
                                        @elseif($writer->writerProfile && $writer->writerProfile->id_verification_status == 'pending')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                        @elseif($writer->writerProfile && $writer->writerProfile->id_verification_status == 'rejected')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Failed</span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">None</span>
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="px-5 py-3 bg-gray-50 border-t flex justify-between items-center">
                            <a href="{{ route('admin.writers.show', $writer->id) }}" class="text-sm font-medium text-primary-600 hover:text-primary-900">
                                View Details
                            </a>
                            
                            <div class="flex space-x-2">
                                @if($writer->status != 'suspended' && $writer->is_suspended != 'yes')
                                    <form action="{{ route('admin.writers.suspend', $writer->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500" onclick="return confirm('Are you sure you want to suspend this writer?')">
                                            <i class="fas fa-ban mr-1"></i> Suspend
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('admin.writers.activate', $writer->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded text-green-700 bg-green-100 hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500" onclick="return confirm('Are you sure you want to activate this writer?')">
                                            <i class="fas fa-check-circle mr-1"></i> Activate
                                        </button>
                                    </form>
                                @endif
                                
                                @if($writer->writerProfile && $writer->writerProfile->id_verification_status == 'pending')
                                    <form action="{{ route('admin.writers.verify', $writer->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" onclick="return confirm('Are you sure you want to verify this writer?')">
                                            <i class="fas fa-user-check mr-1"></i> Verify
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
        
        <!-- Pagination -->
        <div class="mt-6">
            {{ $writers->links() }}
        </div>
    @else
        <div class="bg-white shadow rounded-lg p-6 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100">
                <i class="fas fa-exclamation-triangle text-yellow-600"></i>
            </div>
            <h3 class="mt-3 text-lg font-medium text-gray-900">No writers found</h3>
            <p class="mt-2 text-sm text-gray-500">
                No writers match your current filtering criteria. Try adjusting your filters or search terms.
            </p>
            <div class="mt-4">
                <a href="{{ route('admin.writers.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                    Clear Filters
                </a>
            </div>
        </div>
    @endif


<script>
    // Initialize any JS needed for this page
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-submit form when status or verification filters change
        document.getElementById('status').addEventListener('change', function() {
            this.form.submit();
        });
        
        document.getElementById('verification').addEventListener('change', function() {
            this.form.submit();
        });
    });
</script>
@endsection

