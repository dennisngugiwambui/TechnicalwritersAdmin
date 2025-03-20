<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Finance;
use App\Models\User;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Notifications\WithdrawalProcessed;
use App\Notifications\WithdrawalFailed;
use App\Notifications\BonusReceived;
use App\Notifications\PenaltyApplied;
use App\Services\MpesaDarajaService;

class PaymentController extends Controller
{
    protected $mpesaService;
    
    /**
     * Create a new controller instance.
     *
     * @param MpesaDarajaService $mpesaService
     * @return void
     */
    public function __construct(MpesaDarajaService $mpesaService)
    {
        $this->middleware('auth');
        $this->mpesaService = $mpesaService;
    }
    
    /**
     * Display the finance dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function dashboard()
    {
        // Current month and previous month dates
        $currentMonth = Carbon::now()->startOfMonth();
        $previousMonth = Carbon::now()->subMonth()->startOfMonth();
        
        // Calculate total revenue (current month)
        $totalRevenue = Finance::whereIn('transaction_type', [
                Finance::TYPE_ORDER_PAYMENT
            ])
            ->where('status', Finance::STATUS_COMPLETED)
            ->whereMonth('created_at', $currentMonth->month)
            ->whereYear('created_at', $currentMonth->year)
            ->sum('amount');
        
        // Calculate total revenue (previous month)
        $previousRevenue = Finance::whereIn('transaction_type', [
                Finance::TYPE_ORDER_PAYMENT
            ])
            ->where('status', Finance::STATUS_COMPLETED)
            ->whereMonth('created_at', $previousMonth->month)
            ->whereYear('created_at', $previousMonth->year)
            ->sum('amount');
        
        // Calculate revenue change percentage
        $revenueChange = $previousRevenue > 0 
            ? round((($totalRevenue - $previousRevenue) / $previousRevenue) * 100, 1) 
            : 0;
        
        // Calculate writer payments (current month)
        $writerPayments = Finance::whereIn('transaction_type', [
                Finance::TYPE_ORDER_PAYMENT,
                Finance::TYPE_BONUS
            ])
            ->where('status', Finance::STATUS_COMPLETED)
            ->whereMonth('created_at', $currentMonth->month)
            ->whereYear('created_at', $currentMonth->year)
            ->sum('amount');
        
        // Calculate pending payments
        $pendingPayments = Finance::where('transaction_type', Finance::TYPE_WITHDRAWAL)
            ->whereIn('status', [
                Finance::STATUS_PENDING,
                Finance::STATUS_PROCESSING
            ])
            ->sum('amount');
        
        // Calculate profit margin
        $profitMargin = $totalRevenue > 0 
            ? round((($totalRevenue - $writerPayments) / $totalRevenue) * 100, 1) 
            : 0;
        
        // Calculate previous month's profit margin
        $previousWriterPayments = Finance::whereIn('transaction_type', [
                Finance::TYPE_ORDER_PAYMENT,
                Finance::TYPE_BONUS
            ])
            ->where('status', Finance::STATUS_COMPLETED)
            ->whereMonth('created_at', $previousMonth->month)
            ->whereYear('created_at', $previousMonth->year)
            ->sum('amount');
        
        $previousProfitMargin = $previousRevenue > 0 
            ? round((($previousRevenue - $previousWriterPayments) / $previousRevenue) * 100, 1) 
            : 0;
        
        $marginChange = round($profitMargin - $previousProfitMargin, 1);
        
        // Get recent transactions
        $recentTransactions = Finance::with(['user', 'order'])
            ->latest()
            ->limit(10)
            ->get();
        
        // Generate chart data for the last 30 days
        $endDate = Carbon::now();
        $startDate = Carbon::now()->subDays(29);
        $dates = [];
        $revenueData = [];
        $paymentData = [];
        
        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            $dates[] = $date->format('M d');
            
            // Daily revenue
            $dailyRevenue = Finance::whereIn('transaction_type', [
                    Finance::TYPE_ORDER_PAYMENT
                ])
                ->where('status', Finance::STATUS_COMPLETED)
                ->whereDate('created_at', $date->format('Y-m-d'))
                ->sum('amount');
            
            $revenueData[] = $dailyRevenue;
            
            // Daily writer payments
            $dailyPayments = Finance::whereIn('transaction_type', [
                    Finance::TYPE_ORDER_PAYMENT,
                    Finance::TYPE_BONUS
                ])
                ->where('status', Finance::STATUS_COMPLETED)
                ->whereDate('created_at', $date->format('Y-m-d'))
                ->sum('amount');
            
            $paymentData[] = $dailyPayments;
        }
        
        $chartLabels = $dates;
        
        return view('admin.finance.dashboard', compact(
            'totalRevenue',
            'revenueChange',
            'writerPayments',
            'pendingPayments',
            'profitMargin',
            'marginChange',
            'recentTransactions',
            'chartLabels',
            'revenueData',
            'paymentData'
        ));
    }
    
    /**
     * Get chart data for AJAX request.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getChartData(Request $request)
    {
        $period = $request->get('period', 30);
        $endDate = Carbon::now();
        $startDate = Carbon::now()->subDays($period - 1);
        
        $dates = [];
        $revenueData = [];
        $paymentData = [];
        
        // Adjust interval based on period
        $interval = 'day';
        $dateFormat = 'M d';
        
        if ($period > 90) {
            $interval = 'week';
            $dateFormat = 'M d';
        }
        
        if ($period > 180) {
            $interval = 'month';
            $dateFormat = 'M Y';
        }
        
        if ($interval === 'day') {
            for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
                $dates[] = $date->format($dateFormat);
                
                // Daily revenue
                $dailyRevenue = Finance::whereIn('transaction_type', [
                        Finance::TYPE_ORDER_PAYMENT
                    ])
                    ->where('status', Finance::STATUS_COMPLETED)
                    ->whereDate('created_at', $date->format('Y-m-d'))
                    ->sum('amount');
                
                $revenueData[] = $dailyRevenue;
                
                // Daily writer payments
                $dailyPayments = Finance::whereIn('transaction_type', [
                        Finance::TYPE_ORDER_PAYMENT,
                        Finance::TYPE_BONUS
                    ])
                    ->where('status', Finance::STATUS_COMPLETED)
                    ->whereDate('created_at', $date->format('Y-m-d'))
                    ->sum('amount');
                
                $paymentData[] = $dailyPayments;
            }
        } elseif ($interval === 'week') {
            $currentDate = $startDate->copy()->startOfWeek();
            while ($currentDate <= $endDate) {
                $weekEnd = $currentDate->copy()->endOfWeek();
                $dates[] = $currentDate->format('M d') . ' - ' . $weekEnd->format('M d');
                
                // Weekly revenue
                $weeklyRevenue = Finance::whereIn('transaction_type', [
                        Finance::TYPE_ORDER_PAYMENT
                    ])
                    ->where('status', Finance::STATUS_COMPLETED)
                    ->whereBetween('created_at', [$currentDate, $weekEnd])
                    ->sum('amount');
                
                $revenueData[] = $weeklyRevenue;
                
                // Weekly writer payments
                $weeklyPayments = Finance::whereIn('transaction_type', [
                        Finance::TYPE_ORDER_PAYMENT,
                        Finance::TYPE_BONUS
                    ])
                    ->where('status', Finance::STATUS_COMPLETED)
                    ->whereBetween('created_at', [$currentDate, $weekEnd])
                    ->sum('amount');
                
                $paymentData[] = $weeklyPayments;
                
                $currentDate->addWeek();
            }
        } else {
            $currentDate = $startDate->copy()->startOfMonth();
            while ($currentDate <= $endDate) {
                $monthEnd = $currentDate->copy()->endOfMonth();
                $dates[] = $currentDate->format($dateFormat);
                
                // Monthly revenue
                $monthlyRevenue = Finance::whereIn('transaction_type', [
                        Finance::TYPE_ORDER_PAYMENT
                    ])
                    ->where('status', Finance::STATUS_COMPLETED)
                    ->whereMonth('created_at', $currentDate->month)
                    ->whereYear('created_at', $currentDate->year)
                    ->sum('amount');
                
                $revenueData[] = $monthlyRevenue;
                
                // Monthly writer payments
                $monthlyPayments = Finance::whereIn('transaction_type', [
                        Finance::TYPE_ORDER_PAYMENT,
                        Finance::TYPE_BONUS
                    ])
                    ->where('status', Finance::STATUS_COMPLETED)
                    ->whereMonth('created_at', $currentDate->month)
                    ->whereYear('created_at', $currentDate->year)
                    ->sum('amount');
                
                $paymentData[] = $monthlyPayments;
                
                $currentDate->addMonth();
            }
        }
        
        return response()->json([
            'labels' => $dates,
            'revenue' => $revenueData,
            'payments' => $paymentData
        ]);
    }
    
    /**
     * Display a listing of all financial transactions.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Finance::with(['user', 'order', 'processor']);
        
        // Filter by transaction type
        if ($request->has('type') && $request->type) {
            $query->where('transaction_type', $request->type);
        }
        
        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        // Filter by date range
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        // Get transactions with pagination
        $transactions = $query->latest()->paginate(15);
        
        // Get transaction statistics
        $transactionCount = Finance::count();
        
        // Calculate total amounts
        $totalIncome = Finance::whereIn('transaction_type', [
                Finance::TYPE_ORDER_PAYMENT, 
                Finance::TYPE_BONUS
            ])
            ->where('status', Finance::STATUS_COMPLETED)
            ->sum('amount');
            
        $totalOutgoing = Finance::whereIn('transaction_type', [
                Finance::TYPE_WITHDRAWAL, 
                Finance::TYPE_REFUND,
                Finance::TYPE_PENALTY
            ])
            ->where('status', Finance::STATUS_COMPLETED)
            ->sum('amount');
        
        $totalAmount = $totalIncome + $totalOutgoing;
        $netBalance = $totalIncome - $totalOutgoing;
        
        // Variables needed for the view based on your blade file
        $totalRevenue = $totalIncome; // Ensuring this is passed to the view
        $revenueChange = 0; // You can calculate this if needed
        $writerPayments = Finance::whereIn('transaction_type', [
                Finance::TYPE_ORDER_PAYMENT,
                Finance::TYPE_BONUS
            ])
            ->where('status', Finance::STATUS_COMPLETED)
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('amount');
        $pendingPayments = Finance::where('transaction_type', Finance::TYPE_WITHDRAWAL)
            ->whereIn('status', [Finance::STATUS_PENDING, Finance::STATUS_PROCESSING])
            ->sum('amount');
        $profitMargin = $totalRevenue > 0 ? round((($totalRevenue - $writerPayments) / $totalRevenue) * 100, 1) : 0;
        $marginChange = 0; // You can calculate this if needed
        $recentTransactions = Finance::latest()->limit(10)->get();
        
        // For chart data
        $chartLabels = [];
        $revenueData = [];
        $paymentData = [];
        
        return view('admin.finance.index', compact(
            'transactions', 
            'transactionCount', 
            'totalAmount', 
            'netBalance',
            'totalRevenue',
            'revenueChange',
            'writerPayments',
            'pendingPayments',
            'profitMargin',
            'marginChange',
            'recentTransactions',
            'chartLabels',
            'revenueData',
            'paymentData'
        ));
    }
    
    /**
     * Display a listing of payments (filtered by type, status, etc.)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function payments(Request $request)
    {
        $query = Finance::with(['user', 'order', 'processor']);
        
        // Filter by payment types only
        $query->whereIn('transaction_type', [
            Finance::TYPE_ORDER_PAYMENT,
            Finance::TYPE_WITHDRAWAL,
            Finance::TYPE_REFUND
        ]);
        
        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        // Filter by date range
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        // Get payments with pagination
        $payments = $query->latest()->paginate(15);
        
        // Get payment statistics
        $pendingCount = Finance::whereIn('transaction_type', [
                Finance::TYPE_ORDER_PAYMENT,
                Finance::TYPE_WITHDRAWAL,
                Finance::TYPE_REFUND
            ])
            ->where('status', Finance::STATUS_PENDING)
            ->count();
            
        $pendingAmount = Finance::whereIn('transaction_type', [
                Finance::TYPE_ORDER_PAYMENT,
                Finance::TYPE_WITHDRAWAL,
                Finance::TYPE_REFUND
            ])
            ->where('status', Finance::STATUS_PENDING)
            ->sum('amount');
        
        $completedToday = Finance::whereIn('transaction_type', [
                Finance::TYPE_ORDER_PAYMENT,
                Finance::TYPE_WITHDRAWAL,
                Finance::TYPE_REFUND
            ])
            ->where('status', Finance::STATUS_COMPLETED)
            ->whereDate('processed_at', Carbon::today())
            ->count();
        
        $completedAmountToday = Finance::whereIn('transaction_type', [
                Finance::TYPE_ORDER_PAYMENT,
                Finance::TYPE_WITHDRAWAL,
                Finance::TYPE_REFUND
            ])
            ->where('status', Finance::STATUS_COMPLETED)
            ->whereDate('processed_at', Carbon::today())
            ->sum('amount');
        
        // Additional variables that might be needed based on the view
        $totalRevenue = Finance::whereIn('transaction_type', [Finance::TYPE_ORDER_PAYMENT])
            ->where('status', Finance::STATUS_COMPLETED)
            ->sum('amount');
        $revenueChange = 0;
        $writerPayments = Finance::whereIn('transaction_type', [
                Finance::TYPE_ORDER_PAYMENT,
                Finance::TYPE_BONUS
            ])
            ->where('status', Finance::STATUS_COMPLETED)
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('amount');
        $pendingPayments = Finance::where('transaction_type', Finance::TYPE_WITHDRAWAL)
            ->whereIn('status', [Finance::STATUS_PENDING, Finance::STATUS_PROCESSING])
            ->sum('amount');
        $profitMargin = $totalRevenue > 0 ? round((($totalRevenue - $writerPayments) / $totalRevenue) * 100, 1) : 0;
        $marginChange = 0;
        $recentTransactions = Finance::latest()->limit(10)->get();
        
        // For chart data
        $chartLabels = [];
        $revenueData = [];
        $paymentData = [];
        
        return view('admin.finance.payments', compact(
            'payments',
            'pendingCount',
            'pendingAmount',
            'completedToday',
            'completedAmountToday',
            'totalRevenue',
            'revenueChange',
            'writerPayments',
            'pendingPayments',
            'profitMargin',
            'marginChange',
            'recentTransactions',
            'chartLabels',
            'revenueData',
            'paymentData'
        ));
    }
    
    /**
     * Display details for a specific transaction.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $transaction = Finance::with(['user', 'order', 'processor'])->findOrFail($id);
        
        return view('admin.finance.show', compact('transaction'));
    }
    
    /**
     * Show the form for creating a new writer bonus.
     *
     * @return \Illuminate\Http\Response
     */
    public function createBonus()
    {
        $writers = User::where('role', 'writer')->get();
        
        return view('admin.finance.bonus', compact('writers'));
    }
    
