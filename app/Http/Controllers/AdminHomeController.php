<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Finance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AdminHomeController extends Controller
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
     * Show the application dashboard based on user type.
     *
     * @return \Illuminate\Contracts\Support\Renderable|\Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        $user = Auth::user();
        
        // Check if user is an admin
        if ($user->usertype !== 'admin') {
            // Log out non-admin users
            Auth::logout();
            
            // Redirect to login with error
            return redirect()->route('login')
                ->withErrors(['phone' => 'These credentials do not have administrative access.']);
        }
        
        // Check admin account status
        if ($user->status === 'pending') {
            return redirect()->route('welcome');
        } elseif (in_array($user->status, ['failed', 'suspended', 'banned', 'terminated', 'locked']) || 
                 $user->is_suspended === 'yes') {
            return redirect()->route('failed');
        }

        // Dashboard statistics
        $availableOrdersCount = Order::where('status', Order::STATUS_AVAILABLE)->count();
    
        $inProgressOrdersCount = Order::whereIn('status', [
            Order::STATUS_CONFIRMED, 
            Order::STATUS_IN_PROGRESS,
            Order::STATUS_DONE,
            Order::STATUS_DELIVERED
        ])->count();
        
        $completedOrdersCount = Order::whereIn('status', [
            Order::STATUS_COMPLETED,
            Order::STATUS_PAID,
            Order::STATUS_FINISHED
        ])->count();
        
        $activeWritersCount = User::where('usertype', User::ROLE_WRITER)
            ->where('status', User::STATUS_ACTIVE)
            ->count();
        
        // Get top writers by completed orders
        $topWriters = User::where('usertype', User::ROLE_WRITER)
            ->withCount(['ordersAsWriter as orders_count' => function($query) {
                $query->whereIn('status', [
                    Order::STATUS_COMPLETED,
                    Order::STATUS_PAID,
                    Order::STATUS_FINISHED
                ]);
            }])
            ->orderByDesc('orders_count')
            ->limit(5)
            ->get();
        
        // Recent orders
        $recentOrders = Order::with('writer')
            ->latest()
            ->limit(10)
            ->get();
        
        // Orders requiring attention (revisions or approaching deadlines)
        $attentionOrders = Order::where(function($query) {
                $query->where('status', Order::STATUS_REVISION)
                    ->orWhere(function($q) {
                        $q->where('status', Order::STATUS_IN_PROGRESS)
                        ->where('deadline', '<=', Carbon::now()->addDays(1));
                    });
            })
            ->orderBy('deadline')
            ->limit(5)
            ->get();
        
        // Pending payment requests
        $pendingPayments = Finance::with('user')
            ->where('transaction_type', Finance::TYPE_WITHDRAWAL)
            ->where('status', Finance::STATUS_PENDING)
            ->latest()
            ->limit(5)
            ->get();
        
        // Prepare chart data
        [$monthlyOrdersLabels, $monthlyAvailableData, $monthlyCompletedData] = $this->getMonthlyOrdersData();
        [$disciplineLabels, $disciplineCounts] = $this->getOrdersByDisciplineData();
        
        return view('admin.index', compact(
            'availableOrdersCount',
            'inProgressOrdersCount',
            'completedOrdersCount',
            'activeWritersCount',
            'topWriters',
            'recentOrders',
            'attentionOrders',
            'pendingPayments',
            'monthlyOrdersLabels',
            'monthlyAvailableData',
            'monthlyCompletedData',
            'disciplineLabels',
            'disciplineCounts'
        ));
    }

    /**
     * Get chart data for AJAX requests
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getChartData(Request $request)
    {
        $year = $request->input('year', date('Y'));
        
        // Get monthly data for the selected year
        $available = [];
        $completed = [];
        
        for ($month = 1; $month <= 12; $month++) {
            $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();
            
            // Available orders count
            $available[] = Order::where('status', Order::STATUS_AVAILABLE)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();
            
            // Completed orders count
            $completed[] = Order::whereIn('status', [
                    Order::STATUS_COMPLETED,
                    Order::STATUS_PAID,
                    Order::STATUS_FINISHED
                ])
                ->whereBetween('updated_at', [$startDate, $endDate])
                ->count();
        }
        
        return response()->json([
            'available' => $available,
            'completed' => $completed
        ]);
    }
    
    /**
     * Get monthly orders data for chart
     *
     * @return array
     */
    private function getMonthlyOrdersData()
    {
        $labels = [];
        $availableData = [];
        $completedData = [];
        
        // Get data for the last 12 months
        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $labels[] = $month->format('M Y');
            
            $startDate = $month->copy()->startOfMonth();
            $endDate = $month->copy()->endOfMonth();
            
            // Available orders count
            $availableData[] = Order::where('status', Order::STATUS_AVAILABLE)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();
            
            // Completed orders count
            $completedData[] = Order::whereIn('status', [
                    Order::STATUS_COMPLETED,
                    Order::STATUS_PAID,
                    Order::STATUS_FINISHED
                ])
                ->whereBetween('updated_at', [$startDate, $endDate])
                ->count();
        }
        
        return [$labels, $availableData, $completedData];
    }
    
    /**
     * Get orders by discipline data for chart
     *
     * @return array
     */
    private function getOrdersByDisciplineData()
    {
        $disciplines = Order::select('discipline', DB::raw('count(*) as count'))
            ->whereNotNull('discipline')
            ->groupBy('discipline')
            ->orderByDesc('count')
            ->limit(6)
            ->get();
        
        $labels = [];
        $counts = [];
        
        foreach ($disciplines as $discipline) {
            $labels[] = $discipline->discipline;
            $counts[] = $discipline->count;
        }
        
        // If we have fewer than 6 disciplines, add "Other"
        if (count($labels) < 6) {
            $totalCount = Order::whereNotNull('discipline')->count();
            $countedSum = array_sum($counts);
            
            if ($totalCount > $countedSum) {
                $labels[] = 'Other';
                $counts[] = $totalCount - $countedSum;
            }
        }
        
        return [$labels, $counts];
    }
    
  
    
    
    

    /**
     * Display and manage the admin profile
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function profile()
    {
        $user = Auth::user();
        
        // Load recent admin activity
        $recentActivities = \App\Models\AdminActivity::where('admin_id', $user->id)
            ->latest()
            ->limit(5)
            ->get();
            
        // Get stats for the dashboard
        $stats = [
            'managed_writers' => User::where('usertype', User::ROLE_WRITER)->count(),
            'completed_orders' => Order::whereIn('status', [
                Order::STATUS_COMPLETED,
                Order::STATUS_PAID,
                Order::STATUS_FINISHED
            ])->count(),
            'pending_payments' => Finance::where('status', Finance::STATUS_PENDING)
                ->where('transaction_type', Finance::TYPE_WITHDRAWAL)
                ->count(),
            'total_earnings' => Finance::where('transaction_type', Finance::TYPE_WITHDRAWAL)
                ->where('status', Finance::STATUS_COMPLETED)
                ->sum('amount'),
        ];
        
        return view('admin.adminprofile', compact('user', 'recentActivities', 'stats'));
    }
    
    /**
     * Update the user profile
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'phone' => 'nullable|string|max:15',
            'bio' => 'nullable|string|max:1000',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);
        
        if ($request->hasFile('avatar')) {
            // Delete old profile picture if exists
            if ($user->profile_picture && Storage::disk('public')->exists('avatars/' . $user->profile_picture)) {
                Storage::disk('public')->delete('avatars/' . $user->profile_picture);
            }
            
            // Store new profile picture
            $avatarName = time() . '.' . $request->avatar->extension();
            $request->avatar->storeAs('avatars', $avatarName, 'public');
            $user->profile_picture = $avatarName; // FIXED: Use profile_picture instead of avatar
        }
        
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->phone = $validated['phone'] ?? $user->phone;
        $user->bio = $validated['bio'] ?? $user->bio;
        $user->save();
        
        // Log the activity
        $this->logAdminActivity('profile_updated', 'Updated profile information');
        
        return redirect()->route('admin.profile.show')->with('success', 'Profile updated successfully!');
    }
    
    /**
     * Update the user password
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);
        
        $user = Auth::user();
        
        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }
        
        $user->password = Hash::make($validated['password']);
        $user->save();
        
        // Log the activity
        $this->logAdminActivity('password_updated', 'Updated account password');
        
        return redirect()->route('admin.profile.show')->with('success', 'Password updated successfully!');
    }
    
    /**
     * Log out from other browser sessions.
     *
     * @param  \Illuminate\Http\Request  $request//-
     * @return \Illuminate\Http\RedirectResponse//-
     * This function logs out the current admin user from all other browser sessions.//+

     */
    public function logoutOtherDevices(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
        ]);

        $user = Auth::user();

        // Check if password matches
        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()
                ->withErrors(['current_password' => 'The password is incorrect.']);
        }

        // Log out from other devices
        Auth::logoutOtherDevices($request->current_password);

        // Log the activity
        $this->logAdminActivity('devices_logged_out', 'Logged out from other devices');

        return redirect()->route('admin.profile.show')
            ->with('success', 'You have been logged out from other devices.');
    }
    
    /**
     * Log admin activity.
     *
     * @param  string  $action
     * @param  string  $description
     * @param  mixed   $target_id
     * @param  string  $target_type
     * @return void
     */
    private function logAdminActivity($action, $description, $target_id = null, $target_type = null)
    {
        \App\Models\AdminActivity::create([
            'admin_id' => Auth::id(),
            'action' => $action,
            'description' => $description,
            'target_id' => $target_id,
            'target_type' => $target_type,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}