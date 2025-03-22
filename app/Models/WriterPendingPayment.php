<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Models\Order;
use App\Models\Finance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WriterPendingPayment extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'writer_id',
        'order_id',
        'amount',
        'status',
        'release_date',
        'released_at',
        'released_by',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'release_date' => 'datetime',
        'released_at' => 'datetime',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_RELEASED = 'released';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Get the writer that owns the pending payment.
     */
    public function writer()
    {
        return $this->belongsTo(User::class, 'writer_id');
    }

    /**
     * Get the order associated with the pending payment.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the admin who released the payment.
     */
    public function releasedBy()
    {
        return $this->belongsTo(User::class, 'released_by');
    }

    /**
     * Get all pending payments ready for release
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getPaymentsReadyForRelease()
    {
        return self::where('status', self::STATUS_PENDING)
            ->where('release_date', '<=', now())
            ->get();
    }

    /**
     * Release payment to writer's balance
     * 
     * @param int $adminId The ID of the admin releasing the payment
     * @return bool
     */
    public function releasePayment($adminId)
    {
        // Only pending payments can be released
        if ($this->status !== self::STATUS_PENDING) {
            return false;
        }

        // Start database transaction
        DB::beginTransaction();

        try {
            // Update pending payment record
            $this->status = self::STATUS_RELEASED;
            $this->released_at = now();
            $this->released_by = $adminId;
            $this->save();

            // Add to writer's finance balance
            Finance::addOrderPayment(
                $this->writer_id,
                $this->order_id,
                $this->amount,
                'Payment for Order #' . $this->order_id . ' (Auto-released)',
                $adminId
            );

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to release payment: ' . $e->getMessage(), [
                'pending_payment_id' => $this->id,
                'writer_id' => $this->writer_id,
                'order_id' => $this->order_id,
                'amount' => $this->amount
            ]);
            return false;
        }
    }

    /**
     * Cancel a pending payment
     * 
     * @param int $adminId The ID of the admin cancelling the payment
     * @param string $reason Reason for cancellation
     * @return bool
     */
    public function cancelPayment($adminId, $reason = null)
    {
        // Only pending payments can be cancelled
        if ($this->status !== self::STATUS_PENDING) {
            return false;
        }

        $this->status = self::STATUS_CANCELLED;
        $this->released_by = $adminId;
        $this->released_at = now();
        $this->notes = $reason ? 'Cancelled: ' . $reason : 'Cancelled by admin';
        $this->save();

        return true;
    }

    /**
     * Get total pending amount for a writer
     * 
     * @param int $writerId
     * @return float
     */
    public static function getTotalPendingForWriter($writerId)
    {
        return self::where('writer_id', $writerId)
            ->where('status', self::STATUS_PENDING)
            ->sum('amount');
    }

    /**
     * Get expected release dates for a writer
     * 
     * @param int $writerId
     * @return \Illuminate\Support\Collection
     */
    public static function getUpcomingReleasesForWriter($writerId)
    {
        return self::where('writer_id', $writerId)
            ->where('status', self::STATUS_PENDING)
            ->where('release_date', '>', now())
            ->orderBy('release_date')
            ->select(DB::raw('DATE(release_date) as date'), DB::raw('SUM(amount) as total_amount'))
            ->groupBy(DB::raw('DATE(release_date)'))
            ->get();
    }

    /**
     * Get formatted amount
     *
     * @return string
     */
    public function getFormattedAmountAttribute()
    {
        return '$' . number_format($this->amount, 2);
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
            self::STATUS_RELEASED => 'bg-green-100 text-green-800',
            self::STATUS_CANCELLED => 'bg-red-100 text-red-800',
        ][$this->status] ?? 'bg-gray-100 text-gray-800';
        
        return '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ' . $badgeClasses . '">' 
            . ucfirst($this->status) . '</span>';
    }
}