    /**
     * Store a newly created bonus.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeBonus(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
        ]);
        
        $user = User::findOrFail($validated['user_id']);
        
        // Add bonus transaction
        $transaction = Finance::addBonus(
            $validated['user_id'], 
            $validated['amount'], 
            $validated['description'], 
            Auth::id()
        );
        
        // Notify the writer
        $user->notify(new BonusReceived($transaction));
        
        return redirect()->route('admin.finance.transactions')
            ->with('success', 'Bonus has been added successfully.');
    }
    
    /**
     * Show the form for creating a new writer penalty.
     *
     * @return \Illuminate\Http\Response
     */
    public function createPenalty()
    {
        $writers = User::where('role', 'writer')->get();
        
        return view('admin.finance.penalty', compact('writers'));
    }
    
    /**
     * Store a newly created penalty.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storePenalty(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
        ]);
        
        $user = User::findOrFail($validated['user_id']);
        
        // Check available balance
        $availableBalance = Finance::getAvailableBalance($validated['user_id']);
        
        if ($availableBalance < $validated['amount']) {
            return back()->withErrors([
                'amount' => 'The penalty amount exceeds the writer\'s available balance.'
            ])->withInput();
        }
        
        // Add penalty transaction
        $transaction = Finance::addPenalty(
            $validated['user_id'], 
            $validated['amount'], 
            $validated['description'], 
            Auth::id()
        );
        
        // Notify the writer
        $user->notify(new PenaltyApplied($transaction));
        
        return redirect()->route('admin.finance.transactions')
            ->with('success', 'Penalty has been applied successfully.');
    }
    
    /**
     * Display a listing of withdrawal requests.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function withdrawals(Request $request)
    {
        $query = Finance::with(['user', 'processor'])
            ->where('transaction_type', Finance::TYPE_WITHDRAWAL);
        
        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        } else {
            // Default to showing pending and processing withdrawals
            $query->whereIn('status', [
                Finance::STATUS_PENDING, 
                Finance::STATUS_PROCESSING
            ]);
        }
        
        // Filter by date range
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        // Get withdrawals with pagination
        $withdrawals = $query->latest()->paginate(15);
        
        // Get withdrawal statistics
        $totalPendingAmount = Finance::where('transaction_type', Finance::TYPE_WITHDRAWAL)
            ->whereIn('status', [Finance::STATUS_PENDING, Finance::STATUS_PROCESSING])
            ->sum('amount');
            
        $totalProcessedToday = Finance::where('transaction_type', Finance::TYPE_WITHDRAWAL)
            ->where('status', Finance::STATUS_COMPLETED)
            ->whereDate('processed_at', Carbon::today())
            ->sum('amount');
        
        return view('admin.finance.withdrawals', compact(
            'withdrawals', 
            'totalPendingAmount', 
            'totalProcessedToday'
        ));
    }
    
    /**
     * Approve a withdrawal request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function approveWithdrawal(Request $request, $id)
    {
        $transaction = Finance::findOrFail($id);
        
        // Check if it's a pending withdrawal
        if ($transaction->transaction_type !== Finance::TYPE_WITHDRAWAL || 
            $transaction->status !== Finance::STATUS_PENDING) {
            return back()->with('error', 'Invalid transaction or already processed.');
        }
        
        try {
            DB::beginTransaction();
            
            // If it's an M-Pesa withdrawal
            if ($transaction->payment_method === 'mpesa') {
                // First update to processing status
                $transaction->status = Finance::STATUS_PROCESSING;
                $transaction->save();
                
                // Get the user's phone number
                $user = User::findOrFail($transaction->user_id);
                
                // Process M-Pesa B2C payment
                $result = $this->mpesaService->sendMoney(
                    $user->phone,
                    $transaction->amount,
                    "Writer payment from " . config('app.name')
                );
                
                if ($result['success']) {
                    // Complete the withdrawal
                    Finance::processWithdrawal(
                        $id, 
                        Auth::id(), 
                        Finance::STATUS_COMPLETED,
                        $result['transactionId']
                    );
                    
                    // Notify the user
                    $user->notify(new WithdrawalProcessed($transaction));
                    
                    DB::commit();
                    
                    return redirect()->route('admin.finance.withdrawals')
                        ->with('success', 'Withdrawal has been processed successfully via M-Pesa.');
                } else {
                    // Handle failure
                    $transaction->status = Finance::STATUS_FAILED;
                    $transaction->description = $transaction->description . ' | Failed: ' . $result['message'];
                    $transaction->save();
                    
                    // Notify the user
                    $user->notify(new WithdrawalFailed($transaction));
                    
                    DB::commit();
                    
                    return back()->with('error', 'M-Pesa payment failed: ' . $result['message']);
                }
            } else {
                // For manual payment methods
                // Update to processing status
                $transaction->status = Finance::STATUS_PROCESSING;
                $transaction->processed_by = Auth::id();
                $transaction->save();
                
                DB::commit();
                
                return redirect()->route('admin.finance.withdrawals')
                    ->with('success', 'Withdrawal marked as processing. Complete it manually and update the reference.');
            }
        } catch (\Exception $e) {
            DB::rollback();
            
            return back()->with('error', 'Failed to process withdrawal: ' . $e->getMessage());
        }
    }
    
    /**
     * Mark a withdrawal as completed manually.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function completeWithdrawal(Request $request, $id)
    {
        $validated = $request->validate([
            'payment_reference' => 'required|string|max:255',
        ]);
        
        $transaction = Finance::findOrFail($id);
        
        // Check if it's a processing withdrawal
        if ($transaction->transaction_type !== Finance::TYPE_WITHDRAWAL || 
            $transaction->status !== Finance::STATUS_PROCESSING) {
            return back()->with('error', 'Invalid transaction or not in processing state.');
        }
        
        try {
            DB::beginTransaction();
            
            // Complete the withdrawal
            Finance::processWithdrawal(
                $id, 
                Auth::id(), 
                Finance::STATUS_COMPLETED,
                $validated['payment_reference']
            );
            
            // Notify the user
            $user = User::findOrFail($transaction->user_id);
            $user->notify(new WithdrawalProcessed($transaction));
            
            DB::commit();
            
            return redirect()->route('admin.finance.withdrawals')
                ->with('success', 'Withdrawal has been marked as completed.');
        } catch (\Exception $e) {
            DB::rollback();
            
            return back()->with('error', 'Failed to complete withdrawal: ' . $e->getMessage());
        }
    }
    
    /**
     * Reject a withdrawal request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function rejectWithdrawal(Request $request, $id)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:255',
        ]);
        
        $transaction = Finance::findOrFail($id);
        
        // Check if it's a pending withdrawal
        if ($transaction->transaction_type !== Finance::TYPE_WITHDRAWAL || 
            !in_array($transaction->status, [Finance::STATUS_PENDING, Finance::STATUS_PROCESSING])) {
            return back()->with('error', 'Invalid transaction or not in a pending/processing state.');
        }
        
        // Update the transaction
        $transaction->status = Finance::STATUS_CANCELLED;
        $transaction->description = $transaction->description . ' | Rejected: ' . $validated['reason'];
        $transaction->processed_by = Auth::id();
        $transaction->processed_at = now();
        $transaction->save();
        
        // Notify the user
        $user = User::findOrFail($transaction->user_id);
        $user->notify(new WithdrawalProcessed($transaction, true));
        
        return redirect()->route('admin.finance.withdrawals')
            ->with('success', 'Withdrawal request has been rejected.');
    }
    
    /**
     * Process a refund for an order.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function processRefund(Request $request, $id)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:255',
        ]);
        
        $transaction = Finance::findOrFail($id);
        
        // Check if it's a completed payment
        if ($transaction->transaction_type !== Finance::TYPE_ORDER_PAYMENT || 
            $transaction->status !== Finance::STATUS_COMPLETED) {
            return back()->with('error', 'Invalid transaction for refund.');
        }
        
        // Check if the amount is valid
        if ($validated['amount'] > $transaction->amount) {
            return back()->with('error', 'Refund amount cannot exceed the original payment amount.');
        }
        
        try {
            DB::beginTransaction();
            
            // Process the refund
            $refund = Finance::processRefund(
                $transaction->order_id,
                $validated['amount'],
                'Refund: ' . $validated['reason'],
                Auth::id()
            );
            
            if (!$refund) {
                throw new \Exception('Failed to process refund. Writer not found or other error.');
            }
            
            // Update the order status if needed
            $order = Order::findOrFail($transaction->order_id);
            
            if ($validated['amount'] == $transaction->amount) {
                // Full refund - update order status
                $order->status = 'refunded';
                $order->save();
            }
            
            DB::commit();
            
            return redirect()->route('admin.finance.transactions')
                ->with('success', 'Refund has been processed successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            
            return back()->with('error', 'Failed to process refund: ' . $e->getMessage());
        }
    }
    
    /**
     * Display revenue reports by different metrics.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function reports(Request $request)
    {
        $period = $request->get('period', 'monthly');
        $year = $request->get('year', date('Y'));
        $month = $request->get('month', date('m'));
        
        // Get the data based on the selected period
        switch ($period) {
            case 'daily':
                $startDate = Carbon::createFromDate($year, $month, 1)->startOfDay();
                $endDate = $startDate->copy()->endOfMonth();
                $groupBy = 'day';
                break;
                
            case 'monthly':
                $startDate = Carbon::createFromDate($year, 1, 1)->startOfDay();
                $endDate = $startDate->copy()->endOfYear();
                $groupBy = 'month';
                break;
                
            case 'yearly':
                $startDate = Carbon::createFromDate($year - 4, 1, 1)->startOfDay();
                $endDate = Carbon::createFromDate($year, 12, 31)->endOfDay();
                $groupBy = 'year';
                break;
                
            default:
                $startDate = Carbon::createFromDate($year, 1, 1)->startOfDay();
                $endDate = $startDate->copy()->endOfYear();
                $groupBy = 'month';
        }
        
        // Revenue data
        $revenueData = $this->getRevenueData($startDate, $endDate, $groupBy);
        
        // Writer earnings data
        $writerEarningsData = $this->getWriterEarningsData($startDate, $endDate, $groupBy);
        
        // Top writers by earnings
        $topWriters = $this->getTopWriters($startDate, $endDate);
        
        return view('admin.finance.reports', compact(
            'period', 
            'year', 
            'month', 
            'revenueData', 
            'writerEarningsData', 
            'topWriters'
        ));
    }
    
    /**
     * Get revenue data for the selected period.
     *
     * @param  \Carbon\Carbon  $startDate
     * @param  \Carbon\Carbon  $endDate
     * @param  string  $groupBy
     * @return array
     */
    private function getRevenueData($startDate, $endDate, $groupBy)
    {
        $format = $groupBy === 'day' ? 'Y-m-d' : ($groupBy === 'month' ? 'Y-m' : 'Y');
        $selectRaw = "DATE_FORMAT(created_at, '" . ($groupBy === 'day' ? '%Y-%m-%d' : 
            ($groupBy === 'month' ? '%Y-%m' : '%Y')) . "') as period";
        
        $revenues = DB::table('finances')
            ->select(DB::raw($selectRaw))
            ->selectRaw('SUM(CASE WHEN transaction_type IN (?, ?) THEN amount ELSE 0 END) as income', 
                [Finance::TYPE_ORDER_PAYMENT, Finance::TYPE_BONUS])
            ->selectRaw('SUM(CASE WHEN transaction_type IN (?, ?, ?) THEN amount ELSE 0 END) as outgoing', 
                [Finance::TYPE_WITHDRAWAL, Finance::TYPE_REFUND, Finance::TYPE_PENALTY])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', Finance::STATUS_COMPLETED)
            ->groupBy('period')
            ->orderBy('period')
            ->get();
        
        // Prepare the data for the chart
        $labels = [];
        $incomeData = [];
        $outgoingData = [];
        $netData = [];
        
        foreach ($revenues as $revenue) {
            $labels[] = $revenue->period;
            $incomeData[] = $revenue->income;
            $outgoingData[] = $revenue->outgoing;
            $netData[] = $revenue->income - $revenue->outgoing;
        }
        
        return [
            'labels' => $labels,
            'income' => $incomeData,
            'outgoing' => $outgoingData,
            'net' => $netData
        ];
    }
    
