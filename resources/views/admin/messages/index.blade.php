@extends('admin.app')

@section('title', 'Messages')

@section('page-title', 'Messages')

@section('content')
<div class="h-full bg-white rounded-lg shadow-sm overflow-hidden">
    <div class="flex flex-col lg:flex-row h-[calc(100vh-10rem)]">
        <!-- Mobile Header (Only visible on small screens) -->
        <div class="lg:hidden flex justify-between items-center p-3 bg-white border-b sticky top-0 z-10">
            <button id="toggleSidebar" class="p-1 text-gray-600 focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
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
                        <button id="backToList" class="lg:hidden p-1 text-gray-600 mr-2 focus:outline-none focus:text-gray-900">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                        </button>
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
                
                <!-- Messages Thread -->
                <div class="flex-1 overflow-y-auto p-4 bg-gray-50" id="messageThread">
                    <div class="min-h-full flex flex-col">
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
                                
                                <div class="flex {{ $message->isSentByCurrentUser() ? 'justify-end' : 'justify-start' }}" id="message-{{ $message->id }}">
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
                                    
                                    <div class="{{ $message->isSentByCurrentUser() ? 'bg-blue-600 text-white rounded-tl-2xl rounded-tr-sm rounded-br-2xl rounded-bl-2xl' : 'bg-white text-gray-800 rounded-tr-2xl rounded-tl-sm rounded-bl-2xl rounded-br-2xl border border-gray-200' }} px-4 py-2 max-w-[85%] sm:max-w-[70%] shadow-sm">
                                        <div class="flex justify-between items-center mb-1">
                                            <span class="font-medium text-xs {{ $message->isSentByCurrentUser() ? 'text-white' : 'text-gray-900' }}">
                                                @if($message->isSentByCurrentUser())
                                                    You 
                                                    @if($message->message_type == 'client')
                                                        <span class="text-xs font-normal text-blue-100">(as Client)</span>
                                                    @endif
                                                @else
                                                    {{ $message->user->name ?? 'Unknown' }}
                                                    <span class="text-xs font-normal text-gray-500">({{ ucfirst($message->message_type) }})</span>
                                                @endif
                                            </span>
                                            <span class="text-xs {{ $message->isSentByCurrentUser() ? 'text-blue-100' : 'text-gray-500' }}">
                                                {{ $message->created_at->format('g:i A') }}
                                            </span>
                                        </div>
                                        
                                        @if($message->title)
                                            <div class="font-medium text-sm mb-1 {{ $message->isSentByCurrentUser() ? 'text-white' : 'text-gray-900' }}">{{ $message->title }}</div>
                                        @endif
                                        
                                        <div class="text-sm whitespace-pre-wrap {{ $message->isSentByCurrentUser() ? 'text-white' : 'text-gray-800' }}">{{ $message->message }}</div>
                                        
                                        @if($message->files->count() > 0)
                                            <div class="mt-2 pt-2 border-t {{ $message->isSentByCurrentUser() ? 'border-blue-500' : 'border-gray-200' }}">
                                                @foreach($message->files as $file)
                                                    <div class="flex items-center text-xs mt-1">
                                                        <svg class="w-4 h-4 {{ $message->isSentByCurrentUser() ? 'text-blue-100' : 'text-gray-500' }} mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                                        </svg>
                                                        <a href="{{ route('files.download', $file->id) }}" class="{{ $message->isSentByCurrentUser() ? 'text-blue-100' : 'text-primary-600' }} hover:underline truncate max-w-[150px] sm:max-w-none">
                                                            {{ $file->name }}
                                                        </a>
                                                        <span class="ml-1 {{ $message->isSentByCurrentUser() ? 'text-blue-100' : 'text-gray-500' }} hidden sm:inline">({{ number_format($file->size/1024, 1) }} KB)</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                        
                                        @if($message->isSentByCurrentUser())
                                            <div class="text-right mt-1 flex justify-end">
                                                @if($message->read_at)
                                                <span class="text-xs text-blue-100 flex items-center">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                    </svg>
                                                    Seen
                                                </span>
                                                @else
                                                <span class="text-xs text-blue-100 flex items-center">
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
                                        <div class="flex-shrink-0 h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center ml-2 self-end">
                                            @if(Auth::user()->profile_picture)
                                                <img class="h-8 w-8 rounded-full object-cover" src="{{ asset(Auth::user()->profile_picture) }}" alt="{{ Auth::user()->name }}">
                                            @else
                                                <span class="text-blue-600 font-medium text-xs">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                
                <!-- Message Input Form -->
                <div class="border-t border-gray-200 p-3 bg-white">
                    <form id="messageForm" action="{{ route('admin.messages.reply', $currentConversation['id']) }}" method="POST" enctype="multipart/form-data" class="message-form">
                        @csrf
                        <div class="space-y-3">
                            <div class="relative">
                                <textarea name="message" id="message" rows="2" class="block w-full rounded-2xl border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm pr-16" placeholder="Type your message here..." required></textarea>
                                <div class="absolute bottom-3 right-3 flex items-center space-x-2">
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
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                            </svg>
                                            <span id="fileCountDisplay">0 files</span>
                                            <svg class="w-4 h-4 ml-1 transform transition-transform duration-200" id="filePreviewChevron" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                
                                <button type="submit" class="inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-full shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <span>Send</span>
                                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                    </svg>
                                </button>
                            </div>
                            
                            <div id="filePreviewList" class="hidden mt-2 p-3 bg-gray-50 rounded-lg border border-gray-200 text-xs space-y-2 max-h-32 overflow-y-auto"></div>
                        </div>
                    </form>
                </div>
            @else
                <!-- Empty State -->
                <div class="flex-1 flex flex-col items-center justify-center p-8 text-center">
                    <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-gray-100 mb-6">
                        <svg class="h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-medium text-gray-900">No conversation selected</h3>
                    <p class="mt-2 text-sm text-gray-500 max-w-md">
                        Select a conversation from the list to view messages or start a new conversation with a writer or client.
                    </p>
                    <div class="mt-8">
                        <a href="{{ route('admin.messages.create') }}" class="inline-flex items-center px-5 py-3 border border-transparent shadow-sm text-base font-medium rounded-full text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            New Message
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Create Message Modal -->
@if(request()->is('admin/messages/create'))
<div class="fixed inset-0 z-50 overflow-y-auto" id="createMessageModal">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block w-full max-w-lg p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white rounded-xl shadow-xl sm:align-middle">
            <div class="flex justify-between items-center border-b pb-4 mb-5">
                <h3 class="text-lg font-medium text-gray-900">New Message</h3>
                <a href="{{ route('admin.messages.index') }}" class="text-gray-400 hover:text-gray-500 focus:outline-none focus:text-gray-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </a>
            </div>
            
            <form action="{{ route('admin.messages.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="space-y-5">
                    <div>
                        <label for="recipient_type" class="block text-sm font-medium text-gray-700">Recipient Type</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <select id="recipient_type" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                                <option value="writer">Writer</option>
                                <option value="client">Client</option>
                            </select>
                        </div>
                    </div>
                    
                    <div>
                        <label for="receiver_id" class="block text-sm font-medium text-gray-700">Recipient</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <select name="receiver_id" id="receiver_id" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm" required>
                                <option value="">Select recipient...</option>
                                @foreach($writers as $writer)
                                    <option value="{{ $writer->id }}">{{ $writer->name }} - {{ $writer->email }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div>
                        <label for="order_id" class="block text-sm font-medium text-gray-700">Related Order (Optional)</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <select name="order_id" id="order_id" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                                <option value="">No related order</option>
                                @foreach($orders as $order)
                                    <option value="{{ $order->id }}">#{{ $order->id }} - {{ Str::limit($order->title, 50) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700">Subject (Optional)</label>
                        <div class="mt-1">
                            <input type="text" name="title" id="title" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm" placeholder="Message subject">
                        </div>
                    </div>
                    
                    <div>
                        <label for="message" class="block text-sm font-medium text-gray-700">Message</label>
                        <div class="mt-1">
                            <textarea name="message" id="message" rows="4" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm" placeholder="Write your message here..." required></textarea>
                        </div>
                    </div>
                    
                    <div>
                        <label for="message_files" class="block text-sm font-medium text-gray-700">Attachments (Optional)</label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600 justify-center">
                                    <label for="message_files" class="relative cursor-pointer bg-white rounded-md font-medium text-primary-600 hover:text-primary-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary-500">
                                        <span>Upload files</span>
                                        <input id="message_files" name="files[]" type="file" class="sr-only" multiple>
                                    </label>
                                    <p class="pl-1">or drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">
                                    PDF, Word, Excel, Images (max 10MB each)
                                </p>
                            </div>
                        </div>
                        <div id="create_file_list" class="mt-2 text-sm text-gray-500 space-y-1"></div>
                    </div>
                    
                    <div class="flex justify-end space-x-3 pt-5">
                        <a href="{{ route('admin.messages.index') }}" class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-full text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                            Cancel
                        </a>
                        <button type="submit" class="inline-flex justify-center py-2 px-6 border border-transparent shadow-sm text-sm font-medium rounded-full text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                            Send Message
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
// Helper function to format message timestamps
function formatMessageTime(date) {
    if (!date) return '';
    
    const messageDate = new Date(date);
    const now = new Date();
    const yesterday = new Date(now);
    yesterday.setDate(yesterday.getDate() - 1);
    
    const isToday = messageDate.getDate() === now.getDate() && 
                    messageDate.getMonth() === now.getMonth() && 
                    messageDate.getFullYear() === now.getFullYear();
                    
    const isYesterday = messageDate.getDate() === yesterday.getDate() && 
                        messageDate.getMonth() === yesterday.getMonth() && 
                        messageDate.getFullYear() === yesterday.getFullYear();
    
    if (isToday) {
        return messageDate.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
    } else if (isYesterday) {
        return 'Yesterday ' + messageDate.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
    } else {
        // For older messages, show the date
        return messageDate.toLocaleDateString([], { month: 'numeric', day: 'numeric', year: 'numeric' }) + 
               ' ' + messageDate.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Handle recipient type change
    const recipientTypeSelect = document.getElementById('recipient_type');
    const receiverSelect = document.getElementById('receiver_id');
    
    if (recipientTypeSelect && receiverSelect) {
        recipientTypeSelect.addEventListener('change', function() {
            const recipientType = this.value;
            
            // Clear current options
            receiverSelect.innerHTML = '<option value="">Select recipient...</option>';
            
            // Fetch new options based on recipient type
            fetch(`{{ route('admin.messages.recipients', '') }}/${recipientType}`)
                .then(response => response.json())
                .then(data => {
                    data.forEach(recipient => {
                        const option = document.createElement('option');
                        option.value = recipient.id;
                        option.textContent = `${recipient.name} - ${recipient.email}`;
                        receiverSelect.appendChild(option);
                    });
                })
                .catch(error => console.error('Error fetching recipients:', error));
        });
    }
    
    // File upload preview for message form
    const fileInput = document.getElementById('files');
    const filePreviewContainer = document.getElementById('filePreviewContainer');
    const filePreviewList = document.getElementById('filePreviewList');
    const selectedFilesCount = document.getElementById('selectedFilesCount');
    const fileCountDisplay = document.getElementById('fileCountDisplay');
    const toggleFilePreview = document.getElementById('toggleFilePreview');
    const filePreviewChevron = document.getElementById('filePreviewChevron');
    
    if (fileInput && filePreviewContainer && filePreviewList) {
        fileInput.addEventListener('change', function() {
            const files = this.files;
            
            if (files.length > 0) {
                // Update count badge
                selectedFilesCount.textContent = `${files.length} file${files.length > 1 ? 's' : ''} selected`;
                selectedFilesCount.classList.remove('hidden');
                fileCountDisplay.textContent = `${files.length} file${files.length > 1 ? 's' : ''}`;
                
                // Clear and show preview list
                filePreviewList.innerHTML = '';
                filePreviewContainer.classList.remove('hidden');
                
                // Add preview for each file
                Array.from(files).forEach(file => {
                    const div = document.createElement('div');
                    div.classList.add('flex', 'items-center', 'justify-between');
                    
                    // Icon based on file type
                    let iconType = 'document';
                    const extension = file.name.split('.').pop().toLowerCase();
                    
                    if (['jpg', 'jpeg', 'png', 'gif'].includes(extension)) {
                        iconType = 'image';
                    } else if (['doc', 'docx'].includes(extension)) {
                        iconType = 'word';
                    } else if (['xls', 'xlsx'].includes(extension)) {
                        iconType = 'excel';
                    } else if (extension === 'pdf') {
                        iconType = 'pdf';
                    }
                    
                    div.innerHTML = `
                        <div class="flex items-center">
                            <svg class="w-4 h-4 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <span class="text-gray-800 truncate max-w-[200px]">${file.name}</span>
                        </div>
                        <span class="text-gray-500">${(file.size / 1024).toFixed(1)} KB</span>
                    `;
                    
                    filePreviewList.appendChild(div);
                });
            } else {
                // Hide preview and count
                filePreviewContainer.classList.add('hidden');
                selectedFilesCount.classList.add('hidden');
                filePreviewList.classList.add('hidden');
            }
        });
        
        // Toggle file preview
        if (toggleFilePreview) {
            toggleFilePreview.addEventListener('click', function() {
                filePreviewList.classList.toggle('hidden');
                filePreviewChevron.classList.toggle('rotate-180');
            });
        }
    }
    
    // File upload preview for create message form
    const createFileInput = document.getElementById('message_files');
    const createFileList = document.getElementById('create_file_list');
    
    if (createFileInput && createFileList) {
        createFileInput.addEventListener('change', function() {
            const files = this.files;
            createFileList.innerHTML = '';
            
            if (files.length > 0) {
                Array.from(files).forEach(file => {
                    const div = document.createElement('div');
                    div.classList.add('flex', 'items-center', 'justify-between', 'bg-gray-50', 'p-2', 'rounded');
                    div.innerHTML = `
                        <div class="flex items-center">
                            <svg class="w-4 h-4 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <span>${file.name}</span>
                        </div>
                        <span class="text-gray-500">${(file.size / 1024).toFixed(1)} KB</span>
                    `;
                    createFileList.appendChild(div);
                });
            }
        });
        
        // Drag and drop for create message form
        const dropZone = document.querySelector('.border-dashed');
        if (dropZone) {
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, preventDefaults, false);
            });
            
            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }
            
            ['dragenter', 'dragover'].forEach(eventName => {
                dropZone.addEventListener(eventName, highlight, false);
            });
            
            ['dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, unhighlight, false);
            });
            
            function highlight() {
                dropZone.classList.add('border-primary-500', 'bg-primary-50');
                dropZone.classList.remove('border-gray-300');
            }
            
            function unhighlight() {
                dropZone.classList.remove('border-primary-500', 'bg-primary-50');
                dropZone.classList.add('border-gray-300');
            }
            
            dropZone.addEventListener('drop', handleDrop, false);
            
            function handleDrop(e) {
                const dt = e.dataTransfer;
                const files = dt.files;
                createFileInput.files = files;
                
                // Trigger change event manually
                const event = new Event('change', { bubbles: true });
                createFileInput.dispatchEvent(event);
            }
        }
    }
    
    // Scroll to the bottom of message thread (since we're showing latest messages at the bottom)
    const messageThread = document.getElementById('messageThread');
    if (messageThread) {
        messageThread.scrollTop = messageThread.scrollHeight;
    }
    
    // Mobile navigation
    const toggleSidebar = document.getElementById('toggleSidebar');
    const backToList = document.getElementById('backToList');
    const sidebar = document.getElementById('sidebar');
    const messageContent = document.getElementById('messageContent');
    
    if (toggleSidebar && sidebar) {
        toggleSidebar.addEventListener('click', function() {
            if (sidebar.style.display === 'none' || sidebar.style.display === '') {
                sidebar.style.display = 'flex';
                if (messageContent) messageContent.style.display = 'none';
            } else {
                sidebar.style.display = 'none';
                if (messageContent) messageContent.style.display = 'flex';
            }
        });
    }
    
    if (backToList && sidebar && messageContent) {
        backToList.addEventListener('click', function() {
            sidebar.style.display = 'flex';
            messageContent.style.display = 'none';
        });
    }
    
    // Force-fix the back button for Android
    if (backToList) {
        backToList.addEventListener('touchend', function(e) {
            e.preventDefault();
            if (sidebar && messageContent) {
                sidebar.style.display = 'flex';
                messageContent.style.display = 'none';
            }
        });
    }
    
    // Handle conversation selection on mobile
    const conversationLinks = document.querySelectorAll('a[data-conversation-id]');
    conversationLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Only for mobile screens
            if (window.innerWidth < 1024) {
                e.preventDefault();
                const conversationId = this.getAttribute('data-conversation-id');
                
                // Redirect to the conversation page
                window.location.href = "{{ route('admin.messages.index') }}?conversation=" + conversationId;
                
                // Hide sidebar and show message content
                if (sidebar && messageContent) {
                    sidebar.style.display = 'none';
                    messageContent.style.display = 'flex';
                }
            }
        });
        
        // Force enable click/touch events for Android
        link.addEventListener('touchend', function(e) {
            if (window.innerWidth < 1024) {
                e.preventDefault();
                const conversationId = this.getAttribute('data-conversation-id');
                window.location.href = "{{ route('admin.messages.index') }}?conversation=" + conversationId;
            }
        });
    });
    
    // Fix scrolling issues on Android
    if (messageThread && /Android/.test(navigator.userAgent)) {
        messageThread.style.webkitOverflowScrolling = 'touch';
        messageThread.addEventListener('touchmove', function(e) {
            e.stopPropagation();
        }, { passive: true });
    }
    
    // Handle window resize to reset UI for desktop
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 1024 && sidebar) {
            sidebar.style.display = 'flex';
            if (messageContent && document.querySelector('.conversation-item')) {
                messageContent.style.display = 'flex';
            }
        }
    });
    
    // Real-time message updates via AJAX
    let lastCheckedTime = new Date().toISOString();
    let currentConversationId = null;
    
    // Get the current conversation ID if available
    const conversationParam = new URLSearchParams(window.location.search).get('conversation');
    if (conversationParam) {
        currentConversationId = conversationParam;
    }
    
    // AJAX form submission to prevent page refresh
    const messageForm = document.getElementById('messageForm');
    if (messageForm) {
        messageForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitButton = this.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.innerHTML;
            
            // Disable button and show loading state
            submitButton.disabled = true;
            submitButton.innerHTML = `
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Sending...
            `;
            
            fetch(this.action.replace('/reply/', '/ajax-reply/'), {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Clear the form
                    messageForm.reset();
                    document.getElementById('filePreviewContainer').classList.add('hidden');
                    document.getElementById('filePreviewList').classList.add('hidden');
                    
                    // Add the new message to the thread
                    addNewMessageToThread(data.message);
                    
                    // Update last checked time
                    lastCheckedTime = new Date().toISOString();
                } else {
                    alert(data.message || 'An error occurred while sending your message.');
                }
            })
            .catch(error => {
                console.error('Error sending message:', error);
                alert('An error occurred while sending your message. Please try again.');
            })
            .finally(() => {
                // Re-enable button and restore text
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;
            });
        });
    }
    
    // Function to add a new message to the thread
    function addNewMessageToThread(message) {
        const messagesContainer = document.getElementById('messagesContainer');
        if (!messagesContainer) return;
        
        const messageHtml = createMessageHTML(message);
        messagesContainer.insertAdjacentHTML('beforeend', messageHtml);
        
        // Scroll to the new message
        const messageThread = document.getElementById('messageThread');
        if (messageThread) {
            messageThread.scrollTop = messageThread.scrollHeight;
        }
    }
    
    // Function to create HTML for a message
    function createMessageHTML(message) {
        const isSentByCurrentUser = message.user_id == {{ Auth::id() }};
        const justifyClass = isSentByCurrentUser ? 'justify-end' : 'justify-start';
        
        let bgColorClass, textColorClass, borderClass, userInitial;
        
        if (isSentByCurrentUser) {
            bgColorClass = 'bg-blue-600';
            textColorClass = 'text-white';
            borderClass = 'rounded-tl-2xl rounded-tr-sm rounded-br-2xl rounded-bl-2xl';
            userInitial = '{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}';
        } else {
            bgColorClass = 'bg-white';
            textColorClass = 'text-gray-800';
            borderClass = 'rounded-tr-2xl rounded-tl-sm rounded-bl-2xl rounded-br-2xl border border-gray-200';
            
            (message.message_type === 'client') {
                userInitial = 'C';
            } else if (message.message_type === 'writer') {
                userInitial = 'W';
            } else {
                userInitial = 'A';
            }
        }
        
        const messageTime = formatMessageTime(message.created_at);
        
        let html = `
            <div class="flex ${justifyClass}" id="message-${message.id}">
        `;
        
        if (!isSentByCurrentUser) {
            html += `
                <div class="flex-shrink-0 h-8 w-8 rounded-full bg-${message.message_type === 'client' ? 'blue' : (message.message_type === 'writer' ? 'green' : 'gray')}-100 flex items-center justify-center mr-2 self-end">
                    <span class="text-${message.message_type === 'client' ? 'blue' : (message.message_type === 'writer' ? 'green' : 'gray')}-600 font-medium text-xs">
                        ${userInitial}
                    </span>
                </div>
            `;
        }
        
        html += `
            <div class="${bgColorClass} ${textColorClass} ${borderClass} px-4 py-2 max-w-[85%] sm:max-w-[70%] shadow-sm">
                <div class="flex justify-between items-center mb-1">
                    <span class="font-medium text-xs ${isSentByCurrentUser ? 'text-white' : 'text-gray-900'}">
                        ${isSentByCurrentUser ? 'You' : message.user_name || 'Unknown'}
                        ${message.message_type !== 'admin' ? `<span class="text-xs font-normal ${isSentByCurrentUser ? 'text-blue-100' : `text-${message.message_type === 'client' ? 'blue' : 'green'}-600`}">(as ${message.message_type === 'client' ? 'Client' : 'Writer'})</span>` : ''}
                    </span>
                    <span class="text-xs ${isSentByCurrentUser ? 'text-blue-100' : 'text-gray-500'}">
                        ${messageTime}
                    </span>
                </div>
        `;
        
        if (message.title) {
            html += `<div class="font-medium text-sm mb-1 ${isSentByCurrentUser ? 'text-white' : 'text-gray-900'}">${message.title}</div>`;
        }
        
        html += `<div class="text-sm whitespace-pre-wrap ${isSentByCurrentUser ? 'text-white' : 'text-gray-800'}">${message.message}</div>`;
        
        if (message.files && message.files.length > 0) {
            html += `
                <div class="mt-2 pt-2 border-t ${isSentByCurrentUser ? 'border-blue-500' : 'border-gray-200'}">
            `;
            
            message.files.forEach(file => {
                html += `
                    <div class="flex items-center text-xs mt-1">
                        <svg class="w-4 h-4 ${isSentByCurrentUser ? 'text-blue-100' : 'text-gray-500'} mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                        </svg>
                        <a href="${file.download_url}" class="${isSentByCurrentUser ? 'text-blue-100' : 'text-primary-600'} hover:underline truncate max-w-[150px] sm:max-w-none">
                            ${file.name}
                        </a>
                        <span class="ml-1 ${isSentByCurrentUser ? 'text-blue-100' : 'text-gray-500'} hidden sm:inline">(${formatFileSize(file.size)})</span>
                    </div>
                `;
            });
            
            html += `</div>`;
        }
        
        if (isSentByCurrentUser) {
            html += `
                <div class="text-right mt-1 flex justify-end">
                    <span class="text-xs text-blue-100 flex items-center">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Sent
                    </span>
                </div>
            `;
        }
        
        html += `</div>`;
        
        if (isSentByCurrentUser) {
            html += `
                <div class="flex-shrink-0 h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center ml-2 self-end">
                    ${
                        {{ Auth::user()->profile_picture ? true : false }}
                        ? `<img class="h-8 w-8 rounded-full object-cover" src="{{ Auth::user()->profile_picture ? asset(Auth::user()->profile_picture) : '' }}" alt="{{ Auth::user()->name }}">`
                        : `<span class="text-blue-600 font-medium text-xs">${userInitial}</span>`
                    }
                </div>
            `;
        }
        
        html += `</div>`;
        
        return html;
    }
    
    // Function to format file size
    function formatFileSize(bytes) {
        if (bytes < 1024) return bytes + ' B';
        const kb = bytes / 1024;
        if (kb < 1024) return kb.toFixed(1) + ' KB';
        const mb = kb / 1024;
        return mb.toFixed(1) + ' MB';
    }
    
    // Periodically check for new messages
    if (currentConversationId) {
        setInterval(checkForNewMessages, 10000); // Check every 10 seconds
    }
    
    function checkForNewMessages() {
        fetch(`{{ route('admin.messages.check') }}?conversation=${currentConversationId}&after=${encodeURIComponent(lastCheckedTime)}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.messages.length > 0) {
                // Check if we need to add a date separator
                const lastMessage = document.querySelector('#messagesContainer > div:last-child');
                let lastMessageDate = null;
                
                if (lastMessage) {
                    const lastMessageTimestamp = lastMessage.querySelector('.text-gray-500, .text-blue-100').textContent;
                    if (lastMessageTimestamp) {
                        if (lastMessageTimestamp.includes('Today')) {
                            lastMessageDate = 'today';
                        } else if (lastMessageTimestamp.includes('Yesterday')) {
                            lastMessageDate = 'yesterday';
                        } else {
                            // It's a full date
                            lastMessageDate = lastMessageTimestamp.split(' ')[0];
                        }
                    }
                }
                
                // Add new messages to the thread
                data.messages.forEach(message => {
                    // Check if the message already exists in the thread
                    if (!document.getElementById(`message-${message.id}`)) {
                        // Check if we need a new date separator
                        const messageDate = new Date(message.created_at);
                        const today = new Date();
                        const yesterday = new Date(today);
                        yesterday.setDate(yesterday.getDate() - 1);
                        
                        let currentMessageDate;
                        if (messageDate.toDateString() === today.toDateString()) {
                            currentMessageDate = 'today';
                        } else if (messageDate.toDateString() === yesterday.toDateString()) {
                            currentMessageDate = 'yesterday';
                        } else {
                            currentMessageDate = messageDate.toLocaleDateString();
                        }
                        
                        // If the date is different, add a separator
                        if (lastMessageDate !== currentMessageDate) {
                            const dateSeparator = `
                                <div class="flex items-center justify-center my-4">
                                    <div class="bg-gray-200 px-3 py-1 rounded-full">
                                        <span class="text-xs text-gray-600">
                                            ${currentMessageDate === 'today' ? 'Today' : 
                                              currentMessageDate === 'yesterday' ? 'Yesterday' : 
                                              new Date(message.created_at).toLocaleDateString([], {month: 'long', day: 'numeric', year: 'numeric'})}
                                        </span>
                                    </div>
                                </div>
                            `;
                            document.getElementById('messagesContainer').insertAdjacentHTML('beforeend', dateSeparator);
                            lastMessageDate = currentMessageDate;
                        }
                        
                        addNewMessageToThread(message);
                    }
                });
                
                // Play notification sound for new messages
                playNotificationSound();
                
                // Update last checked time
                lastCheckedTime = new Date().toISOString();
            }
        })
        .catch(error => console.error('Error checking for new messages:', error));
    }
    
    // Play notification sound
    function playNotificationSound() {
        try {
            const audio = new Audio('/sounds/notification.mp3');
            audio.volume = 0.5;
            audio.play().catch(e => console.log('Audio play prevented:', e));
        } catch (e) {
            console.log('Notification sound error:', e);
        }
    }
    
    // Search functionality - enhanced to search writer names, IDs, and order numbers
    const searchInput = document.getElementById('searchConversations');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const conversationItems = document.querySelectorAll('.conversation-item');
            
            if (searchTerm.length === 0) {
                // Show all conversations
                conversationItems.forEach(item => {
                    item.style.display = '';
                });
                return;
            }
            
            conversationItems.forEach(item => {
                const writerName = item.dataset.name.toLowerCase();
                const writerId = item.dataset.id.toLowerCase();
                const orderId = item.dataset.order.toLowerCase();
                
                // Search name, writer ID, or order ID
                if (writerName.includes(searchTerm) || 
                    writerId.includes(searchTerm) || 
                    orderId.includes(searchTerm)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });
        
        // Add search auto-focus on desktop
        if (window.innerWidth > 768) {
            setTimeout(() => {
                searchInput.focus();
            }, 500);
        }
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

@php
/**
 * Format message timestamp for display
 *
 * @param \Carbon\Carbon|string $date
 * @return string
 */
function formatMessageTime($date)
{
    if (!$date) return '';
    
    if (is_string($date)) {
        $date = \Carbon\Carbon::parse($date);
    }
    
    $now = \Carbon\Carbon::now();
    
    if ($date->isToday()) {
        return $date->format('g:i A');
    } elseif ($date->isYesterday()) {
        return 'Yesterday ' . $date->format('g:i A');
    } elseif ($date->year === $now->year) {
        return $date->format('M j, g:i A');
    } else {
        return $date->format('M j, Y, g:i A');
    }
}
@endphp
@endpush