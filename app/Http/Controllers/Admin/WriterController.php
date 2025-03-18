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
     * Display the specified writer.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $writer = User::where('usertype', User::ROLE_WRITER)
            ->with([
                'writerProfile',
                'ordersAsWriter' => function($query) {
                    $query->latest()->limit(10);
                },
                'financialTransactions' => function($query) {
                    $query->latest()->limit(10);
                }
            ])
            ->findOrFail($id);
            
        // Get writer statistics
        $stats = [
            'total_orders' => $writer->ordersAsWriter()->count(),
            'completed_orders' => $writer->ordersAsWriter()
                ->whereIn('status', [
                    'completed', 'paid', 'finished'
                ])
                ->count(),
            'in_progress' => $writer->ordersAsWriter()
                ->whereIn('status', [
                    'confirmed', 'in_progress', 'done', 'delivered'
                ])
                ->count(),
            'revision_orders' => $writer->ordersAsWriter()
                ->where('status', 'revision')
                ->count(),
            'dispute_orders' => $writer->ordersAsWriter()
                ->where('status', 'dispute')
                ->count(),
            'total_earnings' => $writer->writerProfile->earnings ?? 0,
            'available_balance' => $writer->getAvailableBalance(),
            'pending_withdrawals' => $writer->financialTransactions()
                ->where('transaction_type', 'withdrawal')
                ->where('status', 'pending')
                ->sum('amount')
        ];
        
        // Get orders by discipline
        $ordersByDiscipline = $writer->ordersAsWriter()
            ->select('discipline', DB::raw('count(*) as count'))
            ->whereNotNull('discipline')
            ->groupBy('discipline')
            ->orderByDesc('count')
            ->limit(5)
            ->get();
            
        // Get recent activity
        $recentActivity = collect();
        
        // Add recent orders
        $recentOrders = $writer->ordersAsWriter()->latest()->limit(5)->get();
        foreach ($recentOrders as $order) {
            $recentActivity->push([
                'type' => 'order',
                'data' => $order,
                'date' => $order->created_at
            ]);
        }
        
        // Add recent transactions
        $recentTransactions = $writer->financialTransactions()->latest()->limit(5)->get();
        foreach ($recentTransactions as $transaction) {
            $recentActivity->push([
                'type' => 'transaction',
                'data' => $transaction,
                'date' => $transaction->created_at
            ]);
        }
        
        // Sort by date
        $recentActivity = $recentActivity->sortByDesc('date')->take(10);
        
        return view('admin.writers.show', compact('writer', 'stats', 'ordersByDiscipline', 'recentActivity'));
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