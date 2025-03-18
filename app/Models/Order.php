<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Order extends Model
{
    protected $fillable = [
        'title', 'instructions', 'price', 'deadline', 'task_size',
        'type_of_service', 'discipline', 'software', 'status',
        'client_id', 'writer_id', 'customer_comments'
    ];
    protected $casts = [
        'deadline' => 'datetime',
        'price' => 'decimal:2',
    ];
    // Order status constants
    const STATUS_AVAILABLE = 'available';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_UNCONFIRMED = 'unconfirmed';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_DONE = 'done';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_REVISION = 'revision';
    const STATUS_DISPUTE = 'dispute';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_PAID = 'paid';
    const STATUS_FINISHED = 'finished';
    
    // Relationships
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }
    
    public function writer()
    {
        return $this->belongsTo(User::class, 'writer_id');
    }
    
    public function bids()
    {
        return $this->hasMany(Bid::class);
    }
    
    public function files()
    {
        return $this->morphMany(File::class, 'fileable');
    }
    
    public function messages()
    {
        return $this->hasMany(Message::class);
    }
    
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
    
    // Scopes for different order statuses
    public function scopeAvailable($query)
    {
        return $query->where('status', self::STATUS_AVAILABLE);
    }
    
    public function scopeAssigned($query)
    {
        return $query->whereIn('status', [
            self::STATUS_CONFIRMED, 
            self::STATUS_UNCONFIRMED,
            self::STATUS_IN_PROGRESS,
            self::STATUS_DONE,
            self::STATUS_DELIVERED,
            self::STATUS_REVISION
        ]);
    }
    
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }
    
    // New scope for finished orders including completed, paid, and finished statuses
    public function scopeFinished($query)
    {
        return $query->whereIn('status', [
            self::STATUS_COMPLETED,
            self::STATUS_PAID,
            self::STATUS_FINISHED
        ]);
    }
    
    // Check if order is completed or finished
    public function isCompleted()
    {
        return in_array($this->status, [
            self::STATUS_COMPLETED,
            self::STATUS_PAID,
            self::STATUS_FINISHED
        ]);
    }
    /**
 * Display user statistics dashboard
 *
 * @param Request $request
 * @return \Illuminate\Contracts\Support\Renderable
 */
public function statistics(Request $request)
{
    $user = Auth::user();
    
    // Load writer profile
    $user->load('writerProfile');
    
    // Calculate writer tenure
    $registrationDate = $user->created_at;
    $now = Carbon::now();
    $diffInMonths = $registrationDate->diffInMonths($now);
    $years = floor($diffInMonths / 12);
    $months = $diffInMonths % 12;
    
    // Get total completed orders lifetime
    $totalLifetimeOrders = Order::where('writer_id', $user->id)
        ->whereIn('status', [
            Order::STATUS_COMPLETED,
            Order::STATUS_PAID,
            Order::STATUS_FINISHED
        ])
        ->count();
    
    // Get default date range (last 30 days)
    $endDate = Carbon::now();
    $startDate = Carbon::now()->subDays(30);
    
    // If request has date parameters, use them instead
    if ($request->filled('start_date') && $request->filled('end_date')) {
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date)->endOfDay();
    }
    
    // Get recent completed orders
    $completedOrders = Order::where('writer_id', $user->id)
        ->whereIn('status', [
            Order::STATUS_COMPLETED,
            Order::STATUS_PAID,
            Order::STATUS_FINISHED
        ])
        ->whereBetween('updated_at', [$startDate, $endDate])
        ->get();
    
    // Get top disciplines from completed orders
    $topDisciplines = $completedOrders
        ->groupBy('discipline')
        ->map(function ($group) {
            return $group->count();
        })
        ->sortDesc()
        ->take(10)
        ->toArray();
    
    $totalOrders = $completedOrders->count();
    
    // Calculate statistics for completed orders
    $totalCompletedOrders = $completedOrders->count();
    $totalPages = $completedOrders->sum('task_size');
    
    // Calculate late orders (where deadline is before completion date)
    $totalLateOrders = $completedOrders->filter(function($order) {
        return $order->deadline && $order->updated_at && $order->deadline < $order->updated_at;
    })->count();
    
    // For disputes, check if there are any messages or status changes indicating disputes
    $totalDisputes = Order::where('writer_id', $user->id)
        ->where('status', Order::STATUS_DISPUTE)
        ->whereBetween('updated_at', [$startDate, $endDate])
        ->count();
    
    // Calculate percentages
    $latenessPercentage = $totalCompletedOrders > 0 ? round(($totalLateOrders / $totalCompletedOrders) * 100, 1) : 0;
    $disputesPercentage = $totalCompletedOrders > 0 ? round(($totalDisputes / $totalCompletedOrders) * 100, 1) : 0;
    
    // Get writer's rating details
    $writerRating = $this->calculateWriterRating($user);
    
    return view('writers.statistics', compact(
        'user',
        'years',
        'months',
        'registrationDate',
        'totalLifetimeOrders',
        'topDisciplines',
        'totalOrders',
        'totalCompletedOrders',
        'totalPages',
        'totalLateOrders',
        'totalDisputes',
        'latenessPercentage',
        'disputesPercentage',
        'writerRating',
        'startDate',
        'endDate'
    ));
}

