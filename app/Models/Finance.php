<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class Finance extends Model
{
    use SoftDeletes;
    
    protected $table = 'finances';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'order_id',
        'transaction_type',
        'amount',
        'balance_before',
        'balance_after',
        'status',
        'payment_method',
        'payment_reference',
        'description',
        'processed_by',
        'processed_at',
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'processed_at' => 'datetime',
    ];
    
    // Transaction types
    const TYPE_ORDER_PAYMENT = 'order_payment';
    const TYPE_WITHDRAWAL = 'withdrawal';
    const TYPE_REFUND = 'refund';
    const TYPE_BONUS = 'bonus';
    const TYPE_PENALTY = 'penalty';
    
    // Transaction statuses
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';
    
    /**
     * Get the user that owns the transaction.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get the order associated with the transaction.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    
    /**
     * Get the user who processed the transaction.
     */
    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
    
    /**
     * Get current balance for a user
     *
     * @param int $userId
     * @return float
     */
    public static function getCurrentBalance($userId)
    {
        $latestTransaction = self::where('user_id', $userId)
            ->orderBy('id', 'desc')
            ->first();
            
        return $latestTransaction ? $latestTransaction->balance_after : 0;
    }
    
    /**
     * Get available balance for a user (excluding pending withdrawals)
     *
     * @param int $userId
     * @return float
     */
    public static function getAvailableBalance($userId)
    {
        $balance = self::getCurrentBalance($userId);
        
        // Subtract pending withdrawals
        $pendingWithdrawals = self::where('user_id', $userId)
            ->where('transaction_type', self::TYPE_WITHDRAWAL)
            ->whereIn('status', [self::STATUS_PENDING, self::STATUS_PROCESSING])
            ->sum('amount');
            
        return $balance - $pendingWithdrawals;
    }
    
    /**
     * Get transactions for a user
     *
     * @param int $userId
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getUserTransactions($userId, $limit = 10)
    {
        return self::where('user_id', $userId)
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->get();
    }
    
    /**
     * Add payment for completed order
     *
     * @param int $userId
     * @param int $orderId
     * @param float $amount
     * @param string $description
     * @param int|null $processedBy
     * @return \App\Models\Finance
     */
    public static function addOrderPayment($userId, $orderId, $amount, $description, $processedBy = null)
    {
        // Get current balance
        $currentBalance = self::getCurrentBalance($userId);
        $newBalance = $currentBalance + $amount;
        
        // Create transaction
        return self::create([
            'user_id' => $userId,
            'order_id' => $orderId,
            'transaction_type' => self::TYPE_ORDER_PAYMENT,
            'amount' => $amount,
            'balance_before' => $currentBalance,
            'balance_after' => $newBalance,
            'status' => self::STATUS_COMPLETED,
            'description' => $description,
            'processed_by' => $processedBy,
            'processed_at' => now(),
        ]);
    }
    
    /**
     * Request a withdrawal
     *
     * @param int $userId
     * @param float $amount
     * @param string $paymentMethod
     * @param string $description
     * @return \App\Models\Finance|bool
     */
    public static function requestWithdrawal($userId, $amount, $paymentMethod, $description = 'Withdrawal request')
    {
        // Check if user has sufficient balance
        $availableBalance = self::getAvailableBalance($userId);
        
        if ($availableBalance < $amount) {
            return false;
        }
        
        // Get current balance
        $currentBalance = self::getCurrentBalance($userId);
        
        // Create transaction
        return self::create([
            'user_id' => $userId,
            'transaction_type' => self::TYPE_WITHDRAWAL,
            'amount' => $amount,
            'balance_before' => $currentBalance,
            'balance_after' => $currentBalance, // Balance doesn't change until processed
            'status' => self::STATUS_PENDING,
            'payment_method' => $paymentMethod,
            'description' => $description,
        ]);
    }
    
    /**
     * Process a withdrawal request
     *
     * @param int $transactionId
     * @param int $processedBy
     * @param string $status
     * @param string|null $reference
     * @return \App\Models\Finance|bool
     */
    public static function processWithdrawal($transactionId, $processedBy, $status = self::STATUS_COMPLETED, $reference = null)
    {
        // Find the withdrawal transaction
        $transaction = self::findOrFail($transactionId);
        
        // Check if it's a pending withdrawal
        if ($transaction->transaction_type !== self::TYPE_WITHDRAWAL || 
            $transaction->status !== self::STATUS_PENDING) {
            return false;
        }
        
        // Get current balance
        $currentBalance = self::getCurrentBalance($transaction->user_id);
        
        // Update the transaction
        $transaction->status = $status;
        $transaction->processed_by = $processedBy;
        $transaction->processed_at = now();
        
        if ($status === self::STATUS_COMPLETED) {
            $transaction->balance_after = $currentBalance - $transaction->amount;
            $transaction->payment_reference = $reference;
        }
        
        $transaction->save();
        
        return $transaction;
    }
    
    /**
     * Add a bonus to a user
     *
     * @param int $userId
     * @param float $amount
     * @param string $description
     * @param int|null $processedBy
     * @return \App\Models\Finance
     */
    public static function addBonus($userId, $amount, $description, $processedBy = null)
    {
        // Get current balance
        $currentBalance = self::getCurrentBalance($userId);
        $newBalance = $currentBalance + $amount;
        
        // Create transaction
        return self::create([
            'user_id' => $userId,
            'transaction_type' => self::TYPE_BONUS,
            'amount' => $amount,
            'balance_before' => $currentBalance,
            'balance_after' => $newBalance,
            'status' => self::STATUS_COMPLETED,
            'description' => $description,
            'processed_by' => $processedBy,
            'processed_at' => now(),
        ]);
    }
    
    /**
     * Add a penalty to a user
     *
     * @param int $userId
     * @param float $amount
     * @param string $description
     * @param int|null $processedBy
     * @return \App\Models\Finance
     */
    public static function addPenalty($userId, $amount, $description, $processedBy = null)
    {
        // Get current balance
        $currentBalance = self::getCurrentBalance($userId);
        $newBalance = $currentBalance - $amount;
        
        // Create transaction
        return self::create([
            'user_id' => $userId,
            'transaction_type' => self::TYPE_PENALTY,
            'amount' => $amount,
            'balance_before' => $currentBalance,
            'balance_after' => $newBalance,
            'status' => self::STATUS_COMPLETED,
            'description' => $description,
            'processed_by' => $processedBy,
            'processed_at' => now(),
        ]);
    }
    
    /**
     * Process a refund
     *
     * @param int $orderId
     * @param float $amount
     * @param string $description
     * @param int|null $processedBy
     * @return \App\Models\Finance
     */
    public static function processRefund($orderId, $amount, $description, $processedBy = null)
    {
        $order = Order::findOrFail($orderId);
        
        if ($order->writer_id) {
            // Get current balance
            $currentBalance = self::getCurrentBalance($order->writer_id);
            $newBalance = $currentBalance - $amount;
            
            // Create transaction
            return self::create([
                'user_id' => $order->writer_id,
                'order_id' => $orderId,
                'transaction_type' => self::TYPE_REFUND,
                'amount' => $amount,
                'balance_before' => $currentBalance,
                'balance_after' => $newBalance,
                'status' => self::STATUS_COMPLETED,
                'description' => $description,
                'processed_by' => $processedBy,
                'processed_at' => now(),
            ]);
        }
        
        return false;
    }
    
    /**
     * Get formatted amount with sign
     *
     * @return string
     */
    public function getFormattedAmountAttribute()
    {
        $sign = in_array($this->transaction_type, [self::TYPE_WITHDRAWAL, self::TYPE_PENALTY, self::TYPE_REFUND]) ? '-' : '+';
        return $sign . '$' . number_format($this->amount, 2);
    }
    
    /**
     * Get status badge HTML
     *
     * @return string
     */
    public function getStatusBadgeAttribute()
    {
        $badgeClasses = [
            self::STATUS_PENDING => 'bg-yellow-100 text-yellow-800',
            self::STATUS_PROCESSING => 'bg-blue-100 text-blue-800',
            self::STATUS_COMPLETED => 'bg-green-100 text-green-800',
            self::STATUS_FAILED => 'bg-red-100 text-red-800',
            self::STATUS_CANCELLED => 'bg-gray-100 text-gray-800',
        ][$this->status] ?? 'bg-gray-100 text-gray-800';
        
        return '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ' . $badgeClasses . '">' 
            . ucfirst($this->status) . '</span>';
    }
    
    /**
     * Get transaction type badge HTML
     *
     * @return string
     */
    public function getTypeBadgeAttribute()
    {
        $badgeClasses = [
            self::TYPE_ORDER_PAYMENT => 'bg-green-100 text-green-800',
            self::TYPE_WITHDRAWAL => 'bg-blue-100 text-blue-800',
            self::TYPE_REFUND => 'bg-red-100 text-red-800',
            self::TYPE_BONUS => 'bg-purple-100 text-purple-800',
            self::TYPE_PENALTY => 'bg-orange-100 text-orange-800',
        ][$this->transaction_type] ?? 'bg-gray-100 text-gray-800';
        
        $typeLabels = [
            self::TYPE_ORDER_PAYMENT => 'Payment',
            self::TYPE_WITHDRAWAL => 'Withdrawal',
            self::TYPE_REFUND => 'Refund',
            self::TYPE_BONUS => 'Bonus',
            self::TYPE_PENALTY => 'Penalty',
        ];
        
        $label = $typeLabels[$this->transaction_type] ?? ucfirst(str_replace('_', ' ', $this->transaction_type));
        
        return '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ' . $badgeClasses . '">' 
            . $label . '</span>';
    }
}