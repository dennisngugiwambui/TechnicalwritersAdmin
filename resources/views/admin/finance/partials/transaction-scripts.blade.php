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

// Complete Modal Functions
function completeTransaction(id) {
    document.getElementById('completeForm').action = "{{ route('admin.finance.complete', '') }}/" + id;
    document.getElementById('completeModal').classList.remove('hidden');
}

function closeCompleteModal() {
    document.getElementById('completeModal').classList.add('hidden');
}

function submitCompleteForm() {
    document.getElementById('completeForm').submit();
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
    if (event.target.id === 'completeModal') {
        closeCompleteModal();
    }
    if (event.target.id === 'refundModal') {
        closeRefundModal();
    }
});
</script>