    /**
     * Get writer earnings data for the selected period.
     *
     * @param  \Carbon\Carbon  $startDate
     * @param  \Carbon\Carbon  $endDate
     * @param  string  $groupBy
     * @return array
     */
    private function getWriterEarningsData($startDate, $endDate, $groupBy)
    {
        $format = $groupBy === 'day' ? 'Y-m-d' : ($groupBy === 'month' ? 'Y-m' : 'Y');
        $selectRaw = "DATE_FORMAT(created_at, '" . ($groupBy === 'day' ? '%Y-%m-%d' : 
            ($groupBy === 'month' ? '%Y-%m' : '%Y')) . "') as period";
        
        $earnings = DB::table('finances')
            ->select(DB::raw($selectRaw))
            ->selectRaw('SUM(CASE WHEN transaction_type = ? THEN amount ELSE 0 END) as payments', 
                [Finance::TYPE_ORDER_PAYMENT])
            ->selectRaw('SUM(CASE WHEN transaction_type = ? THEN amount ELSE 0 END) as bonuses', 
                [Finance::TYPE_BONUS])
            ->selectRaw('SUM(CASE WHEN transaction_type = ? THEN amount ELSE 0 END) as penalties', 
                [Finance::TYPE_PENALTY])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', Finance::STATUS_COMPLETED)
            ->groupBy('period')
            ->orderBy('period')
            ->get();
        
        // Prepare the data for the chart
        $labels = [];
        $paymentsData = [];
        $bonusesData = [];
        $penaltiesData = [];
        
        foreach ($earnings as $earning) {
            $labels[] = $earning->period;
            $paymentsData[] = $earning->payments;
            $bonusesData[] = $earning->bonuses;
            $penaltiesData[] = $earning->penalties;
        }
        
        return [
            'labels' => $labels,
            'payments' => $paymentsData,
            'bonuses' => $bonusesData,
            'penalties' => $penaltiesData
        ];
    }
    
