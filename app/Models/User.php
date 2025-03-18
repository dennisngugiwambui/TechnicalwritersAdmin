<?php
namespace App\Models;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'usertype', 
        'status',
        'profile_picture',
        'is_suspended',
        'password',
        'bio',
        'rating',
        'specialization',
        'last_active_at',
    ];
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_active_at' => 'datetime',
            'rating' => 'decimal:1',
        ];
    }
    // Role constants (using existing usertype values)
    const ROLE_ADMIN = 'admin';
    const ROLE_CLIENT = 'client';
    const ROLE_WRITER = 'writer';
    const ROLE_SUPPORT = 'support';
    
    // Writer status constants
    const STATUS_ACTIVE = 'active';
    const STATUS_PENDING = 'pending';
    const STATUS_SUSPENDED = 'suspended';
    const STATUS_BANNED = 'banned';
    
    // Verification status constants
    const VERIFICATION_PENDING = 'pending';
    const VERIFICATION_VERIFIED = 'verified';
    const VERIFICATION_FAILED = 'failed';
    
    // Relationships
    public function ordersAsClient()
    {
        return $this->hasMany(Order::class, 'client_id');
    }
    
    public function ordersAsWriter()
    {
        return $this->hasMany(Order::class, 'writer_id');
    }
    
    public function bids()
    {
        return $this->hasMany(Bid::class);
    }
    
    public function messages()
    {
        return $this->hasMany(Message::class);
    }
    
    public function files()
    {
        return $this->hasMany(File::class, 'uploaded_by');
    }
    
    /**
     * Get the assessments for the user
     */
    public function assessments()
    {
        return $this->hasMany(Assessment::class);
    }
    
    /**
     * Get the assessment results for the user
     */
    public function assessmentResults()
    {
        return $this->hasMany(AssessmentResult::class);
    }
    
    /**
     * Check if user is verified (has passed grammar assessment)
     *
     * @return bool
     */
    public function isVerified()
    {
        return $this->verification_status === self::VERIFICATION_VERIFIED;
    }
    
    /**
     * Check if user needs to take the grammar assessment
     *
     * @return bool
     */
    public function needsAssessment()
    {
        // Only writers need to take the assessment
        if ($this->usertype !== self::ROLE_WRITER) {
            return false;
        }
        
        return $this->verification_status === self::VERIFICATION_PENDING 
            || $this->verification_status === self::VERIFICATION_FAILED;
    }
    
    /**
     * Get the latest assessment result
     *
     * @return \App\Models\AssessmentResult|null
     */
    public function getLatestAssessmentResult()
    {
        return $this->assessmentResults()
            ->where('assessment_type', 'grammar')
            ->latest()
            ->first();
    }
    
    /**
     * Check if user can retake a failed assessment
     *
     * @return bool
     */
    public function canRetakeAssessment()
    {
        if ($this->verification_status !== self::VERIFICATION_FAILED) {
            return true;
        }
        
        $latestResult = $this->getLatestAssessmentResult();
        
        if (!$latestResult) {
            return true;
        }
        
        // Check if 7 days have passed since the last attempt
        return $latestResult->created_at->addDays(7)->isPast();
    }
    
    /**
     * Set user as verified (passed assessment)
     *
     * @return void
     */
    public function markAsVerified()
    {
        $this->verification_status = self::VERIFICATION_VERIFIED;
        $this->save();
    }
    
    /**
     * Set user as failed (failed assessment)
     *
     * @return void
     */
    public function markAsFailed()
    {
        $this->verification_status = self::VERIFICATION_FAILED;
        $this->save();
    }
        /**
     * Get the writer profile associated with the user.
     */
    public function writerProfile()
    {
        return $this->hasOne(WriterProfile::class);
    }

    /**
     * Get the writer's ID if they are a writer
     */
    public function getWriterIdAttribute()
    {
        if ($this->usertype === 'writer' && $this->writerProfile) {
            return $this->writerProfile->writer_id;
        }
        return null;
    }

    // Add these relationships to your User model

    /**
     * Get the financial transactions for the user.
     */
    public function financialTransactions()
    {
        return $this->hasMany(Finance::class);
    }

    /**
     * Get the user's current balance.
     */
    public function getCurrentBalance()
    {
        return Finance::getCurrentBalance($this->id);
    }

    /**
     * Get the user's available balance.
     */
    public function getAvailableBalance()
    {
        return Finance::getAvailableBalance($this->id);
    }

    /**
     * Get the user's recent transactions.
     */
    public function getRecentTransactions($limit = 10)
    {
        return Finance::getUserTransactions($this->id, $limit);
    }
}