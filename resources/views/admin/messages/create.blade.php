@extends('admin.app')

@section('title', 'New Message')

@section('page-title', 'Compose New Message')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Compose New Message</h1>
            <p class="mt-1 text-sm text-gray-500">Send a message to a writer or client</p>
        </div>
        <div>
            <a href="{{ route('admin.messages.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                <i class="fas fa-arrow-left mr-2"></i> Back to Messages
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <form action="{{ route('admin.messages.store') }}" method="POST" enctype="multipart/form-data" id="messageForm" class="divide-y divide-gray-200">
            @csrf
            
            <div class="p-6 space-y-6">
                <!-- Message Header Info -->
                <div>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">Recipient Type</label>
                            <div class="flex space-x-6">
                                <div class="relative flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="recipient_type_writer" name="recipient_type" type="radio" value="writer" class="h-4 w-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500" checked>
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="recipient_type_writer" class="font-medium text-gray-700">Writer</label>
                                        <p class="text-gray-500">Send to a freelance writer</p>
                                    </div>
                                </div>
                                <div class="relative flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="recipient_type_client" name="recipient_type" type="radio" value="client" class="h-4 w-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="recipient_type_client" class="font-medium text-gray-700">Client</label>
                                        <p class="text-gray-500">Send to a client</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <label for="receiver_id" class="block text-sm font-medium text-gray-700 mb-1">Select Recipient <span class="text-red-600">*</span></label>
                            <div class="relative">
                                <select id="receiver_id" name="receiver_id" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md" required>
                                    <option value="">Select a recipient</option>
                                    
                                    <!-- Writers Group -->
                                    <optgroup label="Writers" id="writers_group">
                                        @foreach($writers as $writer)
                                            <option value="{{ $writer->id }}">{{ $writer->name }} {{ $writer->email ? '(' . $writer->email . ')' : '' }}</option>
                                        @endforeach
                                    </optgroup>
                                    
                                    <!-- Clients Group -->
                                    <optgroup label="Clients" id="clients_group" style="display: none;">
                                        @foreach($clients as $client)
                                            <option value="{{ $client->id }}">{{ $client->name }} {{ $client->email ? '(' . $client->email . ')' : '' }}</option>
                                        @endforeach
                                    </optgroup>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                                    <i class="fas fa-chevron-down text-gray-400"></i>
                                </div>
                            </div>
                            @error('receiver_id')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <!-- Order Reference -->
                <div>
                    <label for="order_id" class="block text-sm font-medium text-gray-700 mb-1">Related Order (Optional)</label>
                    <div class="relative">
                        <select id="order_id" name="order_id" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md">
                            <option value="">None (General Message)</option>
                            @foreach($orders as $order)
                                <option value="{{ $order->id }}">#{{ $order->id }}: {{ Str::limit($order->title, 100) }}</option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                            <i class="fas fa-chevron-down text-gray-400"></i>
                        </div>
                    </div>
                    @error('order_id')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Message Subject -->
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                    <input type="text" id="title" name="title" class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm" placeholder="Enter a subject for your message" value="{{ old('title') }}">
                    @error('title')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Message Type (for writers) -->
                <div id="messageTypeContainer" class="hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-3">Message Type</label>
                    <div class="flex space-x-6">
                        <div class="relative flex items-start">
                            <div class="flex items-center h-5">
                                <input id="message_type_admin" name="message_type" type="radio" value="admin" class="h-4 w-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500" checked>
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="message_type_admin" class="font-medium text-gray-700">Send as Admin</label>
                                <p class="text-gray-500">Message will appear as sent from administration</p>
                            </div>
                        </div>
                        <div class="relative flex items-start">
                            <div class="flex items-center h-5">
                                <input id="message_type_client" name="message_type" type="radio" value="client" class="h-4 w-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="message_type_client" class="font-medium text-gray-700">Send as Client</label>
                                <p class="text-gray-500">Message will appear as sent from the client</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Message Content -->
                <div>
                    <label for="message" class="block text-sm font-medium text-gray-700 mb-1">Message <span class="text-red-600">*</span></label>
                    <textarea id="message" name="message" rows="8" class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm" placeholder="Type your message here..." required>{{ old('message') }}</textarea>
                    @error('message')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- File Attachments -->
                <div>
                    <span class="block text-sm font-medium text-gray-700 mb-2">Attachments</span>
                    <div class="flex items-center">
                        <label for="files" class="relative cursor-pointer bg-white rounded-md font-medium text-primary-600 hover:text-primary-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary-500">
                            <span class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                <i class="fas fa-paperclip mr-2"></i> Attach Files
                            </span>
                            <input id="files" name="files[]" type="file" multiple class="sr-only">
                        </label>
                        <p class="pl-3 text-xs text-gray-500" id="fileHelp">
                            Upload up to 5 files (10MB max per file)
                        </p>
                    </div>
                    <div class="mt-4" id="filePreviewContainer">
                        <ul id="filePreviewList" class="space-y-2 max-h-60 overflow-y-auto"></ul>
                    </div>
                    @error('files')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    @error('files.*')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            
            <!-- Form Actions -->
            <div class="px-6 py-4 bg-gray-50 flex justify-end space-x-3">
                <a href="{{ route('admin.messages.index') }}" class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                    Cancel
                </a>
                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                    <i class="fas fa-paper-plane mr-2"></i> Send Message
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const recipientTypeWriter = document.getElementById('recipient_type_writer');
    const recipientTypeClient = document.getElementById('recipient_type_client');
    const writersGroup = document.getElementById('writers_group');
    const clientsGroup = document.getElementById('clients_group');
    const receiverSelect = document.getElementById('receiver_id');
    const fileInput = document.getElementById('files');
    const filePreviewList = document.getElementById('filePreviewList');
    const filePreviewContainer = document.getElementById('filePreviewContainer');
    const messageTypeContainer = document.getElementById('messageTypeContainer');
    const orderSelect = document.getElementById('order_id');
    
    // Initialize file preview container as empty
    filePreviewContainer.style.display = 'none';
    
    // Handle recipient type changes
    function handleRecipientTypeChange() {
        if (recipientTypeWriter.checked) {
            writersGroup.style.display = '';
            clientsGroup.style.display = 'none';
            messageTypeContainer.classList.remove('hidden');
            
            // Reset select value
            receiverSelect.value = '';
            
            // Show only writer options
            Array.from(receiverSelect.options).forEach(option => {
                if (option.parentNode === writersGroup || option.value === '') {
                    option.style.display = '';
                } else {
                    option.style.display = 'none';
                }
            });
        } else {
            writersGroup.style.display = 'none';
            clientsGroup.style.display = '';
            messageTypeContainer.classList.add('hidden');
            
            // Reset select value
            receiverSelect.value = '';
            
            // Show only client options
            Array.from(receiverSelect.options).forEach(option => {
                if (option.parentNode === clientsGroup || option.value === '') {
                    option.style.display = '';
                } else {
                    option.style.display = 'none';
                }
            });
        }
    }
    
    recipientTypeWriter.addEventListener('change', handleRecipientTypeChange);
    recipientTypeClient.addEventListener('change', handleRecipientTypeChange);
    
    // Initialize on page load
    handleRecipientTypeChange();
    
    // Handle file uploads and preview
    fileInput.addEventListener('change', function() {
        filePreviewList.innerHTML = '';
        
        if (this.files.length > 0) {
            filePreviewContainer.style.display = 'block';
            
            Array.from(this.files).forEach((file, index) => {
                // Format file size
                let size = file.size;
                let sizeDisplay = '';
                
                if (size < 1024) {
                    sizeDisplay = size + ' bytes';
                } else if (size < 1024 * 1024) {
                    sizeDisplay = (size / 1024).toFixed(1) + ' KB';
                } else {
                    sizeDisplay = (size / (1024 * 1024)).toFixed(1) + ' MB';
                }
                
                // Determine icon based on file type
                let icon = 'fa-file';
                const extension = file.name.split('.').pop().toLowerCase();
                
                if (['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'].includes(extension)) {
                    icon = 'fa-file-image';
                } else if (['doc', 'docx'].includes(extension)) {
                    icon = 'fa-file-word';
                } else if (['xls', 'xlsx', 'csv'].includes(extension)) {
                    icon = 'fa-file-excel';
                } else if (extension === 'pdf') {
                    icon = 'fa-file-pdf';
                } else if (['zip', 'rar', '7z'].includes(extension)) {
                    icon = 'fa-file-archive';
                } else if (['mp3', 'wav', 'ogg'].includes(extension)) {
                    icon = 'fa-file-audio';
                } else if (['mp4', 'avi', 'mov', 'wmv'].includes(extension)) {
                    icon = 'fa-file-video';
                } else if (['txt', 'rtf'].includes(extension)) {
                    icon = 'fa-file-alt';
                }
                
                // Create list item for preview
                const li = document.createElement('li');
                li.className = 'relative py-3 px-4 flex items-center bg-gray-50 rounded-lg';
                
                li.innerHTML = `
                    <div class="flex-shrink-0 text-primary-500 mr-4">
                        <i class="fas ${icon} text-xl"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-gray-900 truncate">${file.name}</p>
                        <p class="text-xs text-gray-500">${sizeDisplay}</p>
                    </div>
                    <div class="ml-4 flex-shrink-0">
                        <button type="button" class="bg-white rounded-md text-gray-400 hover:text-gray-500 focus:outline-none" onclick="removeFile(${index})">
                            <span class="sr-only">Remove file</span>
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;
                
                filePreviewList.appendChild(li);
            });
            
            // Update help text
            document.getElementById('fileHelp').textContent = `${this.files.length} file(s) selected`;
        } else {
            filePreviewContainer.style.display = 'none';
            document.getElementById('fileHelp').textContent = 'Upload up to 5 files (10MB max per file)';
        }
    });
    
    // Show message type options if related to an order
    orderSelect.addEventListener('change', function() {
        if (recipientTypeWriter.checked) {
            messageTypeContainer.classList.remove('hidden');
        }
    });
    
    // Function to remove a file from the input
    window.removeFile = function(index) {
        const dt = new DataTransfer();
        const files = fileInput.files;
        
        for (let i = 0; i < files.length; i++) {
            if (i !== index) {
                dt.items.add(files[i]);
            }
        }
        
        fileInput.files = dt.files;
        
        // Update the file preview
        fileInput.dispatchEvent(new Event('change'));
    };
});
</script>
@endpush