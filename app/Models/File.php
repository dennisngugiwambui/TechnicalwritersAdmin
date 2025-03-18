<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class File extends Model
{
    protected $fillable = [
        'name', 'path', 'size', 'fileable_id', 'fileable_type', 'uploaded_by'
    ];
    
    // Polymorphic relationship
    public function fileable()
    {
        return $this->morphTo();
    }
    
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
