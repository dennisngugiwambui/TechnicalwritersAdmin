@extends('admin.app')

@section('content')
<div class="bg-white shadow rounded-lg overflow-hidden">
    <!-- Message Header -->
    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">Message Details</h2>
            <p class="text-sm text-gray-600">
                @if($message->order_id)
                    Related to Order #{{ $message->order_id }}
                    @if($message->order)
                        - {{ $message->order->title }}
                    @endif
                @else
                    Direct Message
                @endif
            </p>
        </div>
        <div class="flex items-center">
            @if($message->order_id && $message->order)
                <a href="{{ route('admin.orders.show', $message->order_id) }}" class="mr-4 flex items-center px-3 py-1.5 border border-gray-300 text-sm leading-5 font-medium rounded-md text-gray-700 bg-white hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue active:text-gray-800 active:bg-gray-50 transition ease-in-out duration-150">
                    <i class="fas fa-eye mr-1.5"></i>
                    View Order
                </a>
            @endif
            <a href="{{ route('admin.messages.index') }}" class="flex items-center px-3 py-1.5 border border-gray-300 text-sm leading-5 font-medium rounded-md text-gray-700 bg-white hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue active:text-gray-800 active:bg-gray-50 transition ease-in-out duration-150">
                <i class="fas fa-arrow-left mr-1.5"></i>
                Back to Messages
            </a>
        </div>
    </div>
    
    <!-- Message Info -->
    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <h4 class="text-xs uppercase font-semibold text-gray-500 mb-1">From</h4>
                <div class="flex items-center">
                    @if($message->sender)
                        <div class="flex-shrink-0 h-8 w-8 mr-2">
                            @if($message->sender->profile_picture)
                                <img class="h-8 w-8 rounded-full object-cover" src="{{ asset($message->sender->profile_picture) }}" alt="{{ $message->sender->name }}">
                            @else
                                <div class="h-8 w-8 rounded-full bg-primary-100 flex items-center justify-center">
                                    <span class="text-primary-600 font-medium text-sm">{{ strtoupper(substr($message->sender->name, 0, 1)) }}</span>
                                </div>
                            @endif
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-800">{{ $message->sender->name }}</p>
                            <p class="text-xs text-gray-500">{{ $message->sender->email }}</p>
                        </div>
                    @else
                        <p class="text-sm text-gray-600">System Message</p>
                    @endif
                </div>
            </div>
            
            <div>
                <h4 class="text-xs uppercase font-semibold text-gray-500 mb-1">To</h4>
                <div class="flex items-center">
                    @if($message->receiver)
                        <div class="flex-shrink-0 h-8 w-8 mr-2">
                            @if($message->receiver->profile_picture)
                                <img class="h-8 w-8 rounded-full object-cover" src="{{ asset($message->receiver->profile_picture) }}" alt="{{ $message->receiver->name }}">
                            @else
                                <div class="h-8 w-8 rounded-full bg-primary-100 flex items-center justify-center">
                                    <span class="text-primary-600 font-medium text-sm">{{ strtoupper(substr($message->receiver->name, 0, 1)) }}</span>
                                </div>
                            @endif
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-800">{{ $message->receiver->name }}</p>
                            <p class="text-xs text-gray-500">{{ $message->receiver->email }}</p>
                        </div>
                    @else
                        <p class="text-sm text-gray-600">All Users</p>
                    @endif
                </div>
            </div>
            
            <div>
                <h4 class="text-xs uppercase font-semibold text-gray-500 mb-1">Date & Time</h4>
                <p class="text-sm text-gray-800">{{ $message->created_at->format('M d, Y h:i A') }}</p>
                <p class="text-xs text-gray-500">
                    @if($message->read_at)
                        Read {{ $message->read_at->diffForHumans() }}
                    @else
                        Not read yet
                    @endif
                </p>
            </div>
        </div>
    </div>
    
    <!-- Message Subject -->
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800">
            {{ $message->subject ?? 'No Subject' }}
        </h3>
    </div>
    
    <!-- Message Content -->
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="prose max-w-none">
            {!! nl2br(e($message->content)) !!}
        </div>
        
        @if($message->attachments && count(json_decode($message->attachments)) > 0)
            <div class="mt-4 pt-4 border-t border-gray-200">
                <h4 class="text-sm font-medium text-gray-700 mb-2">Attachments</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    @foreach(json_decode($message->attachments) as $attachment)
                        <a href="{{ asset('storage/attachments/' . $attachment->filename) }}" 
                           target="_blank"
                           class="flex items-center p-2 border border-gray-200 rounded-md hover:bg-gray-50">
                            <div class="bg-gray-100 p-2 rounded mr-2">
                                <i class="fas fa-file-alt text-gray-500"></i>
                            </div>
                            <div class="overflow-hidden">
                                <p class="text-sm font-medium text-primary-600 truncate">{{ $attachment->original_name }}</p>
                                <p class="text-xs text-gray-500">{{ $attachment->size }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
    
    <!-- Conversation History -->
    <div class="p-6 bg-gray-50">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Conversation History</h3>
        
        <div class="space-y-6">
            @forelse($conversation as $msg)
                <div class="bg-white rounded-lg shadow-sm p-4 {{ $msg->id === $message->id ? 'border-2 border-primary-500' : '' }}">
                    <div class="flex justify-between">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-8 w-8 mr-2">
                                @if($msg->sender && $msg->sender->profile_picture)
                                    <img class="h-8 w-8 rounded-full object-cover" src="{{ asset($msg->sender->profile_picture) }}" alt="{{ $msg->sender->name }}">
                                @else
                                    <div class="h-8 w-8 rounded-full bg-{{ $msg->sender ? 'primary' : 'gray' }}-100 flex items-center justify-center">
                                        <span class="text-{{ $msg->sender ? 'primary' : 'gray' }}-600 font-medium text-sm">
                                            {{ $msg->sender ? strtoupper(substr($msg->sender->name, 0, 1)) : 'S' }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-800">
                                    {{ $msg->sender ? $msg->sender->name : 'System Message' }}
                                </p>
                                <p class="text-xs text-gray-500">{{ $msg->created_at->format('M d, Y h:i A') }}</p>
                            </div>
                        </div>
                        
                        @if($msg->id !== $message->id)
                            <a href="{{ route('admin.messages.show', $msg->id) }}" class="text-xs text-primary-600 hover:text-primary-800">
                                View Details
                            </a>
                        @endif
                    </div>
                    
                    @if($msg->subject)
                        <h4 class="text-sm font-medium text-gray-700 mt-2">{{ $msg->subject }}</h4>
                    @endif
                    
                    <div class="mt-2 text-sm text-gray-600">
                        @if(strlen($msg->content) > 200 && $msg->id !== $message->id)
                            {!! nl2br(e(substr($msg->content, 0, 200))) !!}...
                        @else
                            {!! nl2br(e($msg->content)) !!}
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-4 text-gray-500">
                    No messages in this conversation.
                </div>
            @endforelse
        </div>
    </div>
    
    <!-- Reply Form -->
    <div class="px-6 py-4 border-t border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Reply</h3>
        
        <form action="{{ route('admin.messages.reply', $message->order_id ? $message->order_id : $message->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="mb-4">
                <label for="reply_content" class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                <textarea id="reply_content" name="content" rows="5" class="shadow-sm focus:ring-primary-500 focus:border-primary-500 mt-1 block w-full sm:text-sm border-gray-300 rounded-md" required></textarea>
            </div>
            
            <div class="mb-4">
                <label for="attachments" class="block text-sm font-medium text-gray-700 mb-1">Attachments (Optional)</label>
                <input type="file" id="attachments" name="attachments[]" multiple class="shadow-sm focus:ring-primary-500 focus:border-primary-500 mt-1 block w-full sm:text-sm border-gray-300 rounded-md">
                <p class="mt-1 text-xs text-gray-500">You can upload multiple files. Maximum size: 10MB per file.</p>
            </div>
            
            <div class="flex justify-end">
                @if($message->order_id)
                    <input type="hidden" name="order_id" value="{{ $message->order_id }}">
                    
                    <div class="mr-4">
                        <button type="submit" name="reply_as" value="client" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                            <i class="fas fa-user mr-1.5"></i>
                            Reply as Client
                        </button>
                    </div>
                    
                    <div>
                        <button type="submit" name="reply_as" value="support" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                            <i class="fas fa-headset mr-1.5"></i>
                            Reply as Support
                        </button>
                    </div>
                @else
                    <input type="hidden" name="receiver_id" value="{{ $message->sender_id }}">
                    
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        <i class="fas fa-paper-plane mr-1.5"></i>
                        Send Reply
                    </button>
                @endif
            </div>
        </form>
    </div>
</div>
@endsection