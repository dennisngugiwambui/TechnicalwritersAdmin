<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MpesaTransaction extends Model
{
    use HasFactory, SoftDeletes;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'checkout_request_id',
        'phone',
        'amount',
        'reference',
        'description',
        'order_id',
        'user_id',
        'status',
        'result_code',
        'result_desc',
        'transaction_id',
        'transaction_date',
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'datetime',
    ];
    
    /**
     * Get the order associated with this transaction.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    
    /**
     * Get the user associated with this transaction.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get the status badge HTML
     *
     * @return string
     */
    public function getStatusBadgeAttribute()
    {
        $badgeClasses = [
            'pending' => 'bg-yellow-100 text-yellow-800',
            'completed' => 'bg-green-100 text-green-800',
            'failed' => 'bg-red-100 text-red-800',
        ][$this->status] ?? 'bg-gray-100 text-gray-800';
        
        return '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ' . $badgeClasses . '">' 
            . ucfirst($this->status) . '</span>';
    }
    
    /**
     * Get the formatted phone number
     *
     * @return string
     */
    public function getFormattedPhoneAttribute()
    {
        // Format 254XXXXXXXXX to +254 XXX XXX XXX
        if (strlen($this->phone) >= 12 && substr($this->phone, 0, 3) === '254') {
            $phone = substr($this->phone, 3);
            return '+254 ' . substr($phone, 0, 3) . ' ' . substr($phone, 3, 3) . ' ' . substr($phone, 6);
        }
        
        return $this->phone;
    }
    
    /**
     * Get the formatted amount
     *
     * @return string
     */
    public function getFormattedAmountAttribute()
    {
        return 'KES ' . number_format($this->amount, 2);
    }
}