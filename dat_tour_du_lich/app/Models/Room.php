<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function bookings()
    {
        return $this->hasMany(RoomBooking::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset(ltrim($this->image, '/')) : null;
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
