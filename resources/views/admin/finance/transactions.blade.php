@extends('admin.app')

@section('title', 'Financial Transactions')

@section('page-title', 'Financial Transactions')

@section('content')
<div class="space-y-6">
    <!-- Filter Controls -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <form action="{{ route('admin.finance.transactions') }}" method="GET" class="space-y-4 sm:space-y-0 sm:flex sm:items-end sm:space-x-4">
            <div class="w-full sm:w-1/4">
                <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Transaction Type</label>
                <select id="type" name="type" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                    <option value="">All Types</option>
                    <option value="payment" {{ request('type') == 'payment' ? 'selected' : '' }}>Payment</option>
                    <option value="revenue" {{ request('type') == 'revenue' ? 'selected' : '' }}>Revenue</option>
                    <option value="refund" {{ request('type') == 'refund' ? 'selected' : '' }}>Refund</option>
                </select>
            </div>
            
            <div class="w-full sm:w-1/4">
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select id="status" name="status" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                    <option value="">All Statuses</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
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
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <!-- Total Transactions -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-100 rounded-full p-3">
                        <svg class="h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Transactions</dt>
                            <dd class="text-xl font-semibold text-gray-900">{{ $transactionCount }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Total Amount -->
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
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Amount</dt>
                            <dd class="text-xl font-semibold text-gray-900">${{ number_format($totalAmount, 2) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Net Balance -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-indigo-100 rounded-full p-3">
                        <svg class="h-6 w-6 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Net Balance</dt>
                            <dd class="text-xl font-semibold {{ $netBalance >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                ${{ number_format(abs($netBalance), 2) }}
                                <span class="text-sm">({{ $netBalance >= 0 ? 'Credit' : 'Debit' }})</span>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Transactions Table -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Transaction History</h3>
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
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($transactions as $transaction)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            #{{ $transaction->id }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                {{ $transaction->type == 'payment' ? 'bg-blue-100 text-blue-800' : 
                                   ($transaction->type == 'revenue' ? 'bg-green-100 text-green-800' : 
                                   'bg-yellow-100 text-yellow-800') }}">
                                {{ ucfirst($transaction->type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            ${{ number_format($transaction->amount, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                {{ $transaction->status == 'completed' ? 'bg-green-100 text-green-800' : 
                                   ($transaction->status == 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                   'bg-red-100 text-red-800') }}">
                                {{ ucfirst($transaction->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if ($transaction->user)
                                <a href="{{ route('admin.users.show', $transaction->user->id) }}" class="text-primary-600 hover:text-primary-900">
                                    {{ $transaction->user->name }}
                                </a>
                            @else
                                <span class="text-gray-400">System</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $transaction->created_at->format('M d, Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <div class="flex space-x-2">
                                <a href="{{ route('admin.finance.transaction', $transaction->id) }}" class="text-indigo-600 hover:text-indigo-900" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                
                                @if ($transaction->status == 'pending')
                                    <button type="button" onclick="confirmTransaction({{ $transaction->id }})" class="text-green-600 hover:text-green-900" title="Approve">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    
                                    <button type="button" onclick="rejectTransaction({{ $transaction->id }})" class="text-red-600 hover:text-red-900" title="Reject">
                                        <i class="fas fa-times"></i>
                                    </button>
                                @endif
                                
                                @if ($transaction->status == 'completed' && $transaction->type == 'payment' && $transaction->created_at->diffInDays(now()) < 30)
                                    <button type="button" onclick="initiateRefund({{ $transaction->id }})" class="text-yellow-600 hover:text-yellow-900" title="Refund">
                                        <i class="fas fa-undo"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                            No transactions found matching your criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $transactions->withQueryString()->links() }}
        </div>
    </div>
</div>

<!-- Transaction Approval Modal -->
<div id="approvalModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-auto">
        <div class="px-6 py-4 border-b">
            <h3 class="text-lg font-medium text-gray-900">Confirm Transaction</h3>
        </div>
        <div class="px-6 py-4">
            <p class="text-gray-700">Are you sure you want to approve this transaction? This action cannot be undone.</p>
            
            <form id="approvalForm" action="" method="POST" class="mt-4">
                @csrf
                @method('PUT')
                
                <div class="mt-4">
                    <label for="transaction_note" class="block text-sm font-medium text-gray-700">Admin Note (Optional)</label>
                    <textarea id="transaction_note" name="note" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"></textarea>
                </div>
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

<!-- Transaction Rejection Modal -->
<div id="rejectionModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-auto">
        <div class="px-6 py-4 border-b">
            <h3 class="text-lg font-medium text-gray-900">Reject Transaction</h3>
        </div>
        <div class="px-6 py-4">
            <p class="text-gray-700">Are you sure you want to reject this transaction? This action cannot be undone.</p>
            
            <form id="rejectionForm" action="" method="POST" class="mt-4">
                @csrf
                @method('PUT')
                
                <div class="mt-4">
                    <label for="rejection_reason" class="block text-sm font-medium text-gray-700">Reason for Rejection <span class="text-red-500">*</span></label>
                    <textarea id="rejection_reason" name="reason" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" required></textarea>
                </div>
            </form>
        </div>
        <div class="px-6 py-4 border-t bg-gray-50 flex justify-end space-x-3">
            <button type="button" onclick="closeRejectionModal()" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                Cancel
            </button>
            <button type="button" onclick="submitRejectionForm()" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                Reject
            </button>
        </div>
    </div>
</div>

<!-- Refund Modal -->
<div id="refundModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-auto">
        <div class="px-6 py-4 border-b">
            <h3 class="text-lg font-medium text-gray-900">Initiate Refund</h3>
        </div>
        <div class="px-6 py-4">
            <p class="text-gray-700">Are you sure you want to initiate a refund for this transaction? This will create a new refund transaction.</p>
            
            <form id="refundForm" action="" method="POST" class="mt-4">
                @csrf
                
                <div class="mt-4">
                    <label for="refund_amount" class="block text-sm font-medium text-gray-700">Refund Amount <span class="text-red-500">*</span></label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">$</span>
                        </div>
                        <input type="number" step="0.01" name="amount" id="refund_amount" class="focus:ring-primary-500 focus:border-primary-500 block w-full pl-7 pr-12 sm:text-sm border-gray-300 rounded-md" placeholder="0.00" required>
                    </div>
                </div>
                
                <div class="mt-4">
                    <label for="refund_reason" class="block text-sm font-medium text-gray-700">Reason for Refund <span class="text-red-500">*</span></label>
                    <textarea id="refund_reason" name="reason" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" required></textarea>
                </div>
            </form>
        </div>
        <div class="px-6 py-4 border-t bg-gray-50 flex justify-end space-x-3">
            <button type="button" onclick="closeRefundModal()" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                Cancel
            </button>
            <button type="button" onclick="submitRefundForm()" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                Process Refund
            </button>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // Approval Modal Functions
    function confirmTransaction(id) {
        document.getElementById('approvalForm').action = "{{ route('admin.finance.approve', '') }}/" + id;
        document.getElementById('approvalModal').classList.remove('hidden');
    }
    
    function closeApprovalModal() {
        document.getElementById('approvalModal').classList.add('hidden');
    }
    
    function submitApprovalForm() {
        document.getElementById('approvalForm').submit();
    }
    
    // Rejection Modal Functions
    function rejectTransaction(id) {
        document.getElementById('rejectionForm').action = "{{ route('admin.finance.reject', '') }}/" + id;
        document.getElementById('rejectionModal').classList.remove('hidden');
    }
    
    function closeRejectionModal() {
        document.getElementById('rejectionModal').classList.add('hidden');
    }
    
    function submitRejectionForm() {
        document.getElementById('rejectionForm').submit();
    }
    
    // Refund Modal Functions
    function initiateRefund(id) {
        document.getElementById('refundForm').action = "{{ route('admin.finance.refund', '') }}/" + id;
        document.getElementById('refundModal').classList.remove('hidden');
    }
    
    function closeRefundModal() {
        document.getElementById('refundModal').classList.add('hidden');
    }
    
    function submitRefundForm() {
        document.getElementById('refundForm').submit();
    }
    
    // Close modals when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target.id === 'approvalModal') {
            closeApprovalModal();
        }
        if (event.target.id === 'rejectionModal') {
            closeRejectionModal();
        }
        if (event.target.id === 'refundModal') {
            closeRefundModal();
        }
    });
</script>
@endsection