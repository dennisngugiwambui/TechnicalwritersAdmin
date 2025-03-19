@extends('admin.app')

@section('title', 'Transaction Details')

@section('page-title', 'Transaction Details')

@section('content')
<div class="space-y-6">
    <!-- Action buttons -->
    <div class="flex justify-end space-x-4">
        <a href="{{ route('admin.finance.transactions') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
            <i class="fas fa-arrow-left mr-2"></i> Back to Transactions
        </a>
        
        @if ($transaction->transaction_type == 'withdrawal' && $transaction->status == 'pending')
        <button type="button" onclick="confirmTransaction({{ $transaction->id }})" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
            <i class="fas fa-check mr-2"></i> Approve Withdrawal
        </button>
        
        <button type="button" onclick="rejectTransaction({{ $transaction->id }})" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
            <i class="fas fa-times mr-2"></i> Reject Withdrawal
        </button>
        @endif
        
        @if ($transaction->transaction_type == 'withdrawal' && $transaction->status == 'processing')
        <button type="button" onclick="completeTransaction({{ $transaction->id }})" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
            <i class="fas fa-check-double mr-2"></i> Mark as Completed
        </button>
        @endif
        
        @if ($transaction->transaction_type == 'order_payment' && $transaction->status == 'completed' && $transaction->created_at->diffInDays(now()) < 30)
        <button type="button" onclick="initiateRefund({{ $transaction->id }})" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
            <i class="fas fa-undo mr-2"></i> Process Refund
        </button>
        @endif
    </div>
    
    <!-- Transaction Details Card -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Transaction #{{ $transaction->id }}</h3>
        </div>
        
        <div class="px-6 py-5">
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Type</dt>
                    <dd class="mt-1 text-sm text-gray-900">{!! $transaction->type_badge !!}</dd>
                </div>
                
                <div>
                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                    <dd class="mt-1 text-sm text-gray-900">{!! $transaction->status_badge !!}</dd>
                </div>
                
                <div>
                    <dt class="text-sm font-medium text-gray-500">Amount</dt>
                    <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ $transaction->formatted_amount }}</dd>
                </div>
                
                <div>
                    <dt class="text-sm font-medium text-gray-500">Balance Before</dt>
                    <dd class="mt-1 text-sm text-gray-900">${{ number_format($transaction->balance_before, 2) }}</dd>
                </div>
                
                <div>
                    <dt class="text-sm font-medium text-gray-500">Balance After</dt>
                    <dd class="mt-1 text-sm text-gray-900">${{ number_format($transaction->balance_after, 2) }}</dd>
                </div>
                
                <div>
                    <dt class="text-sm font-medium text-gray-500">Date Created</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $transaction->created_at->format('M d, Y H:i') }}</dd>
                </div>
                
                @if($transaction->processed_at)
                <div>
                    <dt class="text-sm font-medium text-gray-500">Date Processed</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $transaction->processed_at->format('M d, Y H:i') }}</dd>
                </div>
                @endif
                
                <div>
                    <dt class="text-sm font-medium text-gray-500">User</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        @if($transaction->user)
                        <a href="{{ route('admin.users.show', $transaction->user->id) }}" class="text-primary-600 hover:text-primary-900">
                            {{ $transaction->user->name }}
                        </a>
                        @else
                        <span class="text-gray-400">System</span>
                        @endif
                    </dd>
                </div>
                
                @if($transaction->order)
                <div>
                    <dt class="text-sm font-medium text-gray-500">Order</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        <a href="{{ route('admin.orders.show', $transaction->order->id) }}" class="text-primary-600 hover:text-primary-900">
                            Order #{{ $transaction->order->id }}
                        </a>
                    </dd>
                </div>
                @endif
                
                @if($transaction->payment_method)
                <div>
                    <dt class="text-sm font-medium text-gray-500">Payment Method</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ ucfirst($transaction->payment_method) }}</dd>
                </div>
                @endif
                
                @if($transaction->payment_reference)
                <div>
                    <dt class="text-sm font-medium text-gray-500">Reference/Transaction ID</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $transaction->payment_reference }}</dd>
                </div>
                @endif
                
                @if($transaction->processor)
                <div>
                    <dt class="text-sm font-medium text-gray-500">Processed By</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $transaction->processor->name }}</dd>
                </div>
                @endif
                
                <div class="md:col-span-2">
                    <dt class="text-sm font-medium text-gray-500">Description</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $transaction->description }}</dd>
                </div>
            </dl>
        </div>
    </div>
