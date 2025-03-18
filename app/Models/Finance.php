<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use App\Models\Finance;
use App\Models\WriterProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user = Auth::user();
        
        if ($user->usertype === User::ROLE_ADMIN) {
            return $this->adminDashboard();
        } elseif ($user->usertype === User::ROLE_WRITER) {
            return $this->writerDashboard();
        } elseif ($user->usertype === User::ROLE_CLIENT) {
            return $this->clientDashboard();
        } elseif ($user->usertype === User::ROLE_SUPPORT) {
            return $this->supportDashboard();
        }
        
        return view('home');
    }

    /**
     * Show admin dashboard
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    protected function adminDashboard()
    {
        // Count total users by role
        $totalWriters = User::where('usertype', User::ROLE_WRITER)->count();
        $totalClients = User::where('usertype', User::ROLE_CLIENT)->count();
        $pendingVerifications = WriterProfile::where('id_verification_status', 'pending')->count();
        
        // Count orders by status
        $availableOrders = Order::available()->count();
        $assignedOrders = Order::assigned()->count();
        $completedOrders = Order::finished()->count();
        
        // Calculate total payments processed
        $totalPayments = Finance::where('transaction_type', Finance::TYPE_ORDER_PAYMENT)
            ->where('status', Finance::STATUS_COMPLETED)
            ->sum('amount');
        
        // Get recent orders
        $recentOrders = Order::with(['client', 'writer'])
            ->latest()
            ->take(10)
            ->get();
        
        // Get recent writer registrations
        $recentWriters = User::with('writerProfile')
            ->where('usertype', User::ROLE_WRITER)
            ->latest()
            ->take(5)
            ->get();
        
        // Get pending withdrawals
        $pendingWithdrawals = Finance::with('user')
            ->where('transaction_type', Finance::TYPE_WITHDRAWAL)
            ->where('status', Finance::STATUS_PENDING)
            ->latest()
            ->take(5)
            ->get();
        
        return view('admin.dashboard', compact(
            'totalWriters',
            'totalClients',
            'pendingVerifications',
            'availableOrders',
            'assignedOrders',
            'completedOrders',
            'totalPayments',
            'recentOrders',
            'recentWriters',
            'pendingWithdrawals'
        ));
    }

    /**
     * Show writer dashboard
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    protected function writerDashboard()
    {
        $user = Auth::user();
        
        // Check if writer needs to take assessment
        if ($user->needsAssessment()) {
            return redirect()->route('writer.assessment');
        }
        
        // Check if writer profile is complete
        if (!$user->writerProfile) {
            return redirect()->route('writer.profile.create');
        }
        
        // Get available orders
        $availableOrders = Order::available()
            ->latest()
            ->take(5)
            ->get();
        
        // Get writer's assigned orders
        $assignedOrders = Order::where('writer_id', $user->id)
            ->assigned()
            ->latest()
            ->take(5)
            ->get();
        
        // Get writer's completed orders
        $completedOrders = Order::where('writer_id', $user->id)
            ->finished()
            ->latest()
            ->take(5)
            ->get();
        
        // Get writer's current balance
        $currentBalance = $user->getCurrentBalance();
        $availableBalance = $user->getAvailableBalance();
        
        // Get recent transactions
        $recentTransactions = $user->getRecentTransactions(5);
        
        // Get statistics
        $totalCompletedOrders = Order::where('writer_id', $user->id)
            ->whereIn('status', [
                Order::STATUS_COMPLETED,
                Order::STATUS_PAID,
                Order::STATUS_FINISHED
            ])
            ->count();
        
        $totalEarnings = $user->writerProfile ? $user->writerProfile->earnings : 0;
        
        // Count dispute orders
        $totalDisputes = Order::where('writer_id', $user->id)
            ->where('status', Order::STATUS_DISPUTE)
            ->count();
        
        // Count orders by discipline
        $ordersByDiscipline = Order::where('writer_id', $user->id)
            ->whereIn('status', [
                Order::STATUS_COMPLETED,
                Order::STATUS_PAID,
                Order::STATUS_FINISHED
            ])
            ->select('discipline', DB::raw('count(*) as count'))
            ->groupBy('discipline')
            ->orderByDesc('count')
            ->take(5)
            ->get();
        
        return view('writers.dashboard', compact(
            'user',
            'availableOrders',
            'assignedOrders',
            'completedOrders',
            'currentBalance',
            'availableBalance',
            'recentTransactions',
            'totalCompletedOrders',
            'totalEarnings',
            'totalDisputes',
            'ordersByDiscipline'
        ));
    }

    /**
     * Show client dashboard
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    protected function clientDashboard()
    {
        $user = Auth::user();
        
        // Get client's orders
        $activeOrders = Order::where('client_id', $user->id)
            ->whereNotIn('status', [
                Order::STATUS_COMPLETED,
                Order::STATUS_PAID,
                Order::STATUS_FINISHED,
                Order::STATUS_CANCELLED
            ])
            ->latest()
            ->take(5)
            ->get();
        
        $completedOrders = Order::where('client_id', $user->id)
            ->whereIn('status', [
                Order::STATUS_COMPLETED,
                Order::STATUS_PAID,
                Order::STATUS_FINISHED
            ])
            ->latest()
            ->take(5)
            ->get();
        
        // Count total orders
        $totalOrders = Order::where('client_id', $user->id)->count();
        $totalActiveOrders = $activeOrders->count();
        $totalCompletedOrders = Order::where('client_id', $user->id)
            ->whereIn('status', [
                Order::STATUS_COMPLETED,
                Order::STATUS_PAID,
                Order::STATUS_FINISHED
            ])
            ->count();
        
        return view('clients.dashboard', compact(
            'user',
            'activeOrders',
            'completedOrders',
            'totalOrders',
            'totalActiveOrders',
            'totalCompletedOrders'
        ));
    }

    /**
     * Show support dashboard
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    protected function supportDashboard()
    {
        // Get recent orders in dispute
        $disputeOrders = Order::where('status', Order::STATUS_DISPUTE)
            ->with(['client', 'writer'])
            ->latest()
            ->take(10)
            ->get();
        
        // Get pending writer verifications
        $pendingVerifications = WriterProfile::where('id_verification_status', 'pending')
            ->with('user')
            ->latest()
            ->take(10)
            ->get();
        
        // Count orders by status
        $availableOrders = Order::available()->count();
        $assignedOrders = Order::assigned()->count();
        $disputeOrdersCount = Order::where('status', Order::STATUS_DISPUTE)->count();
        
        return view('support.dashboard', compact(
            'disputeOrders',
            'pendingVerifications',
            'availableOrders',
            'assignedOrders',
            'disputeOrdersCount'
        ));
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
        
        // Calculate total earnings in date range
        $totalEarnings = 0;
        foreach ($completedOrders as $order) {
            $totalEarnings += $order->price * 0.70; // 70% of order price goes to writer
        }
        
        // For disputes, check if there are any status changes indicating disputes
        $totalDisputes = Order::where('writer_id', $user->id)
            ->where('status', Order::STATUS_DISPUTE)
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->count();
        
        // Calculate percentages
        $disputesPercentage = $totalCompletedOrders > 0 ? round(($totalDisputes / $totalCompletedOrders) * 100, 1) : 0;
        
        // Get writer rating if available
        $writerRating = $user->rating ?? 0;
        
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
            'totalEarnings',
            'totalDisputes',
            'disputesPercentage',
            'writerRating',
            'startDate',
            'endDate'
        ));
    }

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
            'chart_type' => 'nullable|in:orders,pages,disputes,revenue',
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
            
            // Count disputes
            $disputes = Order::where('writer_id', Auth::id())
                ->where('status', Order::STATUS_DISPUTE)
                ->whereYear('updated_at', $date->format('Y'))
                ->whereMonth('updated_at', $date->format('m'))
                ->count();
            
            // Calculate revenue for this month
            $revenue = $monthOrders->sum('price') * 0.70; // 70% of order price
            
            $monthlyData[] = [
                'month' => $monthLabel,
                'orders' => $monthOrders->count(),
                'pages' => $monthOrders->sum('task_size'),
                'disputes' => $disputes,
                'revenue' => round($revenue, 2)
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
}