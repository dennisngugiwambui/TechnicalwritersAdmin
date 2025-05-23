<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Models\Bid;
use App\Models\Finance;
use App\Models\File;
use App\Models\Message;
use App\Mail\OrderAssigned;
use App\Mail\RevisionRequested;
use App\Mail\OrderCompleted;
use App\Mail\OrderDisputed;
use App\Services\OrderScrapingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class AdminOrderController extends Controller
{
    /**
     * Display a listing of the orders.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $status = $request->input('status', 'all');
        $search = $request->input('search');
        
        $query = Order::with(['writer', 'files']);
        
        // Filter by status
        if ($status !== 'all') {
            $query->where('status', $status);
        }
        
        // Search by order ID or title
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%");
            });
        }
        
        $orders = $query->latest()->paginate(15);
        
        // Get counts for dashboard stats
        $availableCount = Order::where('status', Order::STATUS_AVAILABLE)->count();
        $assignedCount = Order::whereIn('status', [
            Order::STATUS_CONFIRMED, 
            Order::STATUS_IN_PROGRESS,
            Order::STATUS_DONE,
            Order::STATUS_DELIVERED,
            Order::STATUS_REVISION
        ])->count();
        $completedCount = Order::whereIn('status', [
            Order::STATUS_COMPLETED,
            Order::STATUS_PAID,
            Order::STATUS_FINISHED
        ])->count();
        
        return view('admin.orders.index', compact(
            'orders', 
            'status', 
            'search',
            'availableCount',
            'assignedCount',
            'completedCount'
        ));
    }

    /**
     * Display a listing of all available orders with bids.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function bids(Request $request)
    {
        $search = $request->input('search');
        
        $query = Order::where('status', Order::STATUS_AVAILABLE)
            ->with(['bids', 'bids.user']); 
        
        // Search by order ID or title
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%");
            });
        }
        
        $orders = $query->latest()->paginate(15);
        
        return view('admin.bids.index', compact('orders', 'search'));
    }

    /**
     * Display the specified order with bids.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function showBids($id)
    {
        $order = Order::with([
            'bids' => function($query) {
                $query->orderBy('created_at', 'desc');
            },
            'bids.user', // Changed from bids.writer to bids.user
            'bids.user.writerProfile', // Eager load writer profiles
            'files'
        ])->findOrFail($id);
        
        // Ensure order is available
        if ($order->status !== Order::STATUS_AVAILABLE) {
            return redirect()->route('admin.bids')
                ->with('error', 'This order is no longer available for bidding.');
        }
        
        return view('admin.bids.show', compact('order'));
    }

    /**
     * Filter writers by search term for bidding.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $orderId
     * @return \Illuminate\Http\Response
     */
    public function filterWriters(Request $request, $orderId)
    {
        $searchTerm = $request->input('search', '');
        $order = Order::findOrFail($orderId);
        
        $writers = User::where('usertype', 'writer')
            ->where('status', 'active')
            ->where(function($query) use ($searchTerm) {
                $query->where('name', 'like', "%{$searchTerm}%")
                      ->orWhere('email', 'like', "%{$searchTerm}%")
                      ->orWhere('id', 'like', "%{$searchTerm}%")
                      ->orWhereHas('writerProfile', function($q) use ($searchTerm) {
                          $q->where('writer_id', 'like', "%{$searchTerm}%");
                      });
            })
            ->with('writerProfile') // Eager load the writer profile
            ->orderBy('created_at', 'desc') // Fallback sort
            ->paginate(10);
        
        // Get bids for this order
        $bidders = Bid::where('order_id', $orderId)
            ->pluck('user_id') // Changed from writer_id to user_id
            ->toArray();
        
        return response()->json([
            'writers' => $writers,
            'bidders' => $bidders
        ]);
    }
    
    /**
     * Show the form for creating a new order.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $writers = User::where('usertype', 'writer')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
            
        return view('admin.orders.create', compact('writers'));
    }

    /**
     * Store a newly created order in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'instructions' => 'required|string',
            'price' => 'required|numeric|min:0',
            'deadline' => 'required|date|after:now',
            'task_size' => 'required|integer|min:1',
            'discipline' => 'required|string|max:255',
            'type_of_service' => 'required|string|max:255',
            'writer_id' => 'nullable|exists:users,id',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // Create new order
        $order = new Order();
        $order->title = $request->input('title');
        $order->instructions = $request->input('instructions');
        $order->price = $request->input('price');
        $order->deadline = Carbon::parse($request->input('deadline'));
        $order->task_size = $request->input('task_size');
        $order->discipline = $request->input('discipline');
        $order->type_of_service = $request->input('type_of_service');
        
        // If writer is assigned immediately
        if ($request->input('writer_id')) {
            $order->writer_id = $request->input('writer_id');
            $order->status = Order::STATUS_UNCONFIRMED; // Changed to UNCONFIRMED
        } else {
            $order->status = Order::STATUS_AVAILABLE;
        }
        
        $order->save();
        
        // Handle file uploads
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('order_files', 'public');
                
                $orderFile = new File();
                $orderFile->name = $file->getClientOriginalName();
                $orderFile->path = $path;
                $orderFile->size = $file->getSize();
                $orderFile->fileable_id = $order->id;
                $orderFile->fileable_type = get_class($order);
                $orderFile->uploaded_by = Auth::id();
                $orderFile->save();
            }
        }
        
        // Send notification to writer if assigned
        if ($request->input('writer_id')) {
            $this->notifyWriterOfAssignment($order);
            
            // Create assignment message
            $message = new Message();
            $message->order_id = $order->id;
            $message->user_id = Auth::id();
            $message->receiver_id = $request->input('writer_id');
            $message->title = 'Order Assignment';
            $message->message = "Please check this order {$order->id} and let us know if you can finish it by the deadline and inform us incase anything is missing. You may also send a message to the customer if the order contains contradictory information.";
            $message->message_type = 'admin';
            $message->save();
        }
        
        return redirect()->route('admin.orders.show', $order->id)
            ->with('success', 'Order created successfully.');
    }

    /**
     * Display the specified order.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $order = Order::with(['writer', 'files'])->findOrFail($id);
        
        // Get all available writers for assignment
        $availableWriters = User::where('usertype', 'writer')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
            
        // Get messages for this order
        $messages = Message::where('order_id', $id)
            ->with('user')
            ->orderBy('created_at')
            ->get();
            
        // Get financial transactions for this order
        $transactions = Finance::where('order_id', $id)
            ->with(['user', 'processor'])
            ->latest()
            ->get();
            
        return view('admin.orders.show', compact(
            'order', 
            'availableWriters', 
            'messages',
            'transactions'
        ));
    }

    /**
     * Show the form for editing the specified order.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $order = Order::findOrFail($id);
        
        $writers = User::where('usertype', 'writer')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
            
        return view('admin.orders.edit', compact('order', 'writers'));
    }

    /**
     * Update the specified order in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'instructions' => 'required|string',
            'price' => 'required|numeric|min:0',
            'deadline' => 'required|date',
            'task_size' => 'required|integer|min:1',
            'discipline' => 'required|string|max:255',
            'type_of_service' => 'required|string|max:255',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $order = Order::findOrFail($id);
        
        // Update order details
        $order->title = $request->input('title');
        $order->instructions = $request->input('instructions');
        $order->price = $request->input('price');
        $order->deadline = Carbon::parse($request->input('deadline'));
        $order->task_size = $request->input('task_size');
        $order->discipline = $request->input('discipline');
        $order->type_of_service = $request->input('type_of_service');
        
        $order->save();
        
        // Handle file uploads
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('order_files');
                
                $orderFile = new File();
                $orderFile->name = $file->getClientOriginalName();
                $orderFile->path = $path;
                $orderFile->size = $file->getSize();
                $orderFile->fileable_id = $order->id;
                $orderFile->fileable_type = get_class($order);
                $orderFile->uploaded_by = Auth::id();
                $orderFile->save();
            }
        }
        
        return redirect()->route('admin.orders.show', $order->id)
            ->with('success', 'Order updated successfully.');
    }

    /**
     * Make an order available to writers.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function makeAvailable($id)
    {
        $order = Order::findOrFail($id);
        
        if ($order->status !== Order::STATUS_AVAILABLE && $order->writer_id === null) {
            $order->status = Order::STATUS_AVAILABLE;
            $order->save();
            
            return redirect()->back()->with('success', 'Order is now available for writers to take.');
        }
        
        return redirect()->back()->with('error', 'Order cannot be made available.');
    }

    /**
     * Assign order to a writer.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function assign(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'writer_id' => 'required|exists:users,id',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        try {
            $order = Order::findOrFail($id);
            $writerId = $request->input('writer_id');
            
            // Check if writer exists and is active
            $writer = User::where('id', $writerId)
                ->where('usertype', 'writer')
                ->where('status', 'active')
                ->first();
                
            if (!$writer) {
                return redirect()->back()
                    ->with('error', 'Selected writer is not available.');
            }
            
            // Assign the order
            $order->writer_id = $writerId;
            $order->status = Order::STATUS_UNCONFIRMED; // Changed to UNCONFIRMED
            $order->save();
            
            // If there's a bid from this writer, handle it safely
            $bid = Bid::where('order_id', $id)
                ->where('user_id', $writerId)
                ->first();
                
            if ($bid) {
                // Check if status column exists before trying to update it
                if (Schema::hasColumn('bids', 'status')) {
                    $bid->status = 'accepted';
                    $bid->save();
                    
                    // Reject all other bids if status column exists
                    Bid::where('order_id', $id)
                        ->where('user_id', '!=', $writerId)
                        ->update(['status' => 'rejected']);
                }
            }
            
            // Notify the writer
            $this->notifyWriterOfAssignment($order);
            
            // Record admin message about assignment
            $message = new Message();
            $message->order_id = $order->id;
            $message->user_id = Auth::id();
            $message->receiver_id = $writerId;
            $message->title = 'Order Assignment';
            $message->message = "Please check this order {$order->id} and let us know if you can finish it by the deadline and inform us incase anything is missing. You may also send a message to the customer if the order contains contradictory information.";
            $message->message_type = 'admin';
            $message->save();
            
            // Use toast notification
            return redirect()->back()->with('toast', [
                'title' => 'Order Assigned',
                'message' => 'Order has been successfully assigned to ' . $writer->name
            ]);
        } catch (\Exception $e) {
            Log::error('Error assigning order: ' . $e->getMessage() . ' - ' . $e->getTraceAsString());
            
            return redirect()->back()
                ->with('error', 'An error occurred while assigning the order: ' . $e->getMessage());
        }
    }

    /**
     * Assign order to a writer from the bid page.
     *
     * @param  int  $orderId
     * @param  int  $writerId
     * @return \Illuminate\Http\Response
     */
    public function assignBid($orderId, $writerId)
    {
        try {
            $order = Order::findOrFail($orderId);
            $writer = User::where('usertype', 'writer')->findOrFail($writerId);
            
            // Ensure order is available
            if ($order->status !== Order::STATUS_AVAILABLE) {
                return redirect()->route('admin.bids')
                    ->with('error', 'This order is no longer available for assignment.');
            }
            
            // Update order status and assign writer
            $order->status = Order::STATUS_UNCONFIRMED; // Changed to UNCONFIRMED
            $order->writer_id = $writerId;
            $order->save();
            
            // If there's a bid from this writer, update it without using 'status' column
            $bid = Bid::where('order_id', $orderId)
                ->where('user_id', $writerId)
                ->first();
                
            if ($bid) {
                // Check if status column exists before trying to update it
                if (Schema::hasColumn('bids', 'status')) {
                    $bid->status = 'accepted';
                    $bid->save();
                    
                    // Reject all other bids if status column exists
                    Bid::where('order_id', $orderId)
                        ->where('user_id', '!=', $writerId)
                        ->update(['status' => 'rejected']);
                }
            }
            
            // Notify writer
            $this->notifyWriterOfAssignment($order);
            
            // Create system message
            Message::create([
                'order_id' => $orderId,
                'user_id' => Auth::id(),
                'receiver_id' => $writerId,
                'message' => "Please check this order {$orderId} and let us know if you can finish it by the deadline and inform us incase anything is missing. You may also send a message to the customer if the order contains contradictory information.",
                'message_type' => 'system',
                'title' => 'Order Assignment',
                'is_general' => false,
                'requires_action' => true
            ]);
            
            // Use session flash for toaster
            return redirect()->route('admin.orders.show', $order->id)
                ->with('toast', [
                    'title' => 'Order Assigned',
                    'message' => 'Order has been successfully assigned to ' . $writer->name
                ]);
                
        } catch (\Exception $e) {
            Log::error('Error assigning order: ' . $e->getMessage() . ' - ' . $e->getTraceAsString());
            
            return redirect()->route('admin.bids')
                ->with('error', 'An error occurred while assigning the order: ' . $e->getMessage());
        }
    }

    /**
     * Helper function to format file size in human-readable format
     *
     * @param int $bytes
     * @param int $precision
     * @return string
     */
    private function humanFilesize($bytes, $precision = 2) 
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB']; 
    
        $bytes = max($bytes, 0); 
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
        $pow = min($pow, count($units) - 1); 
    
        $bytes /= pow(1024, $pow);
    
        return round($bytes, $precision) . ' ' . $units[$pow]; 
    }

    /**
     * Writer confirms order assignment
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function confirmAssignment($id)
    {
        $order = Order::findOrFail($id);
        
        // Check if the order belongs to the current writer
        if ($order->writer_id != Auth::id()) {
            return redirect()->back()->with('error', 'You are not assigned to this order.');
        }
        
        // Check if the order is in UNCONFIRMED status
        if ($order->status != Order::STATUS_UNCONFIRMED) {
            return redirect()->back()->with('error', 'This order cannot be confirmed at this time.');
        }
        
        // Update order status
        $order->status = Order::STATUS_CONFIRMED;
        $order->save();
        
        // Create system message confirming the assignment
        Message::create([
            'order_id' => $id,
            'user_id' => Auth::id(),
            'message' => "Order assignment has been accepted by the writer.",
            'message_type' => 'system',
            'title' => 'Assignment Confirmed'
        ]);
        
        return redirect()->route('writer.orders.show', $id)
            ->with('success', 'You have successfully accepted this order. You can now start working on it.');
    }
    
    /**
     * Writer rejects order assignment
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function rejectAssignment($id)
    {
        $order = Order::findOrFail($id);
        
        // Check if the order belongs to the current writer
        if ($order->writer_id != Auth::id()) {
            return redirect()->back()->with('error', 'You are not assigned to this order.');
        }
        
        // Check if the order is in UNCONFIRMED status
        if ($order->status != Order::STATUS_UNCONFIRMED) {
            return redirect()->back()->with('error', 'This order cannot be rejected at this time.');
        }
        
        // Reset order status and writer
        $order->status = Order::STATUS_AVAILABLE;
        $oldWriterId = $order->writer_id;
        $order->writer_id = null;
        $order->save();
        
        // Create system message recording the rejection
        Message::create([
            'order_id' => $id,
            'user_id' => Auth::id(),
            'message' => "Order assignment has been declined by the writer.",
            'message_type' => 'system',
            'title' => 'Assignment Rejected'
        ]);
        
        // Notify admin about the rejection
        $adminUsers = User::whereIn('usertype', ['admin', 'super_admin'])->get();
        foreach ($adminUsers as $admin) {
            Message::create([
                'order_id' => $id,
                'user_id' => Auth::id(),
                'receiver_id' => $admin->id,
                'message' => "Writer has declined the order assignment. Order is now available again.",
                'message_type' => 'notification',
                'title' => 'Order Assignment Rejected'
            ]);
        }
        
        return redirect()->route('writer.orders.index')
            ->with('info', 'You have declined the order assignment.');
    }

    /**
     * Request revision from writer.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function requestRevision(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'revision_comments' => 'required|string',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $order = Order::findOrFail($id);
        
        // Check if order can be revised
        if (!in_array($order->status, [Order::STATUS_DONE, Order::STATUS_DELIVERED])) {
            return redirect()->back()
                ->with('error', 'This order cannot be revised at this time.');
        }
        
        // Update order status
        $order->status = Order::STATUS_REVISION;
        $order->save();
        
        // Add revision message
        $message = new Message();
        $message->order_id = $order->id;
        $message->user_id = Auth::id();
        $message->receiver_id = $order->writer_id;
        $message->title = 'Revision Request';
        $message->message = $request->input('revision_comments');
        $message->message_type = 'revision';
        $message->save();
        
        // Notify writer of revision request
        $this->notifyWriterOfRevision($order, $request->input('revision_comments'));
        
        return redirect()->back()->with('success', 'Revision requested successfully.');
    }

    /**
     * Mark order as complete and process payment.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function complete($id)
    {
        $order = Order::findOrFail($id);
        
        // Check if order can be completed
        if (!in_array($order->status, [Order::STATUS_DONE, Order::STATUS_DELIVERED])) {
            return redirect()->back()
                ->with('error', 'This order cannot be marked as completed at this time.');
        }
        
        // Update order status
        $order->status = Order::STATUS_COMPLETED;
        $order->save();
        
        // Process payment to writer
        if (method_exists($order, 'processWriterPayment')) {
            $order->processWriterPayment(Auth::id());
        } else {
            // Fallback if method doesn't exist
            Finance::create([
                'order_id' => $order->id,
                'user_id' => $order->writer_id,
                'amount' => $order->price * 0.7, // 70% of order price to writer
                'transaction_type' => 'writer_payment',
                'status' => 'completed',
                'description' => 'Payment for completed order #' . $order->id,
                'processor_id' => Auth::id()
            ]);
        }
        
        // Add completion message
        $message = new Message();
        $message->order_id = $order->id;
        $message->user_id = Auth::id();
        $message->receiver_id = $order->writer_id;
        $message->title = 'Order Completed';
        $message->message = 'This order has been marked as completed. Payment has been processed to your account.';
        $message->message_type = 'admin';
        $message->save();
        
        // Notify writer
        $this->notifyWriterOfCompletion($order);
        
        return redirect()->back()->with('success', 'Order marked as completed and payment processed.');
    }

    /**
     * Mark order as disputed.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function dispute(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'dispute_reason' => 'required|string',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $order = Order::findOrFail($id);
        
        // Update order status
        $order->status = Order::STATUS_DISPUTE;
        $order->save();
        
        // Add dispute message
        $message = new Message();
        $message->order_id = $order->id;
        $message->user_id = Auth::id();
        $message->receiver_id = $order->writer_id;
        $message->title = 'Order Disputed';
        $message->message = $request->input('dispute_reason');
        $message->message_type = 'dispute';
        $message->save();
        
        // Notify writer
        $this->notifyWriterOfDispute($order, $request->input('dispute_reason'));
        
        return redirect()->back()->with('success', 'Order marked as disputed.');
    }

    /**
     * Upload files to an order.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function uploadFiles(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'files' => 'required|array',
            'files.*' => 'file|max:10240', // 10MB max per file
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $order = Order::findOrFail($id);
        
        // Upload each file
        foreach ($request->file('files') as $file) {
            $path = $file->store('order_files');
            
            $orderFile = new File();
            $orderFile->name = $file->getClientOriginalName();
            $orderFile->path = $path;
            $orderFile->size = $file->getSize();
            $orderFile->fileable_id = $order->id;
            $orderFile->fileable_type = get_class($order);
            $orderFile->uploaded_by = Auth::id();
            $orderFile->save();
        }
        
        // Add message about file upload
        $message = new Message();
        $message->order_id = $order->id;
        $message->user_id = Auth::id();
        $message->receiver_id = $order->writer_id;
        $message->title = 'Files Uploaded';
        $message->message = 'New files have been uploaded to this order.';
        $message->message_type = 'admin';
        $message->save();
        
        return redirect()->back()->with('success', 'Files uploaded successfully.');
    }

    /**
     * Delete a file from an order.
     *
     * @param  int  $id
     * @param  int  $fileId
     * @return \Illuminate\Http\Response
     */
    public function deleteFile($id, $fileId)
    {
        $order = Order::findOrFail($id);
        $file = File::where('fileable_id', $order->id)
            ->where('fileable_type', get_class($order))
            ->where('id', $fileId)
            ->firstOrFail();
            
        // Delete the file from storage
        Storage::delete($file->path);
        
        // Delete the file record
        $file->delete();
        
        return redirect()->back()->with('success', 'File deleted successfully.');
    }

    /**
     * Send message as a client.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function sendMessageAsClient(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $order = Order::findOrFail($id);
        
        if (!$order->writer_id) {
            return redirect()->back()->with('error', 'No writer assigned to this order.');
        }
        
        // Create message
        $message = new Message();
        $message->order_id = $order->id;
        $message->user_id = Auth::id();
        $message->receiver_id = $order->writer_id;
        $message->title = 'Message from Client';
        $message->message = $request->input('message');
        $message->message_type = 'client';
        $message->save();
        
        // Upload files if any
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('message_files');
                
                $messageFile = new File();
                $messageFile->name = $file->getClientOriginalName();
                $messageFile->path = $path;
                $messageFile->size = $file->getSize();
                $messageFile->fileable_id = $message->id;
                $messageFile->fileable_type = get_class($message);
                $messageFile->uploaded_by = Auth::id();
                $messageFile->save();
            }
        }
        
        return redirect()->back()->with('success', 'Message sent as client.');
    }

    /**
     * Send message as support.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function sendMessageAsSupport(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $order = Order::findOrFail($id);
        
        if (!$order->writer_id) {
            return redirect()->back()->with('error', 'No writer assigned to this order.');
        }
        
        // Create message
        $message = new Message();
        $message->order_id = $order->id;
        $message->user_id = Auth::id();
        $message->receiver_id = $order->writer_id;
        $message->title = 'Message from Support';
        $message->message = $request->input('message');
        $message->message_type = 'admin';
        $message->save();
        
        // Upload files if any
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('message_files');
                
                $messageFile = new File();
                $messageFile->name = $file->getClientOriginalName();
                $messageFile->path = $path;
                $messageFile->size = $file->getSize();
                $messageFile->fileable_id = $message->id;
                $messageFile->fileable_type = get_class($message);
                $messageFile->uploaded_by = Auth::id();
                $messageFile->save();
            }
        }
        
        return redirect()->back()->with('success', 'Message sent as support.');
    }
    
   
    
    /**
     * Notify writer of order assignment.
     *
     * @param  \App\Models\Order  $order
     * @return void
     */
    protected function notifyWriterOfAssignment($order)
    {
        if (!$order->writer) return;
        
        // Send email notification
        try {
            Mail::to($order->writer->email)->send(new OrderAssigned($order));
            
            // Log successful email
            Log::info('Assignment email sent for order #' . $order->id . ' to writer ' . $order->writer->name);
        } catch (\Exception $e) {
            // Log error but don't prevent assignment
            Log::error('Failed to send order assignment email: ' . $e->getMessage());
        }
    }
        
    /**
     * Notify writer of revision request.
     *
     * @param  \App\Models\Order  $order
     * @param  string  $comments
     * @return void
     */
    protected function notifyWriterOfRevision($order, $comments)
    {
        if (!$order->writer) return;
        
        // Send email notification
        try {
            Mail::to($order->writer->email)->send(new RevisionRequested($order, $comments));
        } catch (\Exception $e) {
            Log::error('Failed to send revision request email: ' . $e->getMessage());
        }
    }

    /**
     * Notify writer of order completion.
     *
     * @param  \App\Models\Order  $order
     * @return void
     */
    protected function notifyWriterOfCompletion($order)
    {
        if (!$order->writer) return;
        
        // Send email notification
        try {
            Mail::to($order->writer->email)->send(new OrderCompleted($order));
        } catch (\Exception $e) {
            Log::error('Failed to send order completion email: ' . $e->getMessage());
        }
    }

    /**
     * Notify writer of order dispute.
     *
     * @param  \App\Models\Order  $order
     * @param  string  $reason
     * @return void
     */
    protected function notifyWriterOfDispute($order, $reason)
    {
        if (!$order->writer) return;
        
        // Send email notification
        try {
            Mail::to($order->writer->email)->send(new OrderDisputed($order, $reason));
        } catch (\Exception $e) {
            Log::error('Failed to send order disputed email: ' . $e->getMessage());
        }
    }
}