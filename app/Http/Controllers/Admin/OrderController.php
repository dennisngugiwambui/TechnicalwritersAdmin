<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Models\File;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class OrderController extends Controller
{
    /**
     * Display a listing of the orders based on status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $status = $request->input('status');
        $search = $request->input('search');
        $sort = $request->input('sort', 'created_desc');
        
        $query = Order::query();
        
        // Apply status filter
        if ($status && $status !== 'all') {
            if ($status === 'in_progress') {
                $query->whereIn('status', ['confirmed', 'in_progress', 'done', 'delivered']);
            } else {
                $query->where('status', $status);
            }
        }
        
        // Apply search filter
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%");
            });
        }
        
        // Apply sorting
        switch ($sort) {
            case 'created_asc':
                $query->orderBy('created_at', 'asc');
                break;
            case 'deadline_asc':
                $query->orderBy('deadline', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'created_desc':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }
        
        // Get counts for each status
        $orderCounts = [
            'all' => Order::count(),
            'available' => Order::where('status', Order::STATUS_AVAILABLE)->count(),
            'in_progress' => Order::whereIn('status', ['confirmed', 'in_progress', 'done', 'delivered'])->count(),
            'revision' => Order::where('status', Order::STATUS_REVISION)->count(),
            'completed' => Order::whereIn('status', [Order::STATUS_COMPLETED, Order::STATUS_PAID, Order::STATUS_FINISHED])->count(),
            'dispute' => Order::where('status', Order::STATUS_DISPUTE)->count(),
            'cancelled' => Order::where('status', Order::STATUS_CANCELLED)->count(),
        ];
        
        $orders = $query->with('writer')->paginate(10);
        
        return view('admin.orders.index', compact('orders', 'orderCounts'));
    }
    
    /**
     * Show the form for creating a new order.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // Get writers for assignment dropdown
        $writers = User::where('usertype', 'writer')->get();
        
        // Get disciplines and service types for dropdowns
        $disciplines = ['Business', 'Mathematics', 'Science', 'History', 'Technology', 'Engineering', 'Literature', 'Art', 'Law', 'Psychology', 'Sociology', 'Economics', 'Other'];
        $serviceTypes = ['Essay', 'Research Paper', 'Case Study', 'Dissertation', 'Term Paper', 'Thesis', 'Coursework', 'Assignment', 'Book Review', 'Article Review', 'Lab Report', 'Other'];
        
        return view('admin.orders.create', compact('writers', 'disciplines', 'serviceTypes'));
    }
    
    /**
     * Store a newly created order in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type_of_service' => 'required|string',
            'discipline' => 'required|string',
            'task_size' => 'required|numeric',
            'deadline' => 'required|date',
            'price' => 'required|numeric',
            'writer_id' => 'nullable|exists:users,id',
            'instructions' => 'required|string',
            'software' => 'nullable|string',
            'status' => 'required|string|in:available,hidden',
            'client_name' => 'nullable|string|max:255',
            'client_email' => 'nullable|email|max:255',
            'customer_comments' => 'nullable|string',
            'files.*' => 'nullable|file|max:10240', // Max 10MB per file
        ]);
        
        // Set client_id to the current authenticated user or a specific client if provided
        $clientId = Auth::id();
        
        // If writer is assigned, set status to confirmed, otherwise use the selected status
        $status = $validated['writer_id'] ? Order::STATUS_CONFIRMED : $validated['status'];
        
        // If status is set to available, ensure it's properly set
        if ($status == 'available') {
            $status = Order::STATUS_AVAILABLE;
        }
        
        // Create the order
        $order = Order::create([
            'title' => $validated['title'],
            'type_of_service' => $validated['type_of_service'],
            'discipline' => $validated['discipline'],
            'task_size' => $validated['task_size'],
            'deadline' => $validated['deadline'],
            'price' => $validated['price'],
            'writer_id' => $validated['writer_id'] ?? null,
            'instructions' => $validated['instructions'],
            'software' => $validated['software'] ?? null,
            'status' => $status,
            'client_id' => $clientId,
            'client_name' => $validated['client_name'] ?? null,
            'client_email' => $validated['client_email'] ?? null,
            'customer_comments' => $validated['customer_comments'] ?? null,
        ]);
        
        // Handle file uploads
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $filename = $file->getClientOriginalName();
                $path = $file->store('order_files');
                
                // Create the file record
                File::create([
                    'name' => $filename,
                    'path' => $path,
                    'size' => $file->getSize(),
                    'fileable_id' => $order->id,
                    'fileable_type' => Order::class,
                    'uploaded_by' => Auth::id()
                ]);
            }
        }
        
        // Create an initial message about the order
        if ($status == Order::STATUS_AVAILABLE) {
            // Create a system message for writers to see this available order
            Message::create([
                'user_id' => Auth::id(),
                'order_id' => $order->id,
                'title' => 'New Order Available',
                'message' => 'A new order has been created and is available for bidding: ' . $validated['title'],
                'message_type' => 'system',
                'is_general' => true,
            ]);
        } elseif ($validated['writer_id']) {
            // Create a message for the assigned writer
            Message::create([
                'user_id' => Auth::id(),
                'receiver_id' => $validated['writer_id'],
                'order_id' => $order->id,
                'title' => 'New Order Assignment',
                'message' => 'You have been assigned to a new order: ' . $validated['title'],
                'message_type' => 'system',
                'is_general' => false,
            ]);
        }
        
        // Log the order creation in status history
        if (method_exists($order, 'statusLogs')) {
            $order->statusLogs()->create([
                'status' => $status,
                'changed_by' => Auth::id(),
                'notes' => 'Order created with initial status: ' . ucfirst($status),
            ]);
        }
        
        return redirect()->route('admin.orders.show', $order->id)
            ->with('success', 'Order created successfully');
    }
    
    /**
     * Display the specified order.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\View\View
     */
    public function show(Order $order)
    {
        // Load relationships
        $order->load(['writer', 'files', 'messages']);
        
        // Get available writers for reassignment
        $writers = User::where('usertype', 'writer')->get();
        
        // Get order status history
        $statusHistory = $order->statusLogs()->orderBy('created_at', 'desc')->get();
        
        // Mark unread messages as read
        $order->messages()
            ->whereNull('read_at')
            ->where('receiver_id', Auth::id())
            ->update(['read_at' => now()]);
        
        return view('admin.orders.show', compact('order', 'writers', 'statusHistory'));
    }
    
    /**
     * Show the form for editing the specified order.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\View\View
     */
    public function edit(Order $order)
    {
        // Get writers for assignment dropdown
        $writers = User::where('usertype', 'writer')->get();
        
        // Get disciplines and service types for dropdowns
        $disciplines = ['Business', 'Mathematics', 'Science', 'History', 'Technology', 'Engineering', 'Literature', 'Art', 'Law', 'Psychology', 'Sociology', 'Economics', 'Other'];
        $serviceTypes = ['Essay', 'Research Paper', 'Case Study', 'Dissertation', 'Term Paper', 'Thesis', 'Coursework', 'Assignment', 'Book Review', 'Article Review', 'Lab Report', 'Other'];
        
        return view('admin.orders.edit', compact('order', 'writers', 'disciplines', 'serviceTypes'));
    }
    
    /**
     * Update the specified order in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Order $order)
    {
        // Validate the request
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type_of_service' => 'required|string',
            'discipline' => 'required|string',
            'task_size' => 'required|numeric',
            'deadline' => 'required|date',
            'price' => 'required|numeric',
            'writer_id' => 'nullable|exists:users,id',
            'instructions' => 'required|string',
            'software' => 'nullable|string',
            'status' => 'required|string',
            'client_name' => 'nullable|string|max:255',
            'client_email' => 'nullable|email|max:255',
            'customer_comments' => 'nullable|string',
            'files.*' => 'nullable|file|max:10240', // Max 10MB per file
        ]);
        
        // Check if writer has changed
        $writerChanged = $order->writer_id !== $validated['writer_id'];
        
        // Check if status has changed
        $statusChanged = $order->status !== $validated['status'];
        
        // Update the order
        $order->update([
            'title' => $validated['title'],
            'type_of_service' => $validated['type_of_service'],
            'discipline' => $validated['discipline'],
            'task_size' => $validated['task_size'],
            'deadline' => $validated['deadline'],
            'price' => $validated['price'],
            'writer_id' => $validated['writer_id'],
            'instructions' => $validated['instructions'],
            'software' => $validated['software'],
            'status' => $validated['status'],
            'client_name' => $validated['client_name'],
            'client_email' => $validated['client_email'],
            'customer_comments' => $validated['customer_comments'],
        ]);
        
        // Handle file uploads
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $filename = $file->getClientOriginalName();
                $path = $file->store('order_files');
                
                // Create the file record
                File::create([
                    'name' => $filename,
                    'path' => $path,
                    'size' => $file->getSize(),
                    'fileable_id' => $order->id,
                    'fileable_type' => Order::class,
                    'uploaded_by' => Auth::id()
                ]);
            }
        }
        
        // Create a status change message if status changed
        if ($statusChanged) {
            // Log status change
            if (method_exists($order, 'statusLogs')) {
                $order->statusLogs()->create([
                    'status' => $validated['status'],
                    'changed_by' => Auth::id(),
                    'notes' => $request->input('status_notes') ?? 'Status updated from ' . $order->getOriginal('status') . ' to ' . $validated['status'],
                ]);
            }
            
            // Create notification message for writers if order became available
            if ($validated['status'] == Order::STATUS_AVAILABLE) {
                Message::create([
                    'user_id' => Auth::id(),
                    'order_id' => $order->id,
                    'title' => 'Order Now Available',
                    'message' => 'Order #' . $order->id . ' is now available for bidding.',
                    'message_type' => 'system',
                    'is_general' => true,
                ]);
            }
            
            // Create notification for the writer
            if ($order->writer_id) {
                Message::create([
                    'user_id' => Auth::id(),
                    'receiver_id' => $order->writer_id,
                    'order_id' => $order->id,
                    'title' => 'Order Status Updated',
                    'message' => 'Order #' . $order->id . ' status has been updated to ' . ucfirst($validated['status']),
                    'message_type' => 'system',
                    'is_general' => false,
                ]);
            }
        }
        
        // Create a notification if writer changed
        if ($writerChanged) {
            // Notify new writer if assigned
            if ($validated['writer_id']) {
                Message::create([
                    'user_id' => Auth::id(),
                    'receiver_id' => $validated['writer_id'],
                    'order_id' => $order->id,
                    'title' => 'Order Assignment',
                    'message' => 'You have been assigned to Order #' . $order->id,
                    'message_type' => 'system',
                    'is_general' => false,
                ]);
            }
            
            // Notify previous writer if unassigned
            if ($order->getOriginal('writer_id')) {
                Message::create([
                    'user_id' => Auth::id(),
                    'receiver_id' => $order->getOriginal('writer_id'),
                    'order_id' => $order->id,
                    'title' => 'Order Unassignment',
                    'message' => 'You have been unassigned from Order #' . $order->id,
                    'message_type' => 'system',
                    'is_general' => false,
                ]);
            }
        }
        
        return redirect()->route('admin.orders.show', $order->id)
            ->with('success', 'Order updated successfully');
    }
    
    /**
     * Make an order available for writers.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\RedirectResponse
     */
    public function makeAvailable(Order $order)
    {
        // Check if order is not already available
        if ($order->status == Order::STATUS_AVAILABLE) {
            return redirect()->back()->with('error', 'Order is already available.');
        }
        
        // Unassign writer if assigned
        $previousWriterId = $order->writer_id;
        
        // Update order status
        $order->update([
            'status' => Order::STATUS_AVAILABLE,
            'writer_id' => null
        ]);
        
        // Log status change
        if (method_exists($order, 'statusLogs')) {
            $order->statusLogs()->create([
                'status' => Order::STATUS_AVAILABLE,
                'changed_by' => Auth::id(),
                'notes' => 'Order made available for writers',
            ]);
        }
        
        // Notify previous writer if there was one
        if ($previousWriterId) {
            Message::create([
                'user_id' => Auth::id(),
                'receiver_id' => $previousWriterId,
                'order_id' => $order->id,
                'title' => 'Order Unassignment',
                'message' => 'You have been unassigned from Order #' . $order->id,
                'message_type' => 'system',
                'is_general' => false,
            ]);
        }
        
        // Create a general message for all writers
        Message::create([
            'user_id' => Auth::id(),
            'order_id' => $order->id,
            'title' => 'New Order Available',
            'message' => 'Order #' . $order->id . ' is now available for bidding.',
            'message_type' => 'system',
            'is_general' => true,
        ]);
        
        return redirect()->back()->with('success', 'Order has been made available for all writers.');
    }
    
    /**
     * Assign an order to a writer.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\RedirectResponse
     */
    public function assign(Request $request, Order $order)
    {
        // Validate the request
        $validated = $request->validate([
            'writer_id' => 'required|exists:users,id',
            'message' => 'nullable|string',
        ]);
        
        // Check if writer exists and is a writer
        $writer = User::findOrFail($validated['writer_id']);
        if ($writer->role !== 'writer') {
            return redirect()->back()->with('error', 'Selected user is not a writer.');
        }
        
        // Update order
        $order->update([
            'writer_id' => $validated['writer_id'],
            'status' => Order::STATUS_CONFIRMED
        ]);
        
        // Log status change
        if (method_exists($order, 'statusLogs')) {
            $order->statusLogs()->create([
                'status' => Order::STATUS_CONFIRMED,
                'changed_by' => Auth::id(),
                'notes' => 'Order assigned to writer: ' . $writer->name,
            ]);
        }
        
        // Create assignment message
        Message::create([
            'user_id' => Auth::id(),
            'receiver_id' => $validated['writer_id'],
            'order_id' => $order->id,
            'title' => 'Order Assignment',
            'message' => $validated['message'] ?? 'You have been assigned to Order #' . $order->id,
            'message_type' => 'system',
            'is_general' => false,
        ]);
        
        return redirect()->back()->with('success', 'Order has been assigned to writer.');
    }
    
    /**
     * Request revision for an order.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\RedirectResponse
     */
    public function requestRevision(Request $request, Order $order)
    {
        // Validate the request
        $validated = $request->validate([
            'revision_instructions' => 'required|string',
            'revision_deadline' => 'nullable|date',
        ]);
        
        // Check if order can be revised
        if (!in_array($order->status, ['done', 'delivered', 'completed'])) {
            return redirect()->back()->with('error', 'Order cannot be revised in its current status.');
        }
        
        // Update order
        $order->update([
            'status' => Order::STATUS_REVISION,
            'revision_deadline' => $validated['revision_deadline'] ?? Carbon::now()->addDays(1),
            'revision_count' => $order->revision_count + 1,
        ]);
        
        // Log status change
        if (method_exists($order, 'statusLogs')) {
            $order->statusLogs()->create([
                'status' => Order::STATUS_REVISION,
                'changed_by' => Auth::id(),
                'notes' => 'Revision requested: ' . $validated['revision_instructions'],
            ]);
        }
        
        // Create revision message
        Message::create([
            'user_id' => Auth::id(),
            'receiver_id' => $order->writer_id,
            'order_id' => $order->id,
            'title' => 'Revision Requested',
            'message' => $validated['revision_instructions'],
            'message_type' => 'revision',
            'is_general' => false,
        ]);
        
        // Handle file uploads if any
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $filename = $file->getClientOriginalName();
                $path = $file->store('order_files');
                
                File::create([
                    'name' => $filename,
                    'path' => $path,
                    'size' => $file->getSize(),
                    'fileable_id' => $order->id,
                    'fileable_type' => Order::class,
                    'uploaded_by' => Auth::id()
                ]);
            }
        }
        
        return redirect()->back()->with('success', 'Revision has been requested.');
    }
    
    /**
     * Mark an order as complete.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\RedirectResponse
     */
    public function complete(Request $request, Order $order)
    {
        // Validate the request
        $validated = $request->validate([
            'completion_notes' => 'nullable|string',
        ]);
        
        // Check if order can be completed
        if (!in_array($order->status, ['done', 'delivered', 'revision'])) {
            return redirect()->back()->with('error', 'Order cannot be completed in its current status.');
        }
        
        // Update order
        $order->update([
            'status' => Order::STATUS_COMPLETED,
            'completed_at' => Carbon::now(),
        ]);
        
        // Log status change
        if (method_exists($order, 'statusLogs')) {
            $order->statusLogs()->create([
                'status' => Order::STATUS_COMPLETED,
                'changed_by' => Auth::id(),
                'notes' => $validated['completion_notes'] ?? 'Order marked as complete',
            ]);
        }
        
        // Create completion message
        Message::create([
            'user_id' => Auth::id(),
            'receiver_id' => $order->writer_id,
            'order_id' => $order->id,
            'title' => 'Order Completed',
            'message' => $validated['completion_notes'] ?? 'Order has been marked as complete.',
            'message_type' => 'system',
            'is_general' => false,
        ]);
        
        return redirect()->back()->with('success', 'Order has been marked as complete.');
    }
    
    /**
     * Mark an order as disputed.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\RedirectResponse
     */
    public function dispute(Request $request, Order $order)
    {
        // Validate the request
        $validated = $request->validate([
            'dispute_reason' => 'required|string',
            'files.*' => 'nullable|file|max:10240', // Max 10MB per file
        ]);
        
        // Update order
        $order->update([
            'status' => Order::STATUS_DISPUTE,
            'dispute_reason' => $validated['dispute_reason'],
            'disputed_at' => Carbon::now(),
        ]);
        
        // Log status change
        if (method_exists($order, 'statusLogs')) {
            $order->statusLogs()->create([
                'status' => Order::STATUS_DISPUTE,
                'changed_by' => Auth::id(),
                'notes' => 'Dispute reason: ' . $validated['dispute_reason'],
            ]);
        }
        
        // Create dispute message
        Message::create([
            'user_id' => Auth::id(),
            'receiver_id' => $order->writer_id,
            'order_id' => $order->id,
            'title' => 'Order Disputed',
            'message' => $validated['dispute_reason'],
            'message_type' => 'dispute',
            'is_general' => true, // Make visible to all admin staff
        ]);
        
        // Handle file uploads if any
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $filename = $file->getClientOriginalName();
                $path = $file->store('order_files');
                
                File::create([
                    'name' => $filename,
                    'path' => $path,
                    'size' => $file->getSize(),
                    'fileable_id' => $order->id,
                    'fileable_type' => Order::class,
                    'uploaded_by' => Auth::id()
                ]);
            }
        }
        
        return redirect()->back()->with('success', 'Order has been marked as disputed.');
    }
    
    /**
     * Cancel an order.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cancel(Request $request, Order $order)
    {
        // Validate the request
        $validated = $request->validate([
            'cancellation_reason' => 'required|string',
        ]);
        
        // Check if order can be cancelled
        if (in_array($order->status, ['completed', 'paid', 'finished'])) {
            return redirect()->back()->with('error', 'Completed orders cannot be cancelled.');
        }
        
        // Update order
        $order->update([
            'status' => Order::STATUS_CANCELLED,
            'cancellation_reason' => $validated['cancellation_reason'],
            'cancelled_at' => Carbon::now(),
        ]);
        
        // Log status change
        if (method_exists($order, 'statusLogs')) {
            $order->statusLogs()->create([
                'status' => Order::STATUS_CANCELLED,
                'changed_by' => Auth::id(),
                'notes' => 'Cancellation reason: ' . $validated['cancellation_reason'],
            ]);
        }
        
        // Create cancellation message for writer if assigned
        if ($order->writer_id) {
            Message::create([
                'user_id' => Auth::id(),
                'receiver_id' => $order->writer_id,
                'order_id' => $order->id,
                'title' => 'Order Cancelled',
                'message' => 'Order has been cancelled. Reason: ' . $validated['cancellation_reason'],
                'message_type' => 'system',
                'is_general' => false,
            ]);
        }
        
        return redirect()->back()->with('success', 'Order has been cancelled.');
    }
    
    /**
     * Upload files for an order.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function uploadFiles(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'file_description' => 'required|string',
            'files.*' => 'required|file|max:10240', // Max 10MB per file
        ]);
        
        $order = Order::findOrFail($validated['order_id']);
        
        // Handle file uploads
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $filename = $file->getClientOriginalName();
                $path = $file->store('order_files');
                
                // Create the file record
                File::create([
                    'name' => $filename,
                    'path' => $path,
                    'size' => $file->getSize(),
                    'fileable_id' => $order->id,
                    'fileable_type' => Order::class,
                    'uploaded_by' => Auth::id()
                ]);
            }
            
            // Create message about new files
            Message::create([
                'user_id' => Auth::id(),
                'receiver_id' => $order->writer_id,
                'order_id' => $order->id,
                'title' => 'New Files Uploaded',
                'message' => 'New files have been uploaded: ' . $validated['file_description'],
                'message_type' => 'system',
                'is_general' => false,
            ]);
            
            return redirect()->back()->with('success', 'Files uploaded successfully.');
        }
        
        return redirect()->back()->with('error', 'No files were uploaded.');
    }
    
    /**
     * Resolve a disputed order.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resolveDispute(Request $request, Order $order)
    {
        // Validate the request
        $validated = $request->validate([
            'resolution_action' => 'required|in:complete,revise,cancel',
            'resolution_notes' => 'required|string',
        ]);
        
        // Check if order is in dispute
        if ($order->status != Order::STATUS_DISPUTE) {
            return redirect()->back()->with('error', 'Only disputed orders can be resolved.');
        }
        
        $statusAction = '';
        
        // Apply the resolution action
        switch ($validated['resolution_action']) {
            case 'complete':
                $order->update([
                    'status' => Order::STATUS_COMPLETED,
                    'completed_at' => Carbon::now(),
                    'dispute_resolved_at' => Carbon::now(),
                ]);
                $statusAction = 'completed';
                break;
                
                case 'revise':
                    $order->update([
                        'status' => Order::STATUS_REVISION,
                        'revision_count' => $order->revision_count + 1,
                        'dispute_resolved_at' => Carbon::now(),
                    ]);
                    $statusAction = 'sent back for revision';
                    break;
                    
                case 'cancel':
                    $order->update([
                        'status' => Order::STATUS_CANCELLED,
                        'cancelled_at' => Carbon::now(),
                        'dispute_resolved_at' => Carbon::now(),
                        'cancellation_reason' => 'Cancelled after dispute resolution: ' . $validated['resolution_notes'],
                    ]);
                    $statusAction = 'cancelled';
                    break;
            }
            
            // Log status change
            if (method_exists($order, 'statusLogs')) {
                $order->statusLogs()->create([
                    'status' => $order->status,
                    'changed_by' => Auth::id(),
                    'notes' => 'Dispute resolution: ' . $validated['resolution_notes'],
                ]);
            }
            
            // Create resolution message
            Message::create([
                'user_id' => Auth::id(),
                'receiver_id' => $order->writer_id,
                'order_id' => $order->id,
                'title' => 'Dispute Resolved',
                'message' => 'The dispute for Order #' . $order->id . ' has been resolved. The order has been ' . $statusAction . '. Notes: ' . $validated['resolution_notes'],
                'message_type' => 'system',
                'is_general' => true,
            ]);
            
            return redirect()->back()->with('success', 'Dispute has been resolved successfully.');
        }
        
        /**
         * Process payment for a completed order.
         *
         * @param  \App\Models\Order  $order
         * @return \Illuminate\Http\RedirectResponse
         */
        public function processPayment(Order $order)
        {
            // Check if order is completed and not already paid
            if (!$order->isCompleted() || $order->isPaid()) {
                return redirect()->back()->with('error', 'Payment cannot be processed for this order.');
            }
            
            // Process the payment
            $transaction = $order->processWriterPayment(Auth::id());
            
            if ($transaction) {
                // Log status change
                if (method_exists($order, 'statusLogs')) {
                    $order->statusLogs()->create([
                        'status' => $order->status,
                        'changed_by' => Auth::id(),
                        'notes' => 'Payment processed for writer: $' . number_format($transaction->amount, 2),
                    ]);
                }
                
                // Create payment message
                Message::create([
                    'user_id' => Auth::id(),
                    'receiver_id' => $order->writer_id,
                    'order_id' => $order->id,
                    'title' => 'Payment Processed',
                    'message' => 'Payment of $' . number_format($transaction->amount, 2) . ' has been processed for Order #' . $order->id,
                    'message_type' => 'system',
                    'is_general' => false,
                ]);
                
                return redirect()->back()->with('success', 'Payment has been processed successfully.');
            }
            
            return redirect()->back()->with('error', 'Failed to process payment. Please try again.');
        }
        
        /**
         * Export orders to CSV.
         *
         * @param  \Illuminate\Http\Request  $request
         * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
         */
        public function export(Request $request)
        {
            $query = Order::query();
            
            // Apply filters
            if ($request->has('status') && $request->status != 'all') {
                if ($request->status === 'in_progress') {
                    $query->whereIn('status', ['confirmed', 'in_progress', 'done', 'delivered']);
                } else {
                    $query->where('status', $request->status);
                }
            }
            
            if ($request->has('writer_id') && $request->writer_id) {
                $query->where('writer_id', $request->writer_id);
            }
            
            if ($request->has('date_from') && $request->date_from) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            
            if ($request->has('date_to') && $request->date_to) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }
            
            // Load relationships
            $orders = $query->with(['writer', 'client'])->get();
            
            // Create CSV
            $filename = 'orders_export_' . date('Y-m-d_His') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];
            
            $callback = function() use ($orders) {
                $file = fopen('php://output', 'w');
                
                // Add CSV headers
                fputcsv($file, [
                    'Order ID',
                    'Title',
                    'Status',
                    'Type of Service',
                    'Discipline',
                    'Task Size',
                    'Price',
                    'Writer',
                    'Client',
                    'Deadline',
                    'Created Date',
                    'Completed Date'
                ]);
                
                // Add order data
                foreach ($orders as $order) {
                    fputcsv($file, [
                        $order->id,
                        $order->title,
                        ucfirst($order->status),
                        $order->type_of_service,
                        $order->discipline,
                        $order->task_size,
                        '$' . number_format($order->price, 2),
                        $order->writer ? $order->writer->name : 'Unassigned',
                        $order->client ? $order->client->name : ($order->client_name ?? 'Unknown'),
                        $order->deadline->format('Y-m-d H:i'),
                        $order->created_at->format('Y-m-d H:i'),
                        $order->completed_at ? $order->completed_at->format('Y-m-d H:i') : 'N/A'
                    ]);
                }
                
                fclose($file);
            };
            
            return response()->stream($callback, 200, $headers);
        }
        
        /**
         * Import orders from CSV.
         *
         * @param  \Illuminate\Http\Request  $request
         * @return \Illuminate\Http\RedirectResponse
         */
        public function import(Request $request)
        {
            // Validate the request
            $request->validate([
                'csv_file' => 'required|file|mimes:csv,txt|max:10240',
            ]);
            
            $file = $request->file('csv_file');
            $filePath = $file->path();
            
            $csv = array_map('str_getcsv', file($filePath));
            $headers = array_shift($csv);
            
            // Map CSV headers to database fields
            $requiredHeaders = ['title', 'type_of_service', 'discipline', 'task_size', 'price', 'deadline', 'instructions'];
            $headerMap = [];
            
            foreach ($headers as $index => $header) {
                $header = strtolower(trim($header));
                
                if (in_array($header, $requiredHeaders) || $header == 'status' || $header == 'writer_id' || $header == 'client_name' || $header == 'client_email') {
                    $headerMap[$header] = $index;
                }
            }
            
            // Check if all required headers are present
            $missingHeaders = array_diff($requiredHeaders, array_keys($headerMap));
            if (!empty($missingHeaders)) {
                return redirect()->back()->with('error', 'CSV is missing required columns: ' . implode(', ', $missingHeaders));
            }
            
            // Process the CSV rows
            $imported = 0;
            $errors = [];
            
            foreach ($csv as $row) {
                try {
                    $orderData = [
                        'title' => $row[$headerMap['title']],
                        'type_of_service' => $row[$headerMap['type_of_service']],
                        'discipline' => $row[$headerMap['discipline']],
                        'task_size' => $row[$headerMap['task_size']],
                        'price' => $row[$headerMap['price']],
                        'deadline' => date('Y-m-d H:i:s', strtotime($row[$headerMap['deadline']])),
                        'instructions' => $row[$headerMap['instructions']],
                        'status' => isset($headerMap['status']) ? $row[$headerMap['status']] : Order::STATUS_AVAILABLE,
                        'writer_id' => isset($headerMap['writer_id']) ? $row[$headerMap['writer_id']] : null,
                        'client_id' => Auth::id(),
                        'client_name' => isset($headerMap['client_name']) ? $row[$headerMap['client_name']] : null,
                        'client_email' => isset($headerMap['client_email']) ? $row[$headerMap['client_email']] : null,
                    ];
                    
                    // Create the order
                    $order = Order::create($orderData);
                    
                    // Log the order creation
                    if (method_exists($order, 'statusLogs')) {
                        $order->statusLogs()->create([
                            'status' => $order->status,
                            'changed_by' => Auth::id(),
                            'notes' => 'Order imported from CSV',
                        ]);
                    }
                    
                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = 'Error on row ' . ($imported + 1) . ': ' . $e->getMessage();
                }
            }
            
            $message = 'Successfully imported ' . $imported . ' orders.';
            if (!empty($errors)) {
                $message .= ' Errors encountered: ' . count($errors);
            }
            
            return redirect()->route('admin.orders.index')->with('success', $message);
        }
        
        /**
         * Search for orders based on various criteria.
         *
         * @param  \Illuminate\Http\Request  $request
         * @return \Illuminate\View\View
         */
        public function search(Request $request)
        {
            $query = Order::query();
            
            // Apply search filters
            if ($request->has('keyword') && $request->keyword) {
                $keyword = $request->keyword;
                $query->where(function($q) use ($keyword) {
                    $q->where('id', 'like', "%{$keyword}%")
                      ->orWhere('title', 'like', "%{$keyword}%")
                      ->orWhere('instructions', 'like', "%{$keyword}%");
                });
            }
            
            if ($request->has('status') && $request->status && $request->status != 'all') {
                if ($request->status === 'in_progress') {
                    $query->whereIn('status', ['confirmed', 'in_progress', 'done', 'delivered']);
                } else {
                    $query->where('status', $request->status);
                }
            }
            
            if ($request->has('writer_id') && $request->writer_id) {
                $query->where('writer_id', $request->writer_id);
            }
            
            if ($request->has('discipline') && $request->discipline) {
                $query->where('discipline', $request->discipline);
            }
            
            if ($request->has('type_of_service') && $request->type_of_service) {
                $query->where('type_of_service', $request->type_of_service);
            }
            
            if ($request->has('date_from') && $request->date_from) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            
            if ($request->has('date_to') && $request->date_to) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }
            
            if ($request->has('price_min') && $request->price_min) {
                $query->where('price', '>=', $request->price_min);
            }
            
            if ($request->has('price_max') && $request->price_max) {
                $query->where('price', '<=', $request->price_max);
            }
            
            // Get order counts for facets
            $orderCounts = [
                'all' => Order::count(),
                'available' => Order::where('status', Order::STATUS_AVAILABLE)->count(),
                'in_progress' => Order::whereIn('status', ['confirmed', 'in_progress', 'done', 'delivered'])->count(),
                'revision' => Order::where('status', Order::STATUS_REVISION)->count(),
                'completed' => Order::whereIn('status', [Order::STATUS_COMPLETED, Order::STATUS_PAID, Order::STATUS_FINISHED])->count(),
                'dispute' => Order::where('status', Order::STATUS_DISPUTE)->count(),
                'cancelled' => Order::where('status', Order::STATUS_CANCELLED)->count(),
            ];
            
            // Apply sorting
            $sort = $request->input('sort', 'created_desc');
            switch ($sort) {
                case 'created_asc':
                    $query->orderBy('created_at', 'asc');
                    break;
                case 'deadline_asc':
                    $query->orderBy('deadline', 'asc');
                    break;
                case 'price_desc':
                    $query->orderBy('price', 'desc');
                    break;
                case 'price_asc':
                    $query->orderBy('price', 'asc');
                    break;
                case 'created_desc':
                default:
                    $query->orderBy('created_at', 'desc');
                    break;
            }
            
            // Paginate the results
            $orders = $query->with(['writer', 'client'])->paginate(10)->appends($request->query());
            
            // Get available writers for filter
            $writers = User::where('role', 'writer')->get();
            
            // Get disciplines and service types for filters
            $disciplines = Order::distinct('discipline')->pluck('discipline')->filter()->toArray();
            $serviceTypes = Order::distinct('type_of_service')->pluck('type_of_service')->filter()->toArray();
            
            return view('admin.orders.search', compact('orders', 'orderCounts', 'writers', 'disciplines', 'serviceTypes'));
        }
        
        /**
         * Get order statistics.
         *
         * @param  \Illuminate\Http\Request  $request
         * @return \Illuminate\Http\JsonResponse
         * @return \
         */
        public function getStatistics(Request $request)
        {
            // Set date range
            $startDate = $request->has('start_date') ? Carbon::parse($request->start_date) : Carbon::now()->subDays(30);
            $endDate = $request->has('end_date') ? Carbon::parse($request->end_date) : Carbon::now();
            
            // Get order counts by status
            $ordersByStatus = [
                'available' => Order::where('status', Order::STATUS_AVAILABLE)->count(),
                'in_progress' => Order::whereIn('status', ['confirmed', 'in_progress', 'done', 'delivered'])->count(),
                'revision' => Order::where('status', Order::STATUS_REVISION)->count(),
                'completed' => Order::whereIn('status', [Order::STATUS_COMPLETED, Order::STATUS_PAID, Order::STATUS_FINISHED])->count(),
                'dispute' => Order::where('status', Order::STATUS_DISPUTE)->count(),
                'cancelled' => Order::where('status', Order::STATUS_CANCELLED)->count(),
            ];
            
            // Get orders created per day in the date range
            $ordersCreatedByDay = Order::whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->date => $item->count];
                });
            
            // Get orders completed per day in the date range
            $ordersCompletedByDay = Order::whereIn('status', [Order::STATUS_COMPLETED, Order::STATUS_PAID, Order::STATUS_FINISHED])
                ->whereBetween('completed_at', [$startDate, $endDate])
                ->selectRaw('DATE(completed_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->date => $item->count];
                });
            
            // Get top 5 writers by completed orders
            $topWriters = Order::whereIn('status', [Order::STATUS_COMPLETED, Order::STATUS_PAID, Order::STATUS_FINISHED])
                ->selectRaw('writer_id, COUNT(*) as count')
                ->whereNotNull('writer_id')
                ->groupBy('writer_id')
                ->orderByDesc('count')
                ->limit(5)
                ->with('writer:id,name')
                ->get()
                ->map(function ($item) {
                    return [
                        'writer_name' => $item->writer ? $item->writer->name : 'Unknown',
                        'count' => $item->count,
                    ];
                });
            
            // Get top 5 disciplines
            $topDisciplines = Order::whereNotNull('discipline')
                ->selectRaw('discipline, COUNT(*) as count')
                ->groupBy('discipline')
                ->orderByDesc('count')
                ->limit(5)
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'ordersByStatus' => $ordersByStatus,
                    'ordersCreatedByDay' => $ordersCreatedByDay,
                    'ordersCompletedByDay' => $ordersCompletedByDay,
                    'topWriters' => $topWriters,
                    'topDisciplines' => $topDisciplines,
                ]
            ]);
        }
     }