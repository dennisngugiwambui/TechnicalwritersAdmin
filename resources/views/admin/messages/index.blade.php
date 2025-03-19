@extends('admin.app')

@section('title', 'Messages')

@section('page-title', 'Messages')

@section('content')
<div class="h-[calc(100vh-14rem)] bg-white rounded-lg shadow-sm overflow-hidden">
    <div class="flex h-full">
        <!-- Left Sidebar (Conversations) -->
        <div class="w-1/3 border-r border-gray-200 flex flex-col">
            <!-- Sidebar Header with New Message Button -->
            <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                <h2 class="text-lg font-medium text-gray-900">Messages</h2>
                <a href="{{ route('admin.messages.create') }}" class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                    <i class="fas fa-plus mr-1.5"></i> New Message
                </a>
            </div>
            
            <!-- Search Box -->
            <div class="p-3 border-b border-gray-200">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" id="searchConversations" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm" placeholder="Search messages...">
                </div>
            </div>
            
            <!-- Filter Tabs -->
            <div class="flex border-b border-gray-200">
                <button onclick="filterMessages('all')" class="flex-1 py-3 text-sm font-medium border-b-2 border-primary-500 text-primary-600">
                    All
                </button>
                <button onclick="filterMessages('unread')" class="flex-1 py-3 text-sm font-medium text-gray-500 hover:text-gray-700">
                    Unread 
                    @if($unreadCount > 0)
                        <span class="ml-1 bg-red-100 text-red-600 px-2 py-0.5 rounded-full text-xs">{{ $unreadCount }}</span>
                    @endif
                </button>
                <button onclick="filterMessages('orders')" class="flex-1 py-3 text-sm font-medium text-gray-500 hover:text-gray-700">
                    Orders
                </button>
            </div>
            
            <!-- Conversations List -->
            <div class="overflow-y-auto flex-1">
                <ul class="divide-y divide-gray-200" id="conversationsList">
                    @forelse($conversations as $conversation)
                        <li class="conversation-item" data-unread="{{ $conversation['unread_count'] > 0 ? 'true' : 'false' }}" data-has-order="{{ !empty($conversation['order_id']) ? 'true' : 'false' }}">
                            <a href="{{ route('admin.messages.index', ['conversation' => $conversation['id']]) }}" class="block px-4 py-3 hover:bg-gray-50 transition duration-150 ease-in-out {{ request('conversation') == $conversation['id'] ? 'bg-gray-50' : '' }}">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 rounded-full bg-{{ $conversation['participant']->usertype == 'writer' ? 'green' : 'blue' }}-100 flex items-center justify-center">
                                        @if($conversation['participant']->profile_picture)
                                            <img class="h-10 w-10 rounded-full object-cover" src="{{ asset($conversation['participant']->profile_picture) }}" alt="{{ $conversation['participant']->name }}">
                                        @else
                                            <span class="text-{{ $conversation['participant']->usertype == 'writer' ? 'green' : 'blue' }}-600 font-medium text-sm">{{ strtoupper(substr($conversation['participant']->name, 0, 1)) }}</span>
                                        @endif
                                    </div>
                                    <div class="ml-3 flex-1 min-w-0">
                                        <div class="flex items-center justify-between">
                                            <p class="text-sm font-medium text-gray-900 truncate">
                                                {{ $conversation['participant']->name }}
                                                <span class="ml-1 text-xs text-gray-500">({{ ucfirst($conversation['participant']->usertype) }})</span>
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                {{ $conversation['last_message_at']->diffForHumans() }}
                                            </p>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <p class="text-xs text-gray-500 truncate">
                                                @if($conversation['order'])
                                                    Order #{{ $conversation['order']->id }}
                                                @else
                                                    General Message
                                                @endif
                                            </p>
                                            @if($conversation['unread_count'] > 0)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    {{ $conversation['unread_count'] }}
                                                </span>
                                            @endif
                                        </div>
                                        <p class="mt-1 text-sm text-gray-600 truncate">
                                            {{ Str::limit($conversation['last_message'], 60) }}
                                        </p>
                                    </div>
                                </div>
                            </a>
                        </li>
                    @empty
                        <li class="py-8 text-center">
                            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-gray-100">
                                <i class="fas fa-inbox text-gray-400"></i>
                            </div>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No messages</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Get started by creating a new message.
                            </p>
                            <div class="mt-6">
                                <a href="{{ route('admin.messages.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                    <i class="fas fa-plus mr-2"></i> New Message
                                </a>
                            </div>
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
        
        <!-- Right Content (Message Thread) -->
        <div class="w-2/3 flex flex-col">
            @if(isset($currentConversation) && $currentConversation)
                <!-- Conversation Header -->
                <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0 h-10 w-10 rounded-full bg-{{ $currentConversation['participant']->usertype == 'writer' ? 'green' : 'blue' }}-100 flex items-center justify-center">
                            @if($currentConversation['participant']->profile_picture)
                                <img class="h-10 w-10 rounded-full object-cover" src="{{ asset($currentConversation['participant']->profile_picture) }}" alt="{{ $currentConversation['participant']->name }}">
                            @else
                                <span class="text-{{ $currentConversation['participant']->usertype == 'writer' ? 'green' : 'blue' }}-600 font-medium text-sm">{{ strtoupper(substr($currentConversation['participant']->name, 0, 1)) }}</span>
                            @endif
                        </div>
                        <div>
                            <h2 class="text-base font-medium text-gray-900">{{ $currentConversation['participant']->name }}</h2>
                            <p class="text-xs text-gray-500">
                                @if($currentConversation['order'])
                                    <a href="{{ route('admin.orders.show', $currentConversation['order']->id) }}" class="hover:underline text-primary-600">
                                        Order #{{ $currentConversation['order']->id }}: {{ Str::limit($currentConversation['order']->title, 40) }}
                                    </a>
                                @else
                                    General Message
                                @endif
                            </p>
                        </div>
                    </div>
                    
                    <div class="flex space-x-2">
                        @if($currentConversation['order'])
                        <a href="{{ route('admin.orders.show', $currentConversation['order']->id) }}" class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                            <i class="fas fa-external-link-alt mr-1.5"></i> View Order
                        </a>
                        @endif
                    </div>
                </div>
                
                <!-- Messages Thread -->
                <div class="flex-1 overflow-y-auto p-4 space-y-4" id="messageThread">
                    @foreach($messages->sortByDesc('created_at') as $message)
                        <div class="flex {{ $message->isSentByCurrentUser() ? 'justify-end' : 'justify-start' }}">
                            @if(!$message->isSentByCurrentUser())
                                <div class="flex-shrink-0 h-8 w-8 rounded-full bg-{{ $message->isFromClient() ? 'blue' : ($message->isFromWriter() ? 'green' : 'gray') }}-100 flex items-center justify-center mr-2">
                                    @if($message->user && $message->user->profile_picture)
                                        <img class="h-8 w-8 rounded-full object-cover" src="{{ asset($message->user->profile_picture) }}" alt="{{ $message->user->name }}">
                                    @else
                                        <span class="text-{{ $message->isFromClient() ? 'blue' : ($message->isFromWriter() ? 'green' : 'gray') }}-600 font-medium text-xs">
                                            {{ $message->getSenderInitial() }}
                                        </span>
                                    @endif
                                </div>
                            @endif
                            
                            <div class="{{ $message->isSentByCurrentUser() ? 'bg-primary-50 text-gray-900' : 'bg-gray-100 text-gray-800' }} rounded-lg px-4 py-2 max-w-md">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="font-medium text-sm">
                                        @if($message->isSentByCurrentUser())
                                            You 
                                            @if($message->message_type == 'client')
                                                <span class="text-xs font-normal text-blue-600">(as Client)</span>
                                            @endif
                                        @else
                                            {{ $message->user->name ?? 'Unknown' }}
                                            <span class="text-xs font-normal text-gray-500">({{ ucfirst($message->message_type) }})</span>
                                        @endif
                                    </span>
                                    <span class="text-xs text-gray-500">{{ $message->created_at->format('M d, g:i A') }}</span>
                                </div>
                                
                                @if($message->title)
                                    <div class="font-medium text-sm mb-1">{{ $message->title }}</div>
                                @endif
                                
                                <div class="text-sm whitespace-pre-wrap">{{ $message->message }}</div>
                                
                                @if($message->files->count() > 0)
                                    <div class="mt-2 pt-2 border-t border-gray-200">
                                        @foreach($message->files as $file)
                                            <div class="flex items-center text-xs mt-1">
                                                <i class="fas fa-paperclip text-gray-500 mr-1"></i>
                                                <a href="{{ route('files.download', $file->id) }}" class="text-primary-600 hover:underline">
                                                    {{ $file->name }}
                                                </a>
                                                <span class="ml-1 text-gray-500">({{ number_format($file->size/1024, 1) }} KB)</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                
                                @if($message->read_at && $message->isSentByCurrentUser())
                                    <div class="text-right mt-1">
                                        <span class="text-xs text-gray-500">Read {{ $message->read_at->format('M d, g:i A') }}</span>
                                    </div>
                                @endif
                            </div>
                            
                            @if($message->isSentByCurrentUser())
                                <div class="flex-shrink-0 h-8 w-8 rounded-full bg-primary-100 flex items-center justify-center ml-2">
                                    @if(Auth::user()->profile_picture)
                                        <img class="h-8 w-8 rounded-full object-cover" src="{{ asset(Auth::user()->profile_picture) }}" alt="{{ Auth::user()->name }}">
                                    @else
                                        <span class="text-primary-600 font-medium text-xs">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
                
                <!-- Message Input Form -->
                <div class="border-t border-gray-200 p-4">
                    <form action="{{ route('admin.messages.reply', $currentConversation['id']) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="space-y-3">
                            <div>
                                <textarea name="message" id="message" rows="3" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" placeholder="Type your message here..." required></textarea>
                            </div>
                            
                            <div class="flex justify-between items-center">
                                <div class="flex items-center space-x-4">
                                    <div class="relative">
                                        <input type="file" name="files[]" id="files" multiple class="absolute inset-0 opacity-0 w-8 h-8 cursor-pointer" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.png,.gif">
                                        <button type="button" class="p-2 text-gray-400 hover:text-gray-600 rounded-full hover:bg-gray-100">
                                            <i class="fas fa-paperclip"></i>
                                        </button>
                                        <span id="selectedFilesCount" class="hidden ml-1 text-xs text-gray-500"></span>
                                    </div>
                                    
                                    @if($currentConversation['participant']->usertype == 'writer')
                                    <select name="message_type" class="text-sm rounded-md border-gray-300 py-1 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                        <option value="admin">Send as Admin</option>
                                        <option value="client">Send as Client</option>
                                    </select>
                                    @endif
                                </div>
                                
                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                    <i class="fas fa-paper-plane mr-2"></i> Send
                                </button>
                            </div>
                            
                            <div id="filePreviewContainer" class="hidden border-t border-gray-200 pt-3 mt-3">
                                <div class="text-xs font-medium text-gray-700 mb-2">Attached Files:</div>
                                <ul id="filePreviewList" class="text-xs space-y-1"></ul>
                            </div>
                        </div>
                    </form>
                </div>
            @else
                <!-- Empty State -->
                <div class="flex-1 flex flex-col items-center justify-center p-8 text-center">
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-gray-100 mb-4">
                        <i class="fas fa-comments text-gray-400 text-xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">No conversation selected</h3>
                    <p class="mt-2 text-sm text-gray-500 max-w-md">
                        Select a conversation from the list to view messages or start a new conversation.
                    </p>
                    <div class="mt-6">
                        <a href="{{ route('admin.messages.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                            <i class="fas fa-plus mr-2"></i> New Message
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // File upload preview
    const fileInput = document.getElementById('files');
    const filePreviewContainer = document.getElementById('filePreviewContainer');
    const filePreviewList = document.getElementById('filePreviewList');
    const selectedFilesCount = document.getElementById('selectedFilesCount');
    
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            const files = this.files;
            
            if (files.length > 0) {
                // Update count badge
                selectedFilesCount.textContent = `${files.length} file${files.length > 1 ? 's' : ''}`;
                selectedFilesCount.classList.remove('hidden');
                
                // Clear and show preview list
                filePreviewList.innerHTML = '';
                filePreviewContainer.classList.remove('hidden');
                
                // Add preview for each file
                Array.from(files).forEach(file => {
                    const li = document.createElement('li');
                    li.classList.add('flex', 'items-center');
                    
                    // Icon based on file type
                    let iconClass = 'fa-file';
                    const extension = file.name.split('.').pop().toLowerCase();
                    
                    if (['jpg', 'jpeg', 'png', 'gif'].includes(extension)) {
                        iconClass = 'fa-file-image';
                    } else if (['doc', 'docx'].includes(extension)) {
                        iconClass = 'fa-file-word';
                    } else if (['xls', 'xlsx'].includes(extension)) {
                        iconClass = 'fa-file-excel';
                    } else if (extension === 'pdf') {
                        iconClass = 'fa-file-pdf';
                    }
                    
                    li.innerHTML = `
                        <i class="fas ${iconClass} text-gray-500 mr-1"></i>
                        <span class="text-gray-800">${file.name}</span>
                        <span class="ml-1 text-gray-500">(${(file.size / 1024).toFixed(1)} KB)</span>
                    `;
                    
                    filePreviewList.appendChild(li);
                });
            } else {
                // Hide preview and count
                filePreviewContainer.classList.add('hidden');
                selectedFilesCount.classList.add('hidden');
            }
        });
    }
    
    // Scroll to the top of message thread (since messages are newest first)
    const messageThread = document.getElementById('messageThread');
    if (messageThread) {
        messageThread.scrollTop = 0;
    }
});

