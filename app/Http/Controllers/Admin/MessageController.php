<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use App\Models\Order;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class MessageController extends Controller
{
    /**
     * Display a listing of messages (inbox).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Get unread message count
        $unreadCount = Message::whereNull('read_at')
            ->where('receiver_id', Auth::id())
            ->count();
        
        // Get recent conversations (grouped by participant)
        $conversations = $this->getConversations();
        
        // Get active conversation if ID is provided
        $currentConversation = null;
        $messages = collect();
        
        if ($request->has('conversation') && !empty($request->conversation)) {
            // Find the conversation in our list
            $currentConversation = $conversations->firstWhere('id', $request->conversation);
            
            if ($currentConversation) {
                // Get messages for current conversation
                $query = Message::query();
                
                // Filter by order if specified
                if ($currentConversation['order_id']) {
                    $query->where('order_id', $currentConversation['order_id']);
                }
                
                // Filter by participant
                $query->where(function($q) use ($currentConversation) {
                    $q->where(function($subq) use ($currentConversation) {
                        $subq->where('user_id', Auth::id())
                             ->where('receiver_id', $currentConversation['participant_id']);
                    })->orWhere(function($subq) use ($currentConversation) {
                        $subq->where('user_id', $currentConversation['participant_id'])
                             ->where(function($innerq) {
                                 $innerq->where('receiver_id', Auth::id())
                                       ->orWhere('is_general', true);
                             });
                    });
                });
                
                // Order by creation date (newest first)
                $messages = $query->with(['user', 'receiver', 'files'])
                                  ->orderBy('created_at', 'desc')
                                  ->get();
                
                // Mark messages as read
                $this->markMessagesAsRead($messages);
            }
        }
        
        // Get all orders for the new message form
        $orders = Order::orderBy('created_at', 'desc')->limit(100)->get();
        
        return view('admin.messages.index', compact(
            'conversations', 
            'currentConversation', 
            'messages', 
            'unreadCount', 
            'orders'
        ));
    }
    
    /**
     * Get a specific conversation.
     *
     * @param  string  $conversationId
     * @return \Illuminate\Http\Response
     */
    public function conversation($conversationId)
    {
        // Parse the conversation ID and load the conversation data
        list($participantId, $orderId) = $this->parseConversationId($conversationId);
        
        // Get the participant (user)
        $participant = User::findOrFail($participantId);
        
        // Get the order if applicable
        $order = null;
        if ($orderId) {
            $order = Order::findOrFail($orderId);
        }
        
        // Get messages for this conversation
        $messages = $this->getConversationMessages($participantId, $orderId);
        
        // Mark messages as read
        $this->markMessagesAsRead($messages);
        
        // Get all conversations for the sidebar
        $conversations = $this->getConversations();
        
        // Get unread count
        $unreadCount = Message::whereNull('read_at')
            ->where('receiver_id', Auth::id())
            ->count();
        
        // Create current conversation data
        $currentConversation = [
            'id' => $conversationId,
            'participant' => $participant,
            'participant_id' => $participantId,
            'order' => $order,
            'order_id' => $orderId,
        ];
        
        // Get all orders for the new message form
        $orders = Order::orderBy('created_at', 'desc')->limit(100)->get();
        
        return view('admin.messages.index', compact(
            'conversations', 
            'currentConversation', 
            'messages', 
            'unreadCount', 
            'orders'
        ));
    }

    /**
     * Show the form for creating a new message.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $writers = User::where('usertype', 'writer')->orderBy('name')->get();
        $clients = User::where('usertype', 'client')->orderBy('name')->get();
        $orders = Order::orderBy('created_at', 'desc')->limit(100)->get();

        return view('admin.messages.create', compact('writers', 'clients', 'orders'));
    }

    /**
     * Store a newly created message.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'order_id' => 'nullable|exists:orders,id',
            'title' => 'nullable|string|max:255',
            'message' => 'required|string',
            'files.*' => 'nullable|file|max:10240', // Max 10MB per file
            'message_type' => 'nullable|string|in:admin,client',
        ]);

        // Create the message
        $message = Message::create([
            'user_id' => Auth::id(),
            'receiver_id' => $validated['receiver_id'],
            'order_id' => $validated['order_id'] ?? null,
            'title' => $validated['title'] ?? null,
            'message' => $validated['message'],
            'message_type' => $validated['message_type'] ?? 'admin',
            'is_general' => false,
        ]);

        // Handle file uploads
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $originalName = $file->getClientOriginalName();
                $uniqueName = pathinfo($originalName, PATHINFO_FILENAME) . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('message_files', $uniqueName);
                
                File::create([
                    'name' => $originalName,
                    'path' => $path,
                    'size' => $file->getSize(),
                    'fileable_id' => $message->id,
                    'fileable_type' => Message::class,
                    'uploaded_by' => Auth::id()
                ]);
            }
        }

        // Determine conversation ID for redirect
        $conversationId = $this->generateConversationId($validated['receiver_id'], $validated['order_id'] ?? null);

        return redirect()
            ->route('admin.messages.index', ['conversation' => $conversationId])
            ->with('success', 'Message sent successfully');
    }

   
    /**
     * Reply to a conversation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $conversationId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reply(Request $request, $conversationId)
    {
        // Validate the request
        $validated = $request->validate([
            'message' => 'required|string',
            'message_type' => 'nullable|string|in:admin,client',
            'files.*' => 'nullable|file|max:10240', // Max 10MB per file
        ]);

        // Parse conversation ID to get participant and order
        list($participantId, $orderId) = $this->parseConversationId($conversationId);

        // Create the message
        $message = Message::create([
            'user_id' => Auth::id(),
            'receiver_id' => $participantId,
            'order_id' => $orderId,
            'message' => $validated['message'],
            'message_type' => $validated['message_type'] ?? 'admin',
            'is_general' => false,
        ]);

        // Handle file uploads
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $originalName = $file->getClientOriginalName();
                $uniqueName = pathinfo($originalName, PATHINFO_FILENAME) . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('message_files', $uniqueName);
                
                File::create([
                    'name' => $originalName,
                    'path' => $path,
                    'size' => $file->getSize(),
                    'fileable_id' => $message->id,
                    'fileable_type' => Message::class,
                    'uploaded_by' => Auth::id()
                ]);
            }
        }

        return redirect()
            ->route('admin.messages.index', ['conversation' => $conversationId])
            ->with('success', 'Reply sent successfully');
    }

    /**
     * Get recipients of a specific type.
     *
     * @param  string  $type
     * @return \Illuminate\Http\JsonResponse
     */
    public function recipients($type)
    {
        if (!in_array($type, ['writer', 'client'])) {
            return response()->json([], 400);
        }

        $recipients = User::where('usertype', $type)
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        return response()->json($recipients);
    }

    /**
     * Check for new messages in a conversation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkNewMessages(Request $request)
    {
        // Validate the request
        $request->validate([
            'conversation' => 'required|string',
            'after' => 'required|date'
        ]);

        // Parse conversation ID to get participant and order
        list($participantId, $orderId) = $this->parseConversationId($request->conversation);
        
        // Query for new messages
        $query = Message::query();
        
        // Filter by time
        $query->where('created_at', '>', $request->after);
        
        // Filter by order if specified
        if ($orderId) {
            $query->where('order_id', $orderId);
        }
        
        // Filter by participant
        $query->where(function($q) use ($participantId) {
            $q->where(function($subq) use ($participantId) {
                $subq->where('user_id', $participantId)
                    ->where(function($innerq) {
                        $innerq->where('receiver_id', Auth::id())
                            ->orWhere('is_general', true);
                    });
            });
        });
        
        // Get messages with relationships
        $messages = $query->with(['user', 'files'])
                        ->orderBy('created_at', 'asc')
                        ->get();
        
        // Format messages for JSON response
        $formattedMessages = $messages->map(function($message) {
            return [
                'id' => $message->id,
                'user_id' => $message->user_id,
                'user_name' => $message->user->name ?? 'Unknown',
                'message_type' => $message->message_type,
                'title' => $message->title,
                'message' => $message->message,
                'created_at' => $message->created_at,
                'files' => $message->files->map(function($file) {
                    return [
                        'id' => $file->id,
                        'name' => $file->name,
                        'size' => $file->size,
                        'download_url' => route('files.download', $file->id)
                    ];
                })
            ];
        });
        
        // Mark messages as read
        if ($messages->count() > 0) {
            $messageIds = $messages->pluck('id')->toArray();
            Message::whereIn('id', $messageIds)
                ->where('receiver_id', Auth::id())
                ->whereNull('read_at')
                ->update(['read_at' => Carbon::now()]);
        }
        
        return response()->json([
            'success' => true,
            'messages' => $formattedMessages
        ]);
    }

        /**
     * Reply to a conversation via AJAX.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $conversationId
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajaxReply(Request $request, $conversationId)
    {
        // Validate the request
        $validated = $request->validate([
            'message' => 'required|string',
            'message_type' => 'nullable|string|in:admin,client',
            'files.*' => 'nullable|file|max:10240', // Max 10MB per file
        ]);

        // Parse conversation ID to get participant and order
        list($participantId, $orderId) = $this->parseConversationId($conversationId);

        // Create the message
        $message = Message::create([
            'user_id' => Auth::id(),
            'receiver_id' => $participantId,
            'order_id' => $orderId,
            'message' => $validated['message'],
            'message_type' => $validated['message_type'] ?? 'admin',
            'is_general' => false,
        ]);

        // Handle file uploads
        $files = [];
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $originalName = $file->getClientOriginalName();
                $uniqueName = pathinfo($originalName, PATHINFO_FILENAME) . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('message_files', $uniqueName);
                
                $fileModel = File::create([
                    'name' => $originalName,
                    'path' => $path,
                    'size' => $file->getSize(),
                    'fileable_id' => $message->id,
                    'fileable_type' => Message::class,
                    'uploaded_by' => Auth::id()
                ]);
                
                $files[] = [
                    'id' => $fileModel->id,
                    'name' => $fileModel->name,
                    'size' => $fileModel->size,
                    'download_url' => route('files.download', $fileModel->id)
                ];
            }
        }

        // Format the message for JSON response
        $formattedMessage = [
            'id' => $message->id,
            'user_id' => $message->user_id,
            'user_name' => Auth::user()->name,
            'message_type' => $message->message_type,
            'message' => $message->message,
            'created_at' => $message->created_at,
            'files' => $files
        ];

        return response()->json([
            'success' => true,
            'message' => $formattedMessage
        ]);
    }

    /**
     * Send a message as a client.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendAsClient(Request $request, Order $order)
    {
        // Validate the request
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'message' => 'required|string',
            'files.*' => 'nullable|file|max:10240', // Max 10MB per file
        ]);

        // Check if order has a writer
        if (!$order->writer_id) {
            return redirect()->back()->with('error', 'This order has no assigned writer to message.');
        }

        // Create the message
        $message = Message::create([
            'user_id' => Auth::id(),
            'receiver_id' => $order->writer_id,
            'order_id' => $order->id,
            'title' => $validated['title'] ?? null,
            'message' => $validated['message'],
            'message_type' => 'client', // Send as client
            'is_general' => false,
        ]);

        // Handle file uploads
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $originalName = $file->getClientOriginalName();
                $uniqueName = pathinfo($originalName, PATHINFO_FILENAME) . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('message_files', $uniqueName);
                
                File::create([
                    'name' => $originalName,
                    'path' => $path,
                    'size' => $file->getSize(),
                    'fileable_id' => $message->id,
                    'fileable_type' => Message::class,
                    'uploaded_by' => Auth::id()
                ]);
            }
        }

        // Determine conversation ID for redirect
        $conversationId = $this->generateConversationId($order->writer_id, $order->id);

        return redirect()
            ->route('admin.messages.index', ['conversation' => $conversationId])
            ->with('success', 'Message sent to writer as client');
    }

    /**
     * Send a message as support.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendAsSupport(Request $request, Order $order)
    {
        // Validate the request
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'message' => 'required|string',
            'files.*' => 'nullable|file|max:10240', // Max 10MB per file
        ]);

        // Check if order has a writer
        if (!$order->writer_id) {
            return redirect()->back()->with('error', 'This order has no assigned writer to message.');
        }

        // Create the message
        $message = Message::create([
            'user_id' => Auth::id(),
            'receiver_id' => $order->writer_id,
            'order_id' => $order->id,
            'title' => $validated['title'] ?? null,
            'message' => $validated['message'],
            'message_type' => 'admin', // Send as admin/support
            'is_general' => false,
        ]);

        // Handle file uploads
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $originalName = $file->getClientOriginalName();
                $uniqueName = pathinfo($originalName, PATHINFO_FILENAME) . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('message_files', $uniqueName);
                
                File::create([
                    'name' => $originalName,
                    'path' => $path,
                    'size' => $file->getSize(),
                    'fileable_id' => $message->id,
                    'fileable_type' => Message::class,
                    'uploaded_by' => Auth::id()
                ]);
            }
        }

        // Determine conversation ID for redirect
        $conversationId = $this->generateConversationId($order->writer_id, $order->id);

        return redirect()
            ->route('admin.messages.index', ['conversation' => $conversationId])
            ->with('success', 'Message sent to writer as support');
    }
    
    /**
     * Mark messages as read.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $messageId
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsRead(Request $request, $messageId)
    {
        $message = Message::findOrFail($messageId);
        
        // Check if user is allowed to mark this message as read
        if ($message->receiver_id != Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        
        $message->update(['read_at' => Carbon::now()]);
        
        return response()->json(['success' => true]);
    }
    
    /**
     * Delete a message.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $messageId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request, $messageId)
    {
        $message = Message::findOrFail($messageId);


        // Check if user is allowed to delete this message
        if ($message->user_id != Auth::id() && Auth::user()->usertype !== User::ROLE_ADMIN) {
            return redirect()->back()->with('error', 'You are not authorized to delete this message.');
        }
    
        
        // Delete associated files
        foreach ($message->files as $file) {
            Storage::delete($file->path);
            $file->delete();
        }
        
        $message->delete();
        
        return redirect()->back()->with('success', 'Message deleted successfully.');
    }
    
    /**
     * Get list of conversations.
     *
     * @return \Illuminate\Support\Collection
     */
    private function getConversations()
    {
        $authUserId = Auth::id();
        
        // Get all messages related to the current user
        $messages = Message::where(function($query) use ($authUserId) {
                        // Messages sent by current user
                        $query->where('user_id', $authUserId)
                            // OR messages received by current user
                            ->orWhere('receiver_id', $authUserId)
                            // OR general messages that should be visible to admin
                            ->orWhere('is_general', true);
                    })
                    ->with(['user', 'receiver', 'order'])
                    ->orderBy('created_at', 'desc')
                    ->get();
        
        // Group messages into conversations
        $conversations = collect();
        
        foreach ($messages as $message) {
            // Determine the other participant in the conversation
            $participantId = null;
            
            if ($message->user_id == $authUserId) {
                $participantId = $message->receiver_id;
            } else if ($message->receiver_id == $authUserId || $message->is_general) {
                $participantId = $message->user_id;
            }
            
            // Skip if no participant (e.g. system message with no receiver)
            if (!$participantId) continue;
            
            // Generate a unique ID for this conversation
            $conversationId = $this->generateConversationId($participantId, $message->order_id);
            
            // Check if conversation already exists in our collection
            if (!$conversations->has($conversationId)) {
                // Get the participant user
                $participant = $message->user_id == $authUserId ? $message->receiver : $message->user;
                
                // Skip if participant doesn't exist
                if (!$participant) continue;
                
                // Create a new conversation
                $conversations[$conversationId] = [
                    'id' => $conversationId,
                    'participant' => $participant,
                    'participant_id' => $participantId,
                    'order' => $message->order,
                    'order_id' => $message->order_id,
                    'last_message' => $message->message,
                    'last_message_at' => $message->created_at,
                    'unread_count' => 0,
                ];
            } else {
                // Check if this message is newer than the current one
                if ($message->created_at->isAfter($conversations[$conversationId]['last_message_at'])) {
                    $conversations[$conversationId]['last_message'] = $message->message;
                    $conversations[$conversationId]['last_message_at'] = $message->created_at;
                }
            }
            
            // Count unread messages
            if ($message->receiver_id == $authUserId && !$message->read_at) {
                $conversations[$conversationId]['unread_count']++;
            }
        }
        
        // Sort conversations by last message time
        return $conversations->sortByDesc('last_message_at')->values();
    }
    
    /**
     * Generate a unique conversation ID.
     *
     * @param  int  $participantId
     * @param  int|null  $orderId
     * @return string
     */
    private function generateConversationId($participantId, $orderId = null)
    {
        return $orderId ? "p{$participantId}_o{$orderId}" : "p{$participantId}";
    }
    
    /**
     * Parse a conversation ID into components.
     *
     * @param  string  $conversationId
     * @return array  [participantId, orderId]
     */
    private function parseConversationId($conversationId)
    {
        $participantId = null;
        $orderId = null;
        
        // Extract participant ID
        if (preg_match('/p(\d+)/', $conversationId, $matches)) {
            $participantId = $matches[1];
        }
        
        // Extract order ID
        if (preg_match('/o(\d+)/', $conversationId, $matches)) {
            $orderId = $matches[1];
        }
        
        return [$participantId, $orderId];
    }
    
    /**
     * Mark messages as read.
     *
     * @param  \Illuminate\Support\Collection  $messages
     * @return void
     */
    private function markMessagesAsRead($messages)
    {
        $authUserId = Auth::id();
        
        // Find unread messages
        $unreadMessages = $messages->filter(function($message) use ($authUserId) {
            return $message->receiver_id == $authUserId && !$message->read_at;
        });
        
        // Mark as read
        if ($unreadMessages->count() > 0) {
            $unreadIds = $unreadMessages->pluck('id')->toArray();
            
            Message::whereIn('id', $unreadIds)
                ->update(['read_at' => Carbon::now()]);
        }
    }
}