    /**
     * Get top writers by earnings for the selected period.
     *
     * @param  \Carbon\Carbon  $startDate
     * @param  \Carbon\Carbon  $endDate
     * @return \Illuminate\Support\Collection
     */
    private function getTopWriters($startDate, $endDate)
    {
        return DB::table('finances')
            ->join('users', 'finances.user_id', '=', 'users.id')
            ->select('users.id', 'users.name')
            ->selectRaw('SUM(CASE WHEN transaction_type = ? THEN amount ELSE 0 END) as payments', 
                [Finance::TYPE_ORDER_PAYMENT])
            ->selectRaw('SUM(CASE WHEN transaction_type = ? THEN amount ELSE 0 END) as bonuses', 
                [Finance::TYPE_BONUS])
            ->selectRaw('SUM(CASE WHEN transaction_type = ? THEN amount ELSE 0 END) as penalties', 
                [Finance::TYPE_PENALTY])
            ->selectRaw('SUM(CASE WHEN transaction_type IN (?, ?) THEN amount ELSE -1 * amount END) as total', 
                [Finance::TYPE_ORDER_PAYMENT, Finance::TYPE_BONUS])
            ->whereBetween('finances.created_at', [$startDate, $endDate])
            ->where('finances.status', Finance::STATUS_COMPLETED)
            ->where('users.role', 'writer')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total')
            ->limit(10)
            ->get();
    }
    
