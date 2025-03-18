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
            'available' => Order::where('status', 'available')->count(),
            'in_progress' => Order::whereIn('status', ['confirmed', 'in_progress', 'done', 'delivered'])->count(),
            'revision' => Order::where('status', 'revision')->count(),
            'completed' => Order::whereIn('status', ['completed', 'paid', 'finished'])->count(),
            'dispute' => Order::where('status', 'dispute')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
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
        $writers = User::where('role', 'writer')->get();
        
        // Get disciplines and service types for dropdowns
        $disciplines = ['Business', 'Mathematics', 'Science', 'History', 'Technology', 'Engineering', 'Literature', 'Art', 'Law', 'Psychology', 'Sociology', 'Economics', 'Other'];
        $serviceTypes = ['Essay', 'Research Paper', 'Case Study', 'Dissertation', 'Term Paper', 'Thesis', 'Coursework', 'Assignment', 'Book Review', 'Article Review', 'Lab Report', 'Other'];
        
        return view('createOrder', compact('writers', 'disciplines', 'serviceTypes'));
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
            'instructions' => 'nullable|string',
            'client_name' => 'nullable|string|max:255',
            'client_email' => 'nullable|email|max:255',
            'files.*' => 'nullable|file|max:10240', // Max 10MB per file
        ]);
        
        // Create the order
        $order = Order::create([
            'title' => $validated['title'],
            'type_of_service' => $validated['type_of_service'],
            'discipline' => $validated['discipline'],
            'task_size' => $validated['task_size'],
            'deadline' => $validated['deadline'],
            'price' => $validated['price'],
            'writer_id' => $validated['writer_id'] ?? null,
            'instructions' => $validated['instructions'] ?? null,
            'client_name' => $validated['client_name'] ?? null,
            'client_email' => $validated['client_email'] ?? null,
            'status' => $validated['writer_id'] ? 'confirmed' : 'available',
            'created_by' => Auth::id(),
        ]);
        
        // Handle file uploads
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('order_files');
                
                File::create([
                    'order_id' => $order->id,
                    'user_id' => Auth::id(),
                    'filename' => $file->getClientOriginalName(),
                    'filepath' => $path,
                    'filesize' => $file->getSize(),
                    'filetype' => $file->getClientMimeType(),
                    'file_description' => 'Client Instructions',
                ]);
            }
        }
        
        // Create an initial message about the order
        Message::create([
            'user_id' => Auth::id(),
            'receiver_id' => $validated['writer_id'] ?? null,
            'order_id' => $order->id,
            'title' => 'New Order Created',
            'message' => 'A new order has been created: ' . $validated['title'],
            'message_type' => 'system',
            'is_general' => true,
        ]);
        
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
        $writers = User::where('role', 'writer')->get();
        
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
        $writers = User::where('role', 'writer')->get();
        
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
            'instructions' => 'nullable|string',
            'status' => 'required|string|in:available,confirmed,in_progress,done,delivered,revision,completed,paid,finished,dispute,cancelled',
            'client_name' => 'nullable|string|max:255',
            'client_email' => 'nullable|email|max:255',
            'files.*' => 'nullable|file|max:10240', // Max 10MB per file
        ]);
        
        // Check if writer has changed
        $writerChanged = $order->writer_id !== $validated['writer_id'];
        
        // Check if status has changed
        $statusChanged = $order->status !== $validated['status'];
        
        // Update the order
        $order->update($validated);
        
        // Handle file uploads
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('order_files');
                
                File::create([
                    'order_id' => $order->id,
                    'user_id' => Auth::id(),
                    'filename' => $file->getClientOriginalName(),
                    'filepath' => $path,
                    'filesize' => $file->getSize(),
                    'filetype' => $file->getClientMimeType(),
                    'file_description' => $request->input('file_description', 'Admin Upload'),
                ]);
            }
        }
        
        // Create a status change message if status changed
        if ($statusChanged) {
            // Log status change
            $order->statusLogs()->create([
                'status' => $validated['status'],
                'changed_by' => Auth::id(),
                'notes' => $request->input('status_notes'),
            ]);
            
            // Create notification message
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
            if ($order->writer_id) {
                Message::create([
                    'user_id' => Auth::id(),
                    'receiver_id' => $order->writer_id,
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
        if ($order->status == 'available') {
            return redirect()->back()->with('error', 'Order is already available.');
        }
        
        // Unassign writer if assigned
        $previousWriterId = $order->writer_id;
        
        // Update order status
        $order->update([
            'status' => 'available',
            'writer_id' => null
        ]);
        
        // Log status change
        $order->statusLogs()->create([
            'status' => 'available',
            'changed_by' => Auth::id(),
            'notes' => 'Order made available for writers',
        ]);
        
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
        
        return redirect()->back()->with('success', 'Order has been made available.');
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
            'status' => 'confirmed'
        ]);
        
        // Log status change
        $order->statusLogs()->create([
            'status' => 'confirmed',
            'changed_by' => Auth::id(),
            'notes' => 'Order assigned to writer: ' . $writer->name,
        ]);
        
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
            'status' => 'revision',
            'revision_deadline' => $validated['revision_deadline'] ?? Carbon::now()->addDays(1),
            'revision_count' => $order->revision_count + 1,
        ]);
        
        // Log status change
        $order->statusLogs()->create([
            'status' => 'revision',
            'changed_by' => Auth::id(),
            'notes' => 'Revision requested: ' . $validated['revision_instructions'],
        ]);
        
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
                $path = $file->store('order_files');
                
                File::create([
                    'order_id' => $order->id,
                    'user_id' => Auth::id(),
                    'filename' => $file->getClientOriginalName(),
                    'filepath' => $path,
                    'filesize' => $file->getSize(),
                    'filetype' => $file->getClientMimeType(),
                    'file_description' => 'Revision Instructions',
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
            'status' => 'completed',
            'completed_at' => Carbon::now(),
        ]);
        
        // Log status change
        $order->statusLogs()->create([
            'status' => 'completed',
            'changed_by' => Auth::id(),
            'notes' => $validated['completion_notes'] ?? 'Order marked as complete',
        ]);
        
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
            'status' => 'dispute',
            'dispute_reason' => $validated['dispute_reason'],
            'disputed_at' => Carbon::now(),
        ]);
        
        // Log status change
        $order->statusLogs()->create([
            'status' => 'dispute',
            'changed_by' => Auth::id(),
            'notes' => 'Dispute reason: ' . $validated['dispute_reason'],
        ]);
        
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
                $path = $file->store('order_files');
                
                File::create([
                    'order_id' => $order->id,
                    'user_id' => Auth::id(),
                    'filename' => $file->getClientOriginalName(),
                    'filepath' => $path,
                    'filesize' => $file->getSize(),
                    'filetype' => $file->getClientMimeType(),
                    'file_description' => 'Dispute Evidence',
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
            'status' => 'cancelled',
            'cancellation_reason' => $validated['cancellation_reason'],
            'cancelled_at' => Carbon::now(),
        ]);
        
        // Log status change
        $order->statusLogs()->create([
            'status' => 'cancelled',
            'changed_by' => Auth::id(),
            'notes' => 'Cancellation reason: ' . $validated['cancellation_reason'],
        ]);
        
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
                $path = $file->store('order_files');
                
                File::create([
                    'order_id' => $order->id,
                    'user_id' => Auth::id(),
                    'filename' => $file->getClientOriginalName(),
                    'filepath' => $path,
                    'filesize' => $file->getSize(),
                    'filetype' => $file->getClientMimeType(),
                    'file_description' => $validated['file_description'],
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
}