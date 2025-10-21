<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Delivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'sender_name',
        'receiver_name',
        'address',
        'notes',
        'status',
        'photo',
    ];

    protected $casts = [

    ];

    protected $appends = ['photo_url'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getPhotoUrlAttribute()
    {
        return $this->photo ? Storage::url($this->photo) : null;
    }
}