    /**
     * Initiate payment via M-Pesa
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function initiatePayment(Request $request)
    {
        try {
            $validated = $request->validate([
                'phone' => 'required|string',
                'amount' => 'required|numeric|min:1',
                'order_id' => 'required|integer|exists:orders,id',
            ]);
            
            // Format the phone number
            $phoneNumber = $this->mpesaService->formatPhoneNumber($validated['phone']);
            
            // Get the order
            $order = Order::findOrFail($validated['order_id']);
            
            // Check if the order has already been paid
            if ($order->payment_status === 'paid') {
                return response()->json([
                    'success' => false,
                    'message' => 'This order has already been paid.'
                ], 400);
            }
            
            // Generate a reference (using order number)
            $reference = 'Order-' . $order->id;
            
            // Generate a description
            $description = 'Payment for Order #' . $order->id;
            
            // Initiate STK Push
            $result = $this->mpesaService->initiateSTKPush(
                $phoneNumber,
                $validated['amount'],
                $reference,
                $description
            );
            
            if ($result['success']) {
                // Store the transaction details
                \App\Models\MpesaTransaction::create([
                    'checkout_request_id' => $result['checkoutRequestId'],
                    'phone' => $phoneNumber,
                    'amount' => $validated['amount'],
                    'reference' => $reference,
                    'description' => $description,
                    'order_id' => $validated['order_id'],
                    'user_id' => Auth::id(),
                    'status' => 'pending',
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Payment initiated. Please check your phone to complete the transaction.',
                    'checkoutRequestId' => $result['checkoutRequestId']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to initiate payment: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Check M-Pesa payment status
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkPaymentStatus(Request $request)
    {
        try {
            $validated = $request->validate([
                'checkout_request_id' => 'required|string'
            ]);
            
            $transaction = \App\Models\MpesaTransaction::where('checkout_request_id', $validated['checkout_request_id'])->first();
            
            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction not found'
                ], 404);
            }
            
            if ($transaction->status !== 'pending') {
                return response()->json([
                    'success' => $transaction->status === 'completed',
                    'message' => $transaction->status === 'completed' ? 'Payment completed successfully' : 'Payment failed',
                    'status' => $transaction->status
                ]);
            }
            
            // Check status with M-Pesa
            $result = $this->mpesaService->checkSTKStatus($validated['checkout_request_id']);
            
            if ($result['success']) {
                $transaction->status = 'completed';
                $transaction->transaction_id = $result['data']['ResultCode'] ?? null;
                $transaction->save();
                
                // Update order payment status
                $order = Order::find($transaction->order_id);
                if ($order) {
                    $order->payment_status = 'paid';
                    $order->paid_at = now();
                    $order->save();
                }
                
                return response()->json([
                    'success' => true,
                    'message' => 'Payment completed successfully',
                    'status' => 'completed'
                ]);
            } else {
                if (isset($result['data']['ResultCode']) && $result['data']['ResultCode'] != 0) {
                    $transaction->status = 'failed';
                    $transaction->save();
                    
                    return response()->json([
                        'success' => false,
                        'message' => $result['message'],
                        'status' => 'failed'
                    ]);
                }
                
                return response()->json([
                    'success' => false,
                    'message' => 'Payment is still pending',
                    'status' => 'pending'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check payment status: ' . $e->getMessage()
            ], 500);
        }
    }
}