// Function to filter messages based on type
function filterMessages(type) {
    const conversationItems = document.querySelectorAll('.conversation-item');
    const allButton = document.querySelector('button[onclick="filterMessages(\'all\')"]');
    const unreadButton = document.querySelector('button[onclick="filterMessages(\'unread\')"]');
    const ordersButton = document.querySelector('button[onclick="filterMessages(\'orders\')"]');
    
    // Update active button styling
    allButton.classList.remove('border-b-2', 'border-primary-500', 'text-primary-600');
    unreadButton.classList.remove('border-b-2', 'border-primary-500', 'text-primary-600');
    ordersButton.classList.remove('border-b-2', 'border-primary-500', 'text-primary-600');
    
    allButton.classList.add('text-gray-500', 'hover:text-gray-700');
    unreadButton.classList.add('text-gray-500', 'hover:text-gray-700');
    ordersButton.classList.add('text-gray-500', 'hover:text-gray-700');
    
    // Set active button
    if (type === 'all') {
        allButton.classList.add('border-b-2', 'border-primary-500', 'text-primary-600');
        allButton.classList.remove('text-gray-500', 'hover:text-gray-700');
    } else if (type === 'unread') {
        unreadButton.classList.add('border-b-2', 'border-primary-500', 'text-primary-600');
        unreadButton.classList.remove('text-gray-500', 'hover:text-gray-700');
    } else if (type === 'orders') {
        ordersButton.classList.add('border-b-2', 'border-primary-500', 'text-primary-600');
        ordersButton.classList.remove('text-gray-500', 'hover:text-gray-700');
    }
    
    // Filter items
    conversationItems.forEach(item => {
        if (type === 'all') {
            item.style.display = '';
        } else if (type === 'unread') {
            item.style.display = item.dataset.unread === 'true' ? '' : 'none';
        } else if (type === 'orders') {
            item.style.display = item.dataset.hasOrder === 'true' ? '' : 'none';
        }
    });
}
</script>
@endpush