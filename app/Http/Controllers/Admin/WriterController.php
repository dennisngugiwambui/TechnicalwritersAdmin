<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WriterProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WriterController extends Controller
{
    /**
     * Display a listing of the writers.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = User::where('usertype', User::ROLE_WRITER)
            ->with('writerProfile');
            
        // Apply search if provided
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhereHas('writerProfile', function($q2) use ($search) {
                      $q2->where('writer_id', 'like', "%{$search}%");
                  });
            });
        }
        
        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'suspended') {
                $query->where(function($q) {
                    $q->where('status', 'suspended')
                      ->orWhere('is_suspended', 'yes');
                });
            } else {
                $query->where('status', $request->status);
            }
        }
        
        // Filter by verification status
        if ($request->filled('verification')) {
            $query->whereHas('writerProfile', function($q) use ($request) {
                $q->where('id_verification_status', $request->verification);
            });
        }
        
        // Get writers statistics
        $stats = [
            'active' => User::where('usertype', User::ROLE_WRITER)
                ->where('status', User::STATUS_ACTIVE)
                ->count(),
                
            'pending' => User::where('usertype', User::ROLE_WRITER)
                ->whereHas('writerProfile', function($q) {
                    $q->where('id_verification_status', 'pending');
                })
                ->count(),
                
            'suspended' => User::where('usertype', User::ROLE_WRITER)
                ->where(function($q) {
                    $q->where('status', 'suspended')
                      ->orWhere('is_suspended', 'yes');
                })
                ->count(),
                
            'avg_rating' => User::where('usertype', User::ROLE_WRITER)
                ->where('status', User::STATUS_ACTIVE)
                ->avg('rating') ?? 0
        ];
        
        // Get paginated writers
        $writers = $query->latest()->paginate(16)->withQueryString();
        
        return view('admin.writers', compact('writers', 'stats'));
    }
    
        /**
     * Reply to a message.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function reply(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string',
        ]);
        
        // Determine if this is a reply to an order or a direct message
        if ($request->has('order_id')) {
            $orderId = $request->order_id;
            $replyAs = $request->reply_as ?? 'support';
            
            // Create message depending on who we're replying as
            if ($replyAs === 'client') {
                // Get the client ID from the order
                $order = Order::findOrFail($orderId);
                
                // Reply as client
                $newMessage = Message::create([
                    'order_id' => $orderId,
                    'user_id' => $order->client_id,
                    'receiver_id' => null, // To all (including assigned writer)
                    'title' => 'RE: Order #' . $orderId,
                    'message' => $request->message,
                    'message_type' => 'client_message',
                    'is_general' => false,
                ]);
            } else {
                // Reply as support
                $newMessage = Message::create([
                    'order_id' => $orderId,
                    'user_id' => auth()->id(),
                    'receiver_id' => null, // To all (including client and assigned writer)
                    'title' => 'RE: Order #' . $orderId,
                    'message' => $request->message,
                    'message_type' => 'support_message',
                    'is_general' => false,
                ]);
            }
        } else {
            // Direct message reply
            $newMessage = Message::create([
                'user_id' => auth()->id(),
                'receiver_id' => $request->receiver_id,
                'title' => 'RE: ' . ($request->title ?? 'No Subject'),
                'message' => $request->message,
                'message_type' => 'direct_message',
                'is_general' => false,
            ]);
        }
        
        // Handle file attachments if your application supports it
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('uploads', $fileName, 'public');
                
                $newMessage->files()->create([
                    'name' => $fileName,
                    'path' => $filePath,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'uploaded_by' => auth()->id(),
                ]);
            }
        }
        
        return redirect()->route('admin.messages.show', $newMessage->id)
            ->with('success', 'Your reply has been sent successfully.');
    }
    
    /**
     * Suspend the specified writer.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function suspend($id)
    {
        $writer = User::where('usertype', User::ROLE_WRITER)->findOrFail($id);
        $writer->status = User::STATUS_SUSPENDED;
        $writer->is_suspended = 'yes';
        $writer->save();
        
        return redirect()->back()->with('success', "Writer {$writer->name} has been suspended.");
    }
    
    /**
     * Activate the specified writer.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function activate($id)
    {
        $writer = User::where('usertype', User::ROLE_WRITER)->findOrFail($id);
        $writer->status = User::STATUS_ACTIVE;
        $writer->is_suspended = 'no';
        $writer->save();
        
        return redirect()->back()->with('success', "Writer {$writer->name} has been activated.");
    }
    
    /**
     * Verify the specified writer's ID.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verify($id)
    {
        $writer = User::where('usertype', User::ROLE_WRITER)->findOrFail($id);
        
        if (!$writer->writerProfile) {
            return redirect()->back()->with('error', "Writer profile not found.");
        }
        
        $writer->writerProfile->id_verification_status = 'verified';
        $writer->writerProfile->id_rejection_reason = null;
        $writer->writerProfile->save();
        
        return redirect()->back()->with('success', "Writer {$writer->name} has been verified.");
    }
    
    /**
     * Reject the specified writer's ID verification.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'nullable|string|max:500',
        ]);
        
        $writer = User::where('usertype', User::ROLE_WRITER)->findOrFail($id);
        
        if (!$writer->writerProfile) {
            return redirect()->back()->with('error', "Writer profile not found.");
        }
        
        $writer->writerProfile->id_verification_status = 'rejected';
        $writer->writerProfile->id_rejection_reason = $request->rejection_reason;
        $writer->writerProfile->save();
        
        return redirect()->back()->with('success', "Writer {$writer->name} verification has been rejected.");
    }
}