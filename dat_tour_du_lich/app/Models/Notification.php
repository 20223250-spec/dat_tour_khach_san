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

    public static function createBookingAutoCancelled($userId, $booking)
    {
        return static::create([
            'user_id' => $userId,
            'type' => 'booking_auto_cancelled',
            'title' => 'Đơn đặt tour tự động hủy',
            'message' => "Đơn đặt tour '{$booking->tour->name}' của bạn đã được tự động hủy do tour đã đến ngày khởi hành.",
            'data' => [
                'booking_id' => $booking->id,
                'tour_id' => $booking->tour_id,
                'tour_name' => $booking->tour->name,
            ],
        ]);
    }

    public static function createDepartureReminder($userId, $booking, int $daysLeft)
    {
        $dayLabel = $daysLeft === 1 ? 'ngày mai' : "sau {$daysLeft} ngày";

        return static::create([
            'user_id' => $userId,
            'type' => 'departure_reminder',
            'title' => 'Nhắc lịch khởi hành tour',
            'message' => "Tour '{$booking->tour->name}' của bạn sẽ khởi hành {$dayLabel}. Vui lòng chuẩn bị trước chuyến đi.",
            'data' => [
                'booking_id' => $booking->id,
                'tour_id' => $booking->tour_id,
                'tour_name' => $booking->tour->name,
                'reminder_days' => $daysLeft,
                'start_date' => $booking->tour->start_date,
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