</div>

<!-- Complete Transaction Modal -->
<div id="completeModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-auto">
        <div class="px-6 py-4 border-b">
            <h3 class="text-lg font-medium text-gray-900">Complete Transaction</h3>
        </div>
        <div class="px-6 py-4">
            <p class="text-gray-700">Please provide the reference/transaction ID for this payment.</p>
            
            <form id="completeForm" action="{{ route('admin.finance.complete', $transaction->id) }}" method="POST" class="mt-4">
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
                Complete Transaction
            </button>
        </div>
    </div>
</div>

<!-- Transaction Approval Modal -->
<div id="approvalModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-auto">
        <div class="px-6 py-4 border-b">
            <h3 class="text-lg font-medium text-gray-900">Approve Withdrawal</h3>
        </div>
        <div class="px-6 py-4">
            <p class="text-gray-700">Are you sure you want to approve this withdrawal request? This will initiate the payment process.</p>
        </div>
        <div class="px-6 py-4 border-t bg-gray-50 flex justify-end space-x-3">
            <button type="button" onclick="closeApprovalModal()" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                Cancel
            </button>
            <form action="{{ route('admin.finance.approve', $transaction->id) }}" method="POST">
                @csrf
                @method('PUT')
                <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    Approve Withdrawal
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Transaction Rejection Modal -->
<div id="rejectionModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-auto">
        <div class="px-6 py-4 border-b">
            <h3 class="text-lg font-medium text-gray-900">Reject Withdrawal</h3>
        </div>
        <div class="px-6 py-4">
            <p class="text-gray-700">Please provide a reason for rejecting this withdrawal request.</p>
            
            <form id="rejectionForm" action="{{ route('admin.finance.reject', $transaction->id) }}" method="POST" class="mt-4">
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

<!-- Refund Modal -->
<div id="refundModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-auto">
        <div class="px-6 py-4 border-b">
            <h3 class="text-lg font-medium text-gray-900">Process Refund</h3>
        </div>
        <div class="px-6 py-4">
            <p class="text-gray-700">Please specify the refund amount and reason. The maximum refund amount is ${{ number_format($transaction->amount, 2) }}.</p>
            
            <form id="refundForm" action="{{ route('admin.finance.refund', $transaction->id) }}" method="POST" class="mt-4">
                @csrf
                
                <div class="mt-4">
                    <label for="refund_amount" class="block text-sm font-medium text-gray-700">Refund Amount <span class="text-red-500">*</span></label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">$</span>
                        </div>
                        <input type="number" step="0.01" name="amount" id="refund_amount" class="focus:ring-primary-500 focus:border-primary-500 block w-full pl-7 pr-12 sm:text-sm border-gray-300 rounded-md" placeholder="0.00" max="{{ $transaction->amount }}" required>
                    </div>
                </div>
                
                <div class="mt-4">
                    <label for="refund_reason" class="block text-sm font-medium text-gray-700">Reason for Refund <span class="text-red-500">*</span></label>
                    <textarea id="refund_reason" name="reason" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" required></textarea>
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
    // Complete Transaction Modal Functions
    function completeTransaction(id) {
        document.getElementById('completeModal').classList.remove('hidden');
    }
    
    function closeCompleteModal() {
        document.getElementById('completeModal').classList.add('hidden');
    }
    
    function submitCompleteForm() {
        document.getElementById('completeForm').submit();
    }
    
    // Approval Modal Functions
    function confirmTransaction(id) {
        document.getElementById('approvalModal').classList.remove('hidden');
    }
    
    function closeApprovalModal() {
        document.getElementById('approvalModal').classList.add('hidden');
    }
    
    // Rejection Modal Functions
    function rejectTransaction(id) {
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
        if (event.target.id === 'completeModal') {
            closeCompleteModal();
        }
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