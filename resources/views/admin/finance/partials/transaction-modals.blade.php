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

<!-- Complete Transaction Modal -->
<div id="completeModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-auto">
        <div class="px-6 py-4 border-b">
            <h3 class="text-lg font-medium text-gray-900">Complete Transaction</h3>
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
                Complete Transaction
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