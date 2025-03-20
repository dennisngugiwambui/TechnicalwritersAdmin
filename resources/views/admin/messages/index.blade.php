@extends('admin.app')

@section('title', 'Messages')

@section('page-title', 'Messages')

@section('content')
<div class="h-full bg-white rounded-lg shadow-sm overflow-hidden">
    <div class="flex flex-col lg:flex-row h-[calc(100vh-10rem)]">
        <!-- Mobile Header (Only visible on small screens) -->
        <div class="lg:hidden flex justify-between items-center p-3 bg-white border-b sticky top-0 z-10">
            <button id="backToList" class="p-2 mr-2 text-gray-600 focus:outline-none focus:text-gray-900 hover:bg-gray-100 rounded-full active:bg-gray-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </button>
            
            @if(isset($currentConversation) && $currentConversation)
            <div class="flex items-center">
                <h2 class="text-base font-medium text-gray-900">{{ $currentConversation['participant']->name }}</h2>
                @if($currentConversation['order'])
                    <a href="{{ route('admin.orders.show', $currentConversation['order']->id) }}" class="ml-2 text-xs text-primary-600 hover:underline">
                        #{{ $currentConversation['order']->id }}
                    </a>
                @endif
            </div>
            @else
            <div class="text-base font-medium text-gray-900">Messages</div>
            @endif
            
            <a href="{{ route('admin.messages.create') }}" class="p-1.5 text-primary-600 hover:text-primary-700 focus:outline-none">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
            </a>
        </div>

        <!-- Left Sidebar (Conversations) -->
        <div id="sidebar" class="w-full lg:w-80 xl:w-96 border-r flex flex-col bg-white lg:block transform transition-transform duration-300 ease-in-out" 
             style="display: {{ isset($currentConversation) && !request()->is('admin/messages/create') ? 'none' : 'flex' }};">
            <!-- Sidebar Header with New Message Button (Desktop) -->
            <div class="hidden lg:flex p-4 border-b justify-between items-center bg-white">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-primary-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                    </svg>
                    <h2 class="text-lg font-medium text-gray-900">Messages</h2>
                </div>
                <a href="{{ route('admin.messages.create') }}" class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-full shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    New Message
                </a>
            </div>
            
            <!-- Search Box -->
            <div class="p-3 border-b bg-white">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" id="searchConversations" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-full leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm" placeholder="Search name, ID or order #...">
                </div>
            </div>
            
            <!-- Filter Tabs -->
            <div class="flex border-b bg-white">
                <button onclick="filterMessages('all')" class="flex-1 py-3 text-sm font-medium border-b-2 border-primary-500 text-primary-600 relative">
                    All
                </button>
                <button onclick="filterMessages('unread')" class="flex-1 py-3 text-sm font-medium text-gray-500 hover:text-gray-700 relative">
                    Unread 
                    @if($unreadCount > 0)
                        <span class="ml-1 absolute -top-1 -right-1 bg-red-500 text-white w-5 h-5 rounded-full flex items-center justify-center text-xs">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
                    @endif
                </button>
                <button onclick="filterMessages('orders')" class="flex-1 py-3 text-sm font-medium text-gray-500 hover:text-gray-700">
                    Orders
                </button>
            </div>
            
            <!-- Conversations List -->
            <div class="overflow-y-auto flex-1 bg-white">
                <ul class="divide-y divide-gray-100" id="conversationsList">
                    @forelse($conversations->sortByDesc('last_message_at') as $conversation)
                        <li class="conversation-item" 
                            data-unread="{{ $conversation['unread_count'] > 0 ? 'true' : 'false' }}" 
                            data-has-order="{{ !empty($conversation['order_id']) ? 'true' : 'false' }}"
                            data-name="{{ $conversation['participant']->name }}"
                            data-id="{{ $conversation['participant']->id }}"
                            data-order="{{ $conversation['order_id'] ?? '' }}">
                            <a href="{{ route('admin.messages.index', ['conversation' => $conversation['id']]) }}" 
                               class="block hover:bg-gray-50 transition duration-150 ease-in-out {{ request('conversation') == $conversation['id'] ? 'bg-primary-50 border-l-4 border-primary-500' : '' }}"
                               data-conversation-id="{{ $conversation['id'] }}">
                                <div class="flex items-center px-4 py-3">
                                    <div class="flex-shrink-0 h-12 w-12 rounded-full bg-{{ $conversation['participant']->usertype == 'writer' ? 'green' : 'blue' }}-100 flex items-center justify-center relative">
                                        @if($conversation['participant']->profile_picture)
                                            <img class="h-12 w-12 rounded-full object-cover" src="{{ asset($conversation['participant']->profile_picture) }}" alt="{{ $conversation['participant']->name }}">
                                        @else
                                            <span class="text-{{ $conversation['participant']->usertype == 'writer' ? 'green' : 'blue' }}-600 font-medium text-lg">{{ strtoupper(substr($conversation['participant']->name, 0, 1)) }}</span>
                                        @endif
                                        
                                        @if($conversation['unread_count'] > 0)
                                            <div class="absolute -top-1 -right-1 w-5 h-5 rounded-full bg-red-500 text-white text-xs flex items-center justify-center">
                                                {{ $conversation['unread_count'] > 9 ? '9+' : $conversation['unread_count'] }}
                                            </div>
                                        @endif
                                        
                                        <div class="absolute bottom-0 right-0 w-3.5 h-3.5 rounded-full {{ $conversation['participant']->is_online ? 'bg-green-500' : 'bg-gray-300' }} border-2 border-white"></div>
                                    </div>
                                    <div class="ml-3 flex-1 min-w-0">
                                        <div class="flex items-center justify-between">
                                            <p class="text-sm font-semibold text-gray-900 truncate">
                                                {{ $conversation['participant']->name }}
                                            </p>
                                            <p class="text-xs text-gray-500 whitespace-nowrap">
                                                {{ formatMessageTime($conversation['last_message_at']) }}
                                            </p>
                                        </div>
                                        <div class="flex items-center text-xs mt-0.5">
                                            <span class="text-gray-500">ID: {{ $conversation['participant']->id }}</span>
                                            @if($conversation['order'])
                                                <span class="mx-1 text-gray-400">•</span>
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs bg-gray-100 text-gray-800">
                                                    #{{ $conversation['order']->id }}
                                                </span>
                                            @endif
                                        </div>
                                        <div class="mt-1">
                                            <p class="text-sm {{ $conversation['unread_count'] > 0 ? 'text-gray-900 font-medium' : 'text-gray-500 font-normal' }} truncate pr-2">
                                                {{ Str::limit($conversation['last_message'], 35) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </li>
                    @empty
                        <li class="py-12 text-center">
                            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-gray-100">
                                <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                </svg>
                            </div>
                            <h3 class="mt-4 text-base font-medium text-gray-900">No messages</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Get started by creating a new message.
                            </p>
                            <div class="mt-6">
                                <a href="{{ route('admin.messages.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-full text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    New Message
                                </a>
                            </div>
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
        
        <!-- Right Content (Message Thread) -->
        <div id="messageContent" class="w-full lg:flex-1 flex flex-col bg-white" 
             style="display: {{ isset($currentConversation) && !request()->is('admin/messages/create') ? 'flex' : 'none' }};">
            @if(isset($currentConversation) && $currentConversation)
                <!-- Conversation Header -->
                <div class="p-3 border-b flex justify-between items-center sticky top-0 bg-white z-10">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10 rounded-full bg-{{ $currentConversation['participant']->usertype == 'writer' ? 'green' : 'blue' }}-100 flex items-center justify-center relative">
                            @if($currentConversation['participant']->profile_picture)
                                <img class="h-10 w-10 rounded-full object-cover" src="{{ asset($currentConversation['participant']->profile_picture) }}" alt="{{ $currentConversation['participant']->name }}">
                            @else
                                <span class="text-{{ $currentConversation['participant']->usertype == 'writer' ? 'green' : 'blue' }}-600 font-medium text-sm">{{ strtoupper(substr($currentConversation['participant']->name, 0, 1)) }}</span>
                            @endif
                        </div>
                        <div class="ml-3">
                            <h2 class="text-base font-medium text-gray-900">{{ $currentConversation['participant']->name }}</h2>
                            @if($currentConversation['order'])
                                <a href="{{ route('admin.orders.show', $currentConversation['order']->id) }}" class="text-xs text-primary-600 hover:underline">
                                    Order #{{ $currentConversation['order']->id }}
                                </a>
                            @endif
                        </div>
                    </div>
                    
                    <div class="flex space-x-1">
                        @if($currentConversation['order'])
                        <a href="{{ route('admin.orders.show', $currentConversation['order']->id) }}" class="inline-flex items-center px-2 py-1 border border-gray-300 shadow-sm text-xs font-medium rounded-full text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
                            <span class="hidden sm:inline mr-1">View</span> Order
                        </a>
                        @endif
                        <button type="button" class="p-1.5 text-gray-400 hover:text-gray-600 rounded-full hover:bg-gray-100 focus:outline-none">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <!-- Message Container With Absolute Positioned Input -->
                <div class="relative flex-1 overflow-hidden flex flex-col">
                    <!-- Date Separators and Messages -->
                    <div class="absolute inset-0 overflow-y-auto pb-20 md:pb-24" id="messageThread">
                        <div class="min-h-full flex flex-col justify-end">
                            <div class="space-y-4" id="messagesContainer">
                                <!-- Date Separator -->
                                @php
                                    $currentDate = null;
                                    $messages = $messages->sortBy('created_at');
                                @endphp
                                
                                @foreach($messages as $message)
                                    @php
                                        $messageDate = $message->created_at->format('Y-m-d');
                                        $showDateSeparator = $currentDate !== $messageDate;
                                        $currentDate = $messageDate;
                                    @endphp
                                    
                                    @if($showDateSeparator)
                                        <div class="flex items-center justify-center my-4">
                                            <div class="bg-gray-200 px-3 py-1 rounded-full">
                                                <span class="text-xs text-gray-600">
                                                    @if($message->created_at->isToday())
                                                        Today
                                                    @elseif($message->created_at->isYesterday())
                                                        Yesterday
                                                    @else
                                                        {{ $message->created_at->format('F j, Y') }}
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    <div class="flex {{ $message->isSentByCurrentUser() ? 'justify-end' : 'justify-start' }} px-4" id="message-{{ $message->id }}">
                                        @if(!$message->isSentByCurrentUser())
                                            <div class="flex-shrink-0 h-8 w-8 rounded-full bg-{{ $message->isFromClient() ? 'blue' : ($message->isFromWriter() ? 'green' : 'gray') }}-100 flex items-center justify-center mr-2 self-end">
                                                @if($message->user && $message->user->profile_picture)
                                                    <img class="h-8 w-8 rounded-full object-cover" src="{{ asset($message->user->profile_picture) }}" alt="{{ $message->user->name }}">
                                                @else
                                                    <span class="text-{{ $message->isFromClient() ? 'blue' : ($message->isFromWriter() ? 'green' : 'gray') }}-600 font-medium text-xs">
                                                        {{ $message->getSenderInitial() }}
                                                    </span>
                                                @endif
                                            </div>
                                        @endif
                                        
                                        <div class="{{ $message->isSentByCurrentUser() ? 'bg-purple-600 text-white rounded-tl-2xl rounded-tr-sm rounded-br-2xl rounded-bl-2xl' : 'bg-white text-gray-800 rounded-tr-2xl rounded-tl-sm rounded-bl-2xl rounded-br-2xl border border-gray-200' }} px-4 py-2 max-w-[75%] md:max-w-[60%] shadow-sm">
                                            <div class="flex justify-between items-center mb-1">
                                                <span class="font-medium text-xs {{ $message->isSentByCurrentUser() ? 'text-white' : 'text-gray-900' }}">
                                                    @if($message->isSentByCurrentUser())
                                                        You 
                                                        @if($message->message_type == 'client')
                                                            <span class="text-xs font-normal text-purple-100">(as Client)</span>
                                                        @endif
                                                    @else
                                                        {{ $message->user->name ?? 'Unknown' }}
                                                        <span class="text-xs font-normal text-gray-500">({{ ucfirst($message->message_type) }})</span>
                                                    @endif
                                                </span>
                                                <span class="text-xs {{ $message->isSentByCurrentUser() ? 'text-purple-100' : 'text-gray-500' }}">
                                                    {{ $message->created_at->format('g:i A') }}
                                                </span>
                                            </div>
                                            
                                            @if($message->title)
                                                <div class="font-medium text-sm mb-1 {{ $message->isSentByCurrentUser() ? 'text-white' : 'text-gray-900' }}">{{ $message->title }}</div>
                                            @endif
                                            
                                            <div class="text-sm whitespace-pre-wrap {{ $message->isSentByCurrentUser() ? 'text-white' : 'text-gray-800' }}">{{ $message->message }}</div>
                                            
                                            @if($message->files->count() > 0)
                                                <div class="mt-2 pt-2 border-t {{ $message->isSentByCurrentUser() ? 'border-purple-500' : 'border-gray-200' }}">
                                                    @foreach($message->files as $file)
                                                        <div class="flex items-center text-xs mt-1">
                                                            <svg class="w-4 h-4 {{ $message->isSentByCurrentUser() ? 'text-purple-100' : 'text-gray-500' }} mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                                            </svg>
                                                            <a href="{{ route('files.download', $file->id) }}" class="{{ $message->isSentByCurrentUser() ? 'text-purple-100' : 'text-primary-600' }} hover:underline truncate max-w-[150px] sm:max-w-none">
                                                                {{ $file->name }}
                                                            </a>
                                                            <span class="ml-1 {{ $message->isSentByCurrentUser() ? 'text-purple-100' : 'text-gray-500' }} hidden sm:inline">({{ number_format($file->size/1024, 1) }} KB)</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                            
                                            @if($message->isSentByCurrentUser())
                                                <div class="text-right mt-1 flex justify-end">
                                                    @if($message->read_at)
                                                    <span class="text-xs text-purple-100 flex items-center">
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                        </svg>
                                                        Seen
                                                    </span>
                                                    @else
                                                    <span class="text-xs text-purple-100 flex items-center">
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                        </svg>
                                                        Sent
                                                    </span>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                        
                                        @if($message->isSentByCurrentUser())
                                            <div class="flex-shrink-0 h-8 w-8 rounded-full bg-purple-100 flex items-center justify-center ml-2 self-end">
                                                @if(Auth::user()->profile_picture)
                                                    <img class="h-8 w-8 rounded-full object-cover" src="{{ asset(Auth::user()->profile_picture) }}" alt="{{ Auth::user()->name }}">
                                                @else
                                                    <span class="text-purple-600 font-medium text-xs">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                
                    <!-- Message Input Form - Fixed at Bottom -->
                    <div class="absolute bottom-0 left-0 right-0 border-t border-gray-200 p-3 bg-white z-10">
                        <form id="messageForm" action="{{ route('admin.messages.reply', $currentConversation['id']) }}" method="POST" enctype="multipart/form-data" class="message-form">
                            @csrf
                            <div class="space-y-3">
                                <div class="relative">
                                    <textarea name="message" id="message" rows="1" class="block w-full rounded-2xl border-gray-300 shadow-sm focus:border-green-500 focus:ring-2 focus:ring-green-200 sm:text-sm pr-16" placeholder="Type your message here..." required></textarea>
                                    <div class="absolute bottom-2 right-3 flex items-center space-x-2">
                                        <div class="relative">
                                            <input type="file" name="files[]" id="files" multiple class="absolute inset-0 opacity-0 w-8 h-8 cursor-pointer z-10" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.png,.gif">
                                            <button type="button" class="p-1.5 text-gray-400 hover:text-gray-600 rounded-full hover:bg-gray-100 focus:outline-none">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                                </svg>
                                            </button>
                                            <span id="selectedFilesCount" class="hidden absolute -top-8 right-0 text-xs text-gray-500 bg-white px-2 py-1 rounded-lg shadow-sm border"></span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-2">
                                        @if(isset($currentConversation) && $currentConversation['participant']->usertype == 'writer')
                                        <div class="relative inline-block">
                                            <select name="message_type" class="text-sm rounded-full border-gray-300 py-1 pl-3 pr-8 shadow-sm focus:border-primary-500 focus:ring-primary-500 bg-gray-50">
                                                <option value="admin">Send as Admin</option>
                                                <option value="client">Send as Client</option>
                                            </select>
                                        </div>
                                        @endif
                                        <div id="filePreviewContainer" class="hidden">
                                            <button type="button" id="toggleFilePreview" class="inline-flex items-center text-xs text-gray-600 hover:text-gray-900">
                                                <svg class="