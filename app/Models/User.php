<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // User types
    const ROLE_ADMIN = 'admin';
    const ROLE_WRITER = 'writer';
    const ROLE_CLIENT = 'client';
    const ROLE_SUPPORT = 'support';
    
    // User statuses
    const STATUS_ACTIVE = 'active';
    const STATUS_PENDING = 'pending';
    const STATUS_SUSPENDED = 'suspended';
    const STATUS_BANNED = 'banned';
    const STATUS_FAILED = 'failed';
    const STATUS_VACATION = 'vacation';
    const STATUS_INACTIVE = 'inactive';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'usertype',
        'status',
        'bio',
        'profile_picture',
        'is_suspended',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_active_at' => 'datetime',
    ];

    /**
     * Get the writer profile associated with the user.
     */
    public function writerProfile()
    {
        return $this->hasOne(WriterProfile::class);
    }

    /**
     * Get orders where this user is the client.
     */
    public function ordersAsClient()
    {
        return $this->hasMany(Order::class, 'client_id');
    }

    /**
     * Get orders where this user is the writer.
     */
    public function ordersAsWriter()
    {
        return $this->hasMany(Order::class, 'writer_id');
    }

    /**
     * Get all bids placed by this user.
     */
    public function bids()
    {
        return $this->hasMany(Bid::class, 'user_id');
    }

    /**
     * Get all messages sent by this user.
     */
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'user_id');
    }

    /**
     * Get all messages received by this user.
     */
    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    /**
     * Scope a query to only include writers.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWriters($query)
    {
        return $query->where('usertype', self::ROLE_WRITER);
    }

    /**
     * Scope a query to only include active users.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Get all orders that have been finished by this writer.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function finishedOrders()
    {
        return $this->ordersAsWriter()->whereIn('status', [
            Order::STATUS_COMPLETED,
            Order::STATUS_PAID,
            Order::STATUS_FINISHED
        ]);
    }

    /**
     * Check if the user is a writer.
     *
     * @return bool
     */
    public function isWriter()
    {
        return $this->usertype === self::ROLE_WRITER;
    }

    /**
     * Check if the user is an admin.
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->usertype === self::ROLE_ADMIN;
    }

    /**
     * Check if the user is a client.
     *
     * @return bool
     */
    public function isClient()
    {
        return $this->usertype === self::ROLE_CLIENT;
    }

    /**
     * Check if the user is a support staff.
     *
     * @return bool
     */
    public function isSupport()
    {
        return $this->usertype === self::ROLE_SUPPORT;
    }

    /**
     * Check if the user has an active status.
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Get the display name with user type.
     *
     * @return string
     */
    public function getDisplayNameAttribute()
    {
        return $this->name . ' (' . ucfirst($this->usertype) . ')';
    }

    /**
     * Get the initials for avatar display.
     *
     * @return string
     */
    public function getInitialsAttribute()
    {
        $nameParts = explode(' ', $this->name);
        $initials = '';
        
        foreach ($nameParts as $part) {
            if (!empty($part)) {
                $initials .= strtoupper(substr($part, 0, 1));
                if (strlen($initials) >= 2) break;
            }
        }
        
        return $initials ?: strtoupper(substr($this->name, 0, 1));
    }
}