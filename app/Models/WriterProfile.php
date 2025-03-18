<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WriterProfile extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'writer_id',
        'phone_number',
        'national_id',
        'national_id_image',
        'id_verification_status',
        'id_rejection_reason',
        'country',
        'county',
        'native_language',
        'profile_picture',
        'education_level',
        'experience_years',
        'subjects',
        'bio',
        'night_calls',
        'force_assign',
        'linkedin',
        'facebook',
        'payment_method',
        'payment_details',
        'rating',
        'jobs_completed',
        'earnings'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'subjects' => 'array',
        'experience_years' => 'integer',
        'night_calls' => 'boolean',
        'force_assign' => 'boolean',
        'rating' => 'float',
        'jobs_completed' => 'integer',
        'earnings' => 'decimal:2'
    ];

    /**
     * Get the user that owns the profile.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Generate a unique writer ID based on the user ID
     *
     * @param int $userId
     * @return string
     */
    public static function generateWriterId($userId)
    {
        return 'WR' . str_pad($userId, 6, '0', STR_PAD_LEFT);
    }
    
    /**
     * Check if the ID is verified
     *
     * @return bool
     */
    public function isIdVerified()
    {
        return $this->id_verification_status === 'verified';
    }
}