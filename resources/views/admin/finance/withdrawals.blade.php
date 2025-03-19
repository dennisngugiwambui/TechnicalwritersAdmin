@extends('admin.app')

@section('title', 'Withdrawal Requests')

@section('page-title', 'Withdrawal Requests')

@section('content')
<div class="space-y-6">
    <!-- Filter Controls -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <form action="{{ route('admin.finance.withdrawals') }}" method="GET" class="space-y-4 sm:space-y-0 sm:flex sm:items-end sm:space-x-4">
            <div class="w-full sm:w-1/4">
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select id="status" name="status" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
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
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <!-- Total Pending Amount -->
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
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Pending Withdrawals</dt>
                            <dd class="text-xl font-semibold text-gray-900">${{ number_format($totalPendingAmount, 2) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Total Processed Today -->
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
                            <dt class="text-sm font-medium text-gray-500 truncate">Processed Today</dt>
                            <dd class="text-xl font-semibold text-gray-900">${{ number_format($totalProcessedToday, 2) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Withdrawals Table -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Withdrawal Requests</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Writer</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($withdrawals as $withdrawal)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            #{{ $withdrawal->id }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if ($withdrawal->user)
                                <a href="{{ route('admin.users.show', $withdrawal->user->id) }}" class="text-primary-600 hover:text-primary-900">
                                    {{ $withdrawal->user->name }}
                                </a>
                            @else
                                <span class="text-gray-400">Unknown</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-medium">
                            ${{ number_format($withdrawal->amount, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ ucfirst($withdrawal->payment_method ?? 'Unknown') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {!! $withdrawal->status_badge !!}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $withdrawal->created_at->format('M d, Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <div class="flex space-x-2">
                                <a href="{{ route('admin.finance.transaction', $withdrawal->id) }}" class="text-indigo-600 hover:text-indigo-900" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                
                                @if ($withdrawal->status == 'pending')
                                    <button type="button" onclick="confirmWithdrawal({{ $withdrawal->id }})" class="text-green-600 hover:text-green-900" title="Approve">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    
                                    <button type="button" onclick="rejectWithdrawal({{ $withdrawal->id }})" class="text-red-600 hover:text-red-900" title="Reject">
                                        <i class="fas fa-times"></i>
                                    </button>
                                @endif
                                
                                @if ($withdrawal->status == 'processing')
                                    <button type="button" onclick="completeWithdrawal({{ $withdrawal->id }})" class="text-green-600 hover:text-green-900" title="Mark as Completed">
                                        <i class="fas fa-check-double"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                            No withdrawal requests found matching your criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $withdrawals->withQueryString()->links() }}
        </div>
    </div>
</div>

<!-- Withdrawal Approval Modal -->
<div id="approvalModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-auto">
        <div class="px-6 py-4 border-b">
            <h3 class="text-lg font-medium text-gray-900">Approve Withdrawal</h3>
        </div>
        <div class="px-6 py-4">
            <p class="text-gray-700">Are you sure you want to approve this withdrawal request? This will initiate the payment process.</p>
            
            <form id="approvalForm" action="" method="POST">
                @csrf
                @method('PUT')
            </form>
        </div>
        <div class="px-6 py-4 border-t bg-gray-50 flex justify-end space-x-3">
            <button type="button" onclick="closeApprovalModal()" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                Cancel
            </button>
            <button type="button" onclick="submitApprovalForm()" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                Approve
            </button>
        </div>
    </div>
</div>

<!-- Complete Withdrawal Modal -->
<div id="completeModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-auto">
        <div class="px-6 py-4 border-b">
            <h3 class="text-lg font-medium text-gray-900">Complete Withdrawal</h3>
        </div>
        <div class="px-6 py-4">
            <p class="text-gray-700">Please provide the reference/transaction ID for this payment.</p>
            
            <form id="completeForm" action="" method="POST" class="mt-4">
                @csrf
                @method('PUT')
                
                <div class="mt-4">
                    <label for="payment_reference" class="block text-sm font-medium text-gray-700">Reference/Transaction ID <span class="text-red-500">*</span></label>
                    <input type="text" id="payment_reference" name="payment_reference" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" required>
                </div>
            </form>
        </div>
        <div class="px-6 py-4 border-t bg-gray-50 flex justify-end space-x-3">
            <button type="button" onclick="closeCompleteModal()" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                Cancel
            </button>
            <button type="button" onclick="submitCompleteForm()" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                Complete Withdrawal
            </button>
        </div>
    </div>
</div>

<!-- Withdrawal Rejection Modal -->
<div id="rejectionModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-auto">
        <div class="px-6 py-4 border-b">
            <h3 class="text-lg font-medium text-gray-900">Reject Withdrawal</h3>
        </div>
        <div class="px-6 py-4">
            <p class="text-gray-700">Please provide a reason for rejecting this withdrawal request.</p>
            
            <form id="rejectionForm" action="" method="POST" class="mt-4">
                @csrf
                @method('PUT')
                
                <div class="mt-4">
                    <label for="rejection_reason" class="block text-sm font-medium text-gray-700">Reason for Rejection <span class="text-red-500">*</span></label>
                    <textarea id="rejection_reason" name="reason" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" required></textarea>
                </div>
            </form>
        </div>
        <div class="px-6 py-4 border-t bg-gray-50 flex justify-end space-x-3">
            <button type="button" onclick="closeRejectionModal()" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                Cancel
            </button>
            <button type="button" onclick="submitRejectionForm()" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                Reject Withdrawal
            </button>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // Approval Modal Functions
    function confirmWithdrawal(id) {
        document.getElementById('approvalForm').action = "{{ route('admin.finance.approve', '') }}/" + id;
        document.getElementById('approvalModal').classList.remove('hidden');
    }
    
    function closeApprovalModal() {
        document.getElementById('approvalModal').classList.add('hidden');
    }
    
    function submitApprovalForm() {
        document.getElementById('approvalForm').submit();
    }
    
    // Complete Modal Functions
    function completeWithdrawal(id) {
        document.getElementById('completeForm').action = "{{ route('admin.finance.complete', '') }}/" + id;
        document.getElementById('completeModal').classList.remove('hidden');
    }
    
    function closeCompleteModal() {
        document.getElementById('completeModal').classList.add('hidden');
    }
    
    function submitCompleteForm() {
        document.getElementById('completeForm').submit();
    }
    
    // Rejection Modal Functions
    function rejectWithdrawal(id) {
        document.getElementById('rejectionForm').action = "{{ route('admin.finance.reject', '') }}/" + id;
        document.getElementById('rejectionModal').classList.remove('hidden');
    }
    
    function closeRejectionModal() {
        document.getElementById('rejectionModal').classList.add('hidden');
    }
    
    function submitRejectionForm() {
        document.getElementById('rejectionForm').submit();
    }
    
    // Close modals when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target.id === 'approvalModal') {
            closeApprovalModal();
        }
        if (event.target.id === 'completeModal') {
            closeCompleteModal();
        }
        if (event.target.id === 'rejectionModal') {
            closeRejectionModal();
        }
    });
</script>
@endsection