/**
 * Calculate writer's rating
 *
 * @param User $user
 * @return array
 */


/**
 * Get disciplines statistics as JSON for AJAX requests
 *
 * @param Request $request
 * @return \Illuminate\Http\JsonResponse
 */
public function getDisciplinesStats(Request $request)
{
    $request->validate([
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
    ]);
    
    $startDate = Carbon::parse($request->start_date)->startOfDay();
    $endDate = Carbon::parse($request->end_date)->endOfDay();
    
    // Get completed orders for the date range
    $completedOrders = Order::where('writer_id', Auth::id())
        ->whereIn('status', [
            Order::STATUS_COMPLETED,
            Order::STATUS_PAID,
            Order::STATUS_FINISHED
        ])
        ->whereBetween('updated_at', [$startDate, $endDate])
        ->get();
    
    // Group orders by discipline
    $disciplines = [];
    $disciplineCounts = $completedOrders->groupBy('discipline')
        ->map(function ($orders) {
            return $orders->count();
        })
        ->sortDesc();
    
    foreach ($disciplineCounts as $discipline => $count) {
        if (!$discipline) continue; // Skip empty disciplines
        
        $disciplines[] = [
            'discipline' => $discipline,
            'count' => $count
        ];
    }
    
    // Take only top 10
    $disciplines = array_slice($disciplines, 0, 10);
    
    return response()->json([
        'success' => true,
        'data' => [
            'disciplines' => $disciplines,
            'total_orders' => $completedOrders->count()
        ]
    ]);
}

/**
 * Get orders statistics as JSON for AJAX requests
 *
 * @param Request $request
 * @return \Illuminate\Http\JsonResponse
 */
public function getOrdersStats(Request $request)
{
    $request->validate([
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
        'chart_type' => 'nullable|in:orders,pages,lateness,disputes',
    ]);
    
    $startDate = Carbon::parse($request->start_date)->startOfDay();
    $endDate = Carbon::parse($request->end_date)->endOfDay();
    $chartType = $request->chart_type ?? 'orders';
    
    // Get all orders for the date range
    $orders = Order::where('writer_id', Auth::id())
        ->whereIn('status', [
            Order::STATUS_COMPLETED,
            Order::STATUS_PAID,
            Order::STATUS_FINISHED
        ])
        ->whereBetween('updated_at', [$startDate, $endDate])
        ->get();
    
    // Group by month
    $monthlyData = [];
    
    // Create a period from start date to end date by months
    $period = new \DatePeriod(
        $startDate->startOfMonth(),
        new \DateInterval('P1M'),
        $endDate->endOfMonth()->modify('+1 day')
    );
    
    foreach ($period as $date) {
        $yearMonth = $date->format('Y-m');
        $monthLabel = $date->format('M Y');
        
        // Filter orders for this month
        $monthOrders = $orders->filter(function ($order) use ($date) {
            $orderMonth = Carbon::parse($order->updated_at)->format('Y-m');
            return $orderMonth === $date->format('Y-m');
        });
        
        // Count late orders
        $lateOrders = $monthOrders->filter(function($order) {
            return $order->deadline && $order->updated_at && $order->deadline < $order->updated_at;
        });
        
        // Count disputes
        $disputes = Order::where('writer_id', Auth::id())
            ->where('status', Order::STATUS_DISPUTE)
            ->whereYear('updated_at', $date->format('Y'))
            ->whereMonth('updated_at', $date->format('m'))
            ->count();
        
        $monthlyData[] = [
            'month' => $monthLabel,
            'orders' => $monthOrders->count(),
            'pages' => $monthOrders->sum('task_size'),
            'late_orders' => $lateOrders->count(),
            'disputes' => $disputes,
        ];
    }
    
    return response()->json([
        'success' => true,
        'data' => [
            'months' => $monthlyData,
            'chart_type' => $chartType
        ]
    ]);
}

/**
 * Get subjects distribution based on completed orders
 *
 * @param Request $request
 * @return \Illuminate\Http\JsonResponse
 */
public function getSubjectsDistribution(Request $request)
{
    $user = Auth::user();
    $writerProfile = $user->writerProfile;
    
    // Get subjects from writer profile
    $subjects = [];
    if ($writerProfile && $writerProfile->subjects) {
        $subjects = $writerProfile->subjects;
        
        // If subjects is a JSON string, decode it
        if (is_string($subjects)) {
            $subjects = json_decode($subjects, true) ?: [];
        }
    }
    
    if (empty($subjects)) {
        return response()->json([
            'success' => false,
            'message' => 'No subjects found in writer profile'
        ]);
    }
    
    // Get all completed orders
    $completedOrders = Order::where('writer_id', $user->id)
        ->whereIn('status', [
            Order::STATUS_COMPLETED,
            Order::STATUS_PAID,
            Order::STATUS_FINISHED
        ])
        ->get();
    
    $subjectCounts = [];
    
    // Count orders for each subject based on discipline match
    foreach ($subjects as $subject) {
        $subjectCounts[$subject] = 0;
    }
    
    foreach ($completedOrders as $order) {
        if (!$order->discipline) continue;
        
        $orderDiscipline = strtolower($order->discipline);
        foreach ($subjects as $subject) {
            // Check if discipline matches subject (case insensitive)
            if (strpos($orderDiscipline, strtolower($subject)) !== false) {
                $subjectCounts[$subject]++;
                break; // Count each order only once
            }
        }
    }
    
    // Format data for response
    $formattedSubjects = [];
    foreach ($subjectCounts as $subject => $count) {
        $formattedSubjects[] = [
            'subject' => $subject,
            'count' => $count
        ];
    }
    
    // Sort by count (highest first)
    usort($formattedSubjects, function($a, $b) {
        return $b['count'] - $a['count'];
    });
    
    return response()->json([
        'success' => true,
        'data' => [
            'subjects' => $formattedSubjects,
            'total_orders' => $completedOrders->count()
        ]
    ]);
}
    // Check if payment has been processed
    public function isPaid()
    {
        return in_array($this->status, [
            self::STATUS_PAID,
            self::STATUS_FINISHED
        ]);
    }

    // Add this relationship to your Order model

    /**
     * Get the financial transactions associated with the order.
     */
    public function financialTransactions()
    {
        return $this->hasMany(Finance::class);
    }

    /**
     * Process payment to writer when order is completed
     */
    public function processWriterPayment($processedBy = null)
    {
        if (!$this->writer_id || !$this->isCompleted() || $this->isPaid()) {
            return false;
        }
        
        // Calculate writer payment (you can adjust this calculation based on your business rules)
        $writerAmount = $this->price * 0.70; // 70% goes to writer
        
        // Record the payment
        $transaction = Finance::addOrderPayment(
            $this->writer_id, 
            $this->id, 
            $writerAmount, 
            "Payment for order #{$this->id}: {$this->title}", 
            $processedBy
        );
        
        // Update order status
        $this->status = self::STATUS_PAID;
        $this->save();
        
        return $transaction;
    }
}