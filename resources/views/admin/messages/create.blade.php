@extends('admin.app')

@section('title', 'New Message')

@section('page-title', 'Compose New Message')

@section('content')
<div class="max-w-4xl mx-auto transition-all duration-300 transform animate-fade-in-up">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 flex items-center">
                <i class="fas fa-pen-to-square mr-2 text-primary-500"></i>
                Compose New Message
            </h1>
            <p class="mt-1 text-sm text-gray-500">Send a message to a writer or client</p>
        </div>
        <div>
            <a href="{{ route('admin.messages.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all duration-300 hover:scale-105">
                <i class="fas fa-arrow-left mr-2"></i> Back to Messages
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100 transition-all duration-300 hover:shadow-md">
        <form action="{{ route('admin.messages.store') }}" method="POST" enctype="multipart/form-data" id="messageForm" class="divide-y divide-gray-200">
            @csrf
            
            <div class="p-4 sm:p-6 space-y-5 sm:space-y-6">
                <!-- Message Header Info -->
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-100 transition-all duration-300 hover:bg-gray-100/50">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="space-y-3">
                            <label class="block text-sm font-medium text-gray-700">Recipient Type</label>
                            <div class="flex flex-wrap gap-4">
                                <div class="recipient-option relative flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="recipient_type_writer" name="recipient_type" type="radio" value="writer" class="h-5 w-5 text-primary-600 border-gray-300 rounded-full focus:ring-primary-500 transition-all duration-200" checked>
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="recipient_type_writer" class="font-medium text-gray-700 cursor-pointer">Writer</label>
                                        <p class="text-gray-500">Send to freelance writers</p>
                                    </div>
                                </div>
                                <div class="recipient-option relative flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="recipient_type_client" name="recipient_type" type="radio" value="client" class="h-5 w-5 text-primary-600 border-gray-300 rounded-full focus:ring-primary-500 transition-all duration-200">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="recipient_type_client" class="font-medium text-gray-700 cursor-pointer">Client</label>
                                        <p class="text-gray-500">Send to clients</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <label for="receiver_id" class="block text-sm font-medium text-gray-700 mb-1">
                                Select Recipient <span class="text-red-600">*</span>
                            </label>
                            <div class="relative">
                                <select id="receiver_id" name="receiver_id" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md transition-colors duration-200" required>
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
                                <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none text-gray-400">
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                            </div>
                            <div class="mt-1 min-h-6">
                                @error('receiver_id')
                                    <p class="text-sm text-red-600 animate-pulse">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Order Reference -->
                <div class="relative group">
                    <label for="order_id" class="block text-sm font-medium text-gray-700 mb-1 group-hover:text-primary-600 transition-colors duration-200">
                        Related Order <span class="text-xs text-gray-400">(Optional)</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-clipboard-list text-gray-400 group-hover:text-primary-500 transition-colors duration-200"></i>
                        </div>
                        <select id="order_id" name="order_id" class="block w-full pl-10 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md transition-all duration-200 group-hover:border-primary-300">
                            <option value="">None (General Message)</option>
                            @foreach($orders as $order)
                                <option value="{{ $order->id }}">#{{ $order->id }}: {{ Str::limit($order->title, 80) }}</option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                            <i class="fas fa-chevron-down text-gray-400 group-hover:text-primary-500 transition-colors duration-200"></i>
                        </div>
                    </div>
                    <div class="mt-1 min-h-6">
                        @error('order_id')
                            <p class="text-sm text-red-600 animate-pulse">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                
                <!-- Message Subject -->
                <div class="relative group">
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1 group-hover:text-primary-600 transition-colors duration-200">Subject</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-heading text-gray-400 group-hover:text-primary-500 transition-colors duration-200"></i>
                        </div>
                        <input type="text" id="title" name="title" class="block w-full pl-10 pr-3 py-2 border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-all duration-200 group-hover:border-primary-300" placeholder="Enter a subject for your message" value="{{ old('title') }}">
                    </div>
                    <div class="mt-1 min-h-6">
                        @error('title')
                            <p class="text-sm text-red-600 animate-pulse">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                
                <!-- Message Type (for writers) -->
                <div id="messageTypeContainer" class="hidden bg-gray-50 rounded-lg p-4 border border-gray-100 transition-all duration-300 transform scale-95 opacity-0">
                    <label class="block text-sm font-medium text-gray-700 mb-3">Message Type</label>
                    <div class="flex flex-wrap gap-4">
                        <div class="message-type-option relative flex items-start">
                            <div class="flex items-center h-5">
                                <input id="message_type_admin" name="message_type" type="radio" value="admin" class="h-5 w-5 text-primary-600 border-gray-300 rounded-full focus:ring-primary-500 transition-all duration-200" checked>
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="message_type_admin" class="font-medium text-gray-700 cursor-pointer">Send as Admin</label>
                                <p class="text-gray-500">Message will appear as sent from administration</p>
                            </div>
                        </div>
                        <div class="message-type-option relative flex items-start">
                            <div class="flex items-center h-5">
                                <input id="message_type_client" name="message_type" type="radio" value="client" class="h-5 w-5 text-primary-600 border-gray-300 rounded-full focus:ring-primary-500 transition-all duration-200">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="message_type_client" class="font-medium text-gray-700 cursor-pointer">Send as Client</label>
                                <p class="text-gray-500">Message will appear as sent from the client</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Message Content -->
                <div class="relative group">
                    <div class="flex justify-between items-center mb-1">
                        <label for="message" class="block text-sm font-medium text-gray-700 group-hover:text-primary-600 transition-colors duration-200">
                            Message <span class="text-red-600">*</span>
                        </label>
                        <span id="char-count" class="text-xs text-gray-500">0/1500 characters</span>
                    </div>
                    <div class="relative">
                        <textarea id="message" name="message" rows="6" class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm resize-y transition-all duration-200 group-hover:border-primary-300" placeholder="Type your message here..." required maxlength="1500">{{ old('message') }}</textarea>
                        
                        <!-- Emoji Picker -->
                        <button type="button" id="emoji-button" class="absolute right-2 bottom-2 p-2 text-gray-400 hover:text-gray-600 focus:outline-none transition-colors duration-200">
                            <i class="far fa-smile"></i>
                        </button>
                        
                        <div id="emoji-picker" class="hidden absolute right-0 bottom-12 bg-white border border-gray-200 rounded-lg shadow-lg p-2 z-10">
                            <div class="grid grid-cols-8 gap-1">
                                @foreach(['😊', '😂', '😍', '👍', '👏', '🙏', '👀', '🔥', '💯', '⭐', '❤️', '✅', '⚠️', '🚫', '🤔', '😎'] as $emoji)
                                    <button type="button" class="emoji p-1 text-xl hover:bg-gray-100 rounded transition-colors duration-200">{{ $emoji }}</button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div id="error-message" class="hidden text-sm text-red-600 mt-1 animate-pulse">
                        Message cannot exceed 1500 characters
                    </div>
                    <div class="mt-1 min-h-6">
                        @error('message')
                            <p class="text-sm text-red-600 animate-pulse">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                
                <!-- File Attachments -->
                <div class="relative">
                    <span class="block text-sm font-medium text-gray-700 mb-2">Attachments</span>
                    
                    <div class="flex flex-wrap items-center gap-3">
                        <label for="files" class="relative cursor-pointer">
                            <div class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary-500 transition-all duration-300 hover:scale-105">
                                <i class="fas fa-paperclip mr-2 text-primary-500"></i> 
                                <span>Attach Files</span>
                            </div>
                            <input id="files" name="files[]" type="file" multiple class="sr-only">
                        </label>
                        <p class="text-xs text-gray-500" id="fileHelp">
                            Upload up to 5 files (10MB max per file)
                        </p>
                    </div>
                    
                    <div class="mt-4 transition-all duration-300 transform" id="filePreviewContainer">
                        <ul id="filePreviewList" class="space-y-2 max-h-60 overflow-y-auto rounded-lg border border-gray-100"></ul>
                    </div>
                    
                    <div class="mt-1 min-h-6">
                        @error('files')
                            <p class="text-sm text-red-600 animate-pulse">{{ $message }}</p>
                        @enderror
                        @error('files.*')
                            <p class="text-sm text-red-600 animate-pulse">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
            
            <!-- Form Actions -->
            <div class="px-4 sm:px-6 py-4 bg-gray-50 flex flex-col sm:flex-row justify-end gap-3 sm:space-x-3">
                <a href="{{ route('admin.messages.index') }}" class="inline-flex justify-center items-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all duration-300 hover:scale-105">
                    <i class="fas fa-times mr-2"></i> Cancel
                </a>
                <button type="submit" id="sendButton" class="inline-flex justify-center items-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all duration-300 hover:scale-105 relative overflow-hidden">
                    <i class="fas fa-paper-plane mr-2"></i> Send Message
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in-up {
    animation: fadeInUp 0.4s ease-out forwards;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.animate-pulse {
    animation: pulse 1.5s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

.ripple-effect {
    position: absolute;
    border-radius: 50%;
    background-color: rgba(255, 255, 255, 0.7);
    width: 100px;
    height: 100px;
    margin-top: -50px;
    margin-left: -50px;
    animation: ripple 0.6s;
    opacity: 0;
}

@keyframes ripple {
    0% {
        transform: scale(0);
        opacity: 0.5;
    }
    100% {
        transform: scale(4);
        opacity: 0;
    }
}

.recipient-option, .message-type-option {
    transition: all 0.3s ease;
}

.recipient-option:hover, .message-type-option:hover {
    transform: translateY(-2px);
}

/* Make file inputs more touch-friendly on mobile */
@media (max-width: 640px) {
    #fileHelp {
        width: 100%;
        margin-top: 0.5rem;
    }
}

/* Responsive font adjustments */
@media (min-width: 1024px) {
    .text-sm {
        font-size: 0.9rem;
    }
    
    .text-xs {
        font-size: 0.75rem;
    }
}
</style>
@endpush

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
    const messageInput = document.getElementById('message');
    const charCount = document.getElementById('char-count');
    const errorMessage = document.getElementById('error-message');
    const sendButton = document.getElementById('sendButton');
    const maxLength = 1500;
    
    // Initialize file preview container as hidden
    filePreviewContainer.style.display = 'none';
    
    // Handle recipient type changes with smooth animations
    function handleRecipientTypeChange() {
        if (recipientTypeWriter.checked) {
            // Show writer options with animation
            messageTypeContainer.classList.remove('hidden');
            setTimeout(() => {
                messageTypeContainer.classList.remove('scale-95', 'opacity-0');
            }, 10);
            
            writersGroup.style.display = '';
            clientsGroup.style.display = 'none';
            
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
            // Hide message type with animation
            messageTypeContainer.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                messageTypeContainer.classList.add('hidden');
            }, 300);
            
            writersGroup.style.display = 'none';
            clientsGroup.style.display = '';
            
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
    
    // Character counter functionality for message textarea
    messageInput.addEventListener('input', function() {
        const currentLength = this.value.length;
        charCount.textContent = `${currentLength}/1500 characters`;
        
        if (currentLength > maxLength) {
            charCount.classList.add('text-red-500', 'font-semibold');
            errorMessage.classList.remove('hidden');
            sendButton.disabled = true;
            sendButton.classList.add('opacity-50', 'cursor-not-allowed');
        } else {
            charCount.classList.remove('text-red-500', 'font-semibold');
            errorMessage.classList.add('hidden');
            sendButton.disabled = false;
            sendButton.classList.remove('opacity-50', 'cursor-not-allowed');
            
            // Color changes based on length
            if (currentLength > maxLength * 0.9) {
                charCount.classList.add('text-yellow-500');
                charCount.classList.remove('text-yellow-400', 'text-gray-500');
            } else if (currentLength > maxLength * 0.75) {
                charCount.classList.add('text-yellow-400');
                charCount.classList.remove('text-yellow-500', 'text-gray-500');
            } else {
                charCount.classList.add('text-gray-500');
                charCount.classList.remove('text-yellow-400', 'text-yellow-500');
            }
        }
    });

    // Handle Enter key press for message input
    messageInput.addEventListener('keydown', function(e) {
        // Send message on Ctrl+Enter or Cmd+Enter
        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
            e.preventDefault();
            
            if (!sendButton.disabled) {
                document.getElementById('messageForm').submit();
            }
        }
        
        // Auto resize the textarea based on content
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });
    
    // Initialize on page load
    handleRecipientTypeChange();
    
    // Handle file uploads and preview with modern UI
    fileInput.addEventListener('change', function() {
        filePreviewList.innerHTML = '';
        
        if (this.files.length > 0) {
            filePreviewContainer.style.display = 'block';
            
            // Animate file container
            filePreviewContainer.classList.add('animate-fade-in-up');
            setTimeout(() => {
                filePreviewContainer.classList.remove('animate-fade-in-up');
            }, 500);
            
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
                let color = 'text-gray-500';
                const extension = file.name.split('.').pop().toLowerCase();
                
                if (['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'].includes(extension)) {
                    icon = 'fa-file-image';
                    color = 'text-blue-500';
                } else if (['doc', 'docx'].includes(extension)) {
                    icon = 'fa-file-word';
                    color = 'text-blue-600';
                } else if (['xls', 'xlsx', 'csv'].includes(extension)) {
                    icon = 'fa-file-excel';
                    color = 'text-green-600';
                } else if (extension === 'pdf') {
                    icon = 'fa-file-pdf';
                    color = 'text-red-500';
                } else if (['zip', 'rar', '7z'].includes(extension)) {
                    icon = 'fa-file-archive';
                    color = 'text-yellow-600';
                } else if (['mp3', 'wav', 'ogg'].includes(extension)) {
                    icon = 'fa-file-audio';
                    color = 'text-purple-500';
                } else if (['mp4', 'avi', 'mov', 'wmv'].includes(extension)) {
                    icon = 'fa-file-video';
                    color = 'text-pink-500';
                } else if (['txt', 'rtf'].includes(extension)) {
                    icon = 'fa-file-alt';
                    color = 'text-gray-600';
                }
                
                // Create list item for preview with animation delay based on index
                const li = document.createElement('li');
                li.className = 'relative py-3 px-4 flex items-center bg-gray-50 hover:bg-gray-100 rounded-lg border border-gray-100 transition-all duration-300 animate-fade-in-up';
                li.style.animationDelay = `${index * 50}ms`;
                
                li.innerHTML = `
                    <div class="flex-shrink-0 ${color} mr-4">
                        <i class="fas ${icon} text-xl"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-gray-900 truncate group-hover:text-primary-600">${file.name}</p>
                        <p class="text-xs text-gray-500">${sizeDisplay}</p>
                    </div>
                    <div class="ml-4 flex-shrink-0">
                        <button type="button" class="bg-white rounded-md text-gray-400 hover:text-red-500 focus:outline-none transition-colors duration-200 p-1 hover:bg-red-50" onclick="removeFile(${index})">
                            <span class="sr-only">Remove file</span>
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;
                
                filePreviewList.appendChild(li);
            });
            
            // Update help text
            document.getElementById('fileHelp').textContent = `${this.files.length} file${this.files.length > 1 ? 's' : ''} selected`;
        } else {
            filePreviewContainer.style.display = 'none';
            document.getElementById('fileHelp').textContent = 'Upload up to 5 files (10MB max per file)';
        }
    });
    
    // Show message type options if related to an order
    orderSelect.addEventListener('change', function() {
        if (recipientTypeWriter.checked) {
            messageTypeContainer.classList.remove('hidden');
            setTimeout(() => {
                messageTypeContainer.classList.remove('scale-95', 'opacity-0');
            }, 10);
        }
    });
    
    // Add ripple effect to buttons
    const buttons = document.querySelectorAll('button:not([type="submit"]), .btn');
    buttons.forEach(button => {
        button.addEventListener('click', function(e) {
            const rect = button.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            const ripple = document.createElement('span');
            ripple.classList.add('ripple-effect');
            ripple.style.left = `${x}px`;
            ripple.style.top = `${y}px`;
            
            button.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });
    
    // Handle emoji picker toggle
    const emojiButton = document.getElementById('emoji-button');
    const emojiPicker = document.getElementById('emoji-picker');
    
    if (emojiButton && emojiPicker) {
        emojiButton.addEventListener('click', function() {
            emojiPicker.classList.toggle('hidden');
        });
        
        // Close emoji picker when clicking outside
        document.addEventListener('click', function(e) {
            if (!emojiButton.contains(e.target) && !emojiPicker.contains(e.target)) {
                emojiPicker.classList.add('hidden');
            }
        });
        
        // Handle emoji selection
        const emojis = document.querySelectorAll('.emoji');
        emojis.forEach(emoji => {
            emoji.addEventListener('click', function() {
                const messageInput = document.getElementById('message');
                messageInput.value += emoji.textContent;
                messageInput.focus();
                // Trigger input event to update character count
                messageInput.dispatchEvent(new Event('input'));
                
                // Auto resize the textarea
                messageInput.style.height = 'auto';
                messageInput.style.height = (messageInput.scrollHeight) + 'px';
                
                // Hide picker after selection
                emojiPicker.classList.add('hidden');
            });
        });
    }
});

// Function to remove a file from the input
function removeFile(index) {
    const fileInput = document.getElementById('files');
    const dt = new DataTransfer();
    const files = fileInput.files;
    
    for (let i = 0; i < files.length; i++) {
        if (i !== index) {
            dt.items.add(files[i]);
        }
    }
    
    fileInput.files = dt.files;
    
    // Update the file preview with animation
    const fileItem = document.getElementById('filePreviewList').children[index];
    fileItem.classList.add('opacity-0', 'scale-95');
    
    setTimeout(() => {
        // Update the file preview
        fileInput.dispatchEvent(new Event('change'));
    }, 300);
}
</script>

@endpush