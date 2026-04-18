<?php

namespace App\Models;

use App\Models\Room;
use App\Models\RoomBooking;
use App\Models\Tour;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Notification;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_CUSTOMER = 'customer';
    public const ROLE_TOUR_OWNER = 'tour_owner';
    public const ROLE_HOTEL_OWNER = 'hotel_owner';

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'is_admin',
        'role',
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
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_admin' => 'boolean',
    ];

    public function isAdmin(): bool
    {
        return $this->is_admin || $this->role === self::ROLE_ADMIN;
    }

    public function isTourOwner(): bool
    {
        return $this->role === self::ROLE_TOUR_OWNER;
    }

    public function isHotelOwner(): bool
    {
        return $this->role === self::ROLE_HOTEL_OWNER;
    }

    public function canManageTours(): bool
    {
        return $this->isAdmin() || $this->isTourOwner();
    }

    public function canManageRooms(): bool
    {
        return $this->isAdmin() || $this->isHotelOwner();
    }

    public function roleLabel(): string
    {
        return match ($this->role) {
            self::ROLE_ADMIN => 'Quản trị viên',
            self::ROLE_TOUR_OWNER => 'Chủ tour',
            self::ROLE_HOTEL_OWNER => 'Chủ khách sạn',
            default => 'Khách hàng',
        };
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function roomBookings()
    {
        return $this->hasMany(RoomBooking::class);
    }

    public function tours()
    {
        return $this->hasMany(Tour::class, 'owner_id');
    }

    public function rooms()
    {
        return $this->hasMany(Room::class, 'owner_id');
    }
}
