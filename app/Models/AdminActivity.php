<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AdminActivity extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'admin_id',
        'action',
        'description',
        'target_id',
        'target_type',
        'ip_address',
        'user_agent',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the admin that owns the activity.
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Get the target model of the activity.
     */
    public function target()
    {
        return $this->morphTo();
    }

    /**
     * Get the activity label.
     *
     * @return string
     */
    public function getActivityLabelAttribute()
    {
        $labels = [
            'profile_updated' => 'Updated',
            'password_updated' => 'Security',
            'two_factor_enabled' => 'Security',
            'two_factor_disabled' => 'Security',
            'devices_logged_out' => 'Security',
            'writer_approved' => 'Approved',
            'order_created' => 'Created',
            'order_assigned' => 'Assigned',
            'payment_processed' => 'Released',
            'warning_issued' => 'Warning',
            'account_suspended' => 'Suspended',
            'settings_updated' => 'Modified',
            'login' => 'Login',
            'logout' => 'Logout',
        ];

        return $labels[$this->action] ?? 'Action';
    }

    /**
     * Get the badge class for the activity.
     *
     * @return string
     */
    public function getBadgeClassAttribute()
    {
        $classes = [
            'profile_updated' => 'bg-blue-100 text-blue-800',
            'password_updated' => 'bg-purple-100 text-purple-800',
            'two_factor_enabled' => 'bg-purple-100 text-purple-800',
            'two_factor_disabled' => 'bg-purple-100 text-purple-800',
            'devices_logged_out' => 'bg-purple-100 text-purple-800',
            'writer_approved' => 'bg-green-100 text-green-800',
            'order_created' => 'bg-blue-100 text-blue-800',
            'order_assigned' => 'bg-blue-100 text-blue-800',
            'payment_processed' => 'bg-blue-100 text-blue-800',
            'warning_issued' => 'bg-yellow-100 text-yellow-800',
            'account_suspended' => 'bg-red-100 text-red-800',
            'settings_updated' => 'bg-purple-100 text-purple-800',
            'login' => 'bg-gray-100 text-gray-800',
            'logout' => 'bg-gray-100 text-gray-800',
        ];

        return $classes[$this->action] ?? 'bg-gray-100 text-gray-800';
    }

    /**
     * Get the formatted date attribute.
     * 
     * @return string
     */
    public function getFormattedDateAttribute()
    {
        $now = Carbon::now();
        $activityDate = $this->created_at;
        
        if ($activityDate->isToday()) {
            return 'Today at ' . $activityDate->format('g:i A');
        } elseif ($activityDate->isYesterday()) {
            return 'Yesterday at ' . $activityDate->format('g:i A');
        } elseif ($activityDate->diffInDays($now) < 7) {
            return $activityDate->format('l') . ' at ' . $activityDate->format('g:i A');
        } else {
            return $activityDate->format('M j, Y');
        }
    }
}