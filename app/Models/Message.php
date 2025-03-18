<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Message extends Model
{
    protected $fillable = [
        'order_id', 'user_id', 'receiver_id', 'title', 'message', 
        'message_type', 'read_at', 'is_general'
    ];
    
    protected $casts = [
        'read_at' => 'datetime',
        'is_general' => 'boolean',
    ];
    
    public function order()
    {
        return $this->belongsTo(Order::class)->withDefault([
            'title' => 'General Inquiry'
        ]);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
    
    public function files()
    {
        return $this->morphMany(File::class, 'fileable');
    }
    
    // Helper methods to determine message direction
    public function isFromWriter()
    {
        return $this->user && $this->user->usertype === 'writer';
    }
    
    public function isFromClient()
    {
        return $this->user && $this->user->usertype === 'client';
    }
    
    public function isFromSupport()
    {
        return $this->user && in_array($this->user->usertype, ['admin', 'support']);
    }
    
    public function isSentByCurrentUser()
    {
        return $this->user_id == Auth::id();
    }
    
    public function isForCurrentUser()
    {
        return $this->receiver_id == Auth::id();
    }
    
    // Get the appropriate CSS classes based on message sender
    public function getSenderInitial()
    {
        if ($this->isFromWriter()) return 'W';
        if ($this->isFromClient()) return 'C';
        if ($this->isFromSupport()) return 'S';
        return '?';
    }
    
    public function getReceiverInitial()
    {
        $receiver = $this->receiver;
        if (!$receiver) return '?';
        
        if ($receiver->usertype === 'writer') return 'W';
        if ($receiver->usertype === 'client') return 'C';
        if (in_array($receiver->usertype, ['admin', 'support'])) return 'S';
        return '?';
    }
    
    public function getAvatarClasses()
    {
        $baseClasses = 'w-8 h-8 rounded-full flex items-center justify-center mr-4 flex-shrink-0 mt-1';
        
        if ($this->isSentByCurrentUser()) {
            return $baseClasses . ' bg-blue-100';
        } else if ($this->isFromClient()) {
            return $baseClasses . ' bg-gray-100';
        } else if ($this->isFromSupport()) {
            return $baseClasses . ' bg-green-100';
        }
        
        return $baseClasses . ' bg-gray-100';
    }
    
    public function getMessageBubbleClasses()
    {
        $baseClasses = 'max-w-lg rounded-lg p-4';
        
        if ($this->isSentByCurrentUser()) {
            return $baseClasses . ' bg-blue-50';
        } else if ($this->isFromClient()) {
            return $baseClasses . ' bg-gray-100';
        } else if ($this->isFromSupport()) {
            return $baseClasses . ' bg-green-50';
        }
        
        return $baseClasses . ' bg-gray-100';
    }
}