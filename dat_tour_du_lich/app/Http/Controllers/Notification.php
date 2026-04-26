<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function markAsRead(): void
    {
        if (! $this->is_read) {
            $this->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }
    }

    public function markAsUnread(): void
    {
        $this->update([
            'is_read' => false,
            'read_at' => null,
        ]);
    }

    public static function createBookingReceived($userId, $booking)
    {
        return static::create([
            'user_id' => $userId,
            'type' => 'booking_received',
            'title' => 'Đã nhận yêu cầu đặt tour',
            'message' => "Chúng tôi đã ghi nhận yêu cầu đặt tour '{$booking->tour->name}' của bạn và sẽ sớm xác nhận.",
            'data' => [
                'booking_id' => $booking->id,
                'tour_id' => $booking->tour_id,
                'tour_name' => $booking->tour->name,
            ],
        ]);
    }

    public static function createBookingConfirmed($userId, $booking)
    {
        return static::create([
            'user_id' => $userId,
            'type' => 'booking_confirmed',
            'title' => 'Đơn đặt tour đã được xác nhận',
            'message' => "Đơn đặt tour '{$booking->tour->name}' của bạn đã được xác nhận thành công.",
            'data' => [
                'booking_id' => $booking->id,
                'tour_id' => $booking->tour_id,
                'tour_name' => $booking->tour->name,
            ],
        ]);
    }

    public static function createBookingCancelled($userId, $booking)
    {
        return static::create([
            'user_id' => $userId,
            'type' => 'booking_cancelled',
            'title' => 'Đơn đặt tour đã bị hủy',
            'message' => "Đơn đặt tour '{$booking->tour->name}' của bạn đã bị hủy.",
            'data' => [
                'booking_id' => $booking->id,
                'tour_id' => $booking->tour_id,
                'tour_name' => $booking->tour->name,
            ],
        ]);
    }

    public static function createRoomBookingReceived($userId, $booking)
    {
        return static::create([
            'user_id' => $userId,
            'type' => 'room_booking_received',
            'title' => 'Da nhan yeu cau dat phong',
            'message' => "He thong da ghi nhan yeu cau dat phong '{$booking->room->title}' cua ban.",
            'data' => [
                'room_booking_id' => $booking->id,
                'room_id' => $booking->room_id,
                'room_title' => $booking->room->title,
            ],
        ]);
    }

    public static function createRoomBookingConfirmed($userId, $booking)
    {
        return static::create([
            'user_id' => $userId,
            'type' => 'room_booking_confirmed',
            'title' => 'Don dat phong da duoc xac nhan',
            'message' => "Don dat phong '{$booking->room->title}' cua ban da duoc xac nhan.",
            'data' => [
                'room_booking_id' => $booking->id,
                'room_id' => $booking->room_id,
                'room_title' => $booking->room->title,
            ],
        ]);
    }

    public static function createRoomBookingCheckedIn($userId, $booking)
    {
        return static::create([
            'user_id' => $userId,
            'type' => 'room_booking_checked_in',
            'title' => 'Ban da nhan phong',
            'message' => "Don dat phong '{$booking->room->title}' da duoc cap nhat sang da nhan phong.",
            'data' => [
                'room_booking_id' => $booking->id,
                'room_id' => $booking->room_id,
                'room_title' => $booking->room->title,
            ],
        ]);
    }

    public static function createRoomBookingNoShow($userId, $booking)
    {
        return static::create([
            'user_id' => $userId,
            'type' => 'room_booking_no_show',
            'title' => 'Don dat phong duoc danh dau vang mat',
            'message' => "Don dat phong '{$booking->room->title}' da duoc danh dau khong den nhan phong.",
            'data' => [
                'room_booking_id' => $booking->id,
                'room_id' => $booking->room_id,
                'room_title' => $booking->room->title,
            ],
        ]);
    }

    public static function createRoomBookingCancelled($userId, $booking)
    {
        return static::create([
            'user_id' => $userId,
            'type' => 'room_booking_cancelled',
            'title' => 'Don dat phong da bi huy',
            'message' => "Don dat phong '{$booking->room->title}' cua ban da bi huy.",
            'data' => [
                'room_booking_id' => $booking->id,
                'room_id' => $booking->room_id,
                'room_title' => $booking->room->title,
            ],
        ]);
    }

    public static function createRoomBookingCompleted($userId, $booking)
    {
        return static::create([
            'user_id' => $userId,
            'type' => 'room_booking_completed',
            'title' => 'Don dat phong da tra phong',
            'message' => "Don dat phong '{$booking->room->title}' da duoc cap nhat da tra phong.",
            'data' => [
                'room_booking_id' => $booking->id,
                'room_id' => $booking->room_id,
                'room_title' => $booking->room->title,
            ],
        ]);
    }

    public static function createTourUpdated($userId, $tour)
    {
        return static::create([
            'user_id' => $userId,
            'type' => 'tour_updated',
            'title' => 'Tour đã được cập nhật',
            'message' => "Tour '{$tour->name}' mà bạn quan tâm đã có cập nhật mới.",
            'data' => [
                'tour_id' => $tour->id,
                'tour_name' => $tour->name,
            ],
        ]);
    }
}
