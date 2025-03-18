<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bid extends Model
{
    protected $fillable = [
        'order_id', 'user_id', 'amount', 'delivery_time', 'cover_letter'
    ];
    
    protected $casts = [
        'amount' => 'decimal:2',
        'delivery_time' => 'datetime',
    ];
    
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}