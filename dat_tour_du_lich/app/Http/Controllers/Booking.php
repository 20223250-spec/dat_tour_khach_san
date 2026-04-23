<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_COMPLETED = 'completed';

    public const PAYMENT_STATUS_UNPAID = 'unpaid';
    public const PAYMENT_STATUS_PARTIAL = 'partial';
    public const PAYMENT_STATUS_PAID = 'paid';
    public const PAYMENT_STATUS_REFUNDED = 'refunded';

    public const RESERVING_STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_CONFIRMED,
    ];

    protected $guarded = [];

    protected $casts = [
        'paid_amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tour()
    {
        return $this->belongsTo(Tour::class);
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_PENDING => 'Cho xac nhan',
            self::STATUS_CONFIRMED => 'Da xac nhan',
            self::STATUS_CANCELLED => 'Da huy',
            self::STATUS_COMPLETED => 'Hoan tat',
        ];
    }

    public function statusLabel(): string
    {
        return static::statusOptions()[$this->status] ?? ucfirst((string) $this->status);
    }

    public static function paymentStatusOptions(): array
    {
        return [
            self::PAYMENT_STATUS_UNPAID => 'Chua thanh toan',
            self::PAYMENT_STATUS_PARTIAL => 'Da dat coc',
            self::PAYMENT_STATUS_PAID => 'Da thanh toan',
            self::PAYMENT_STATUS_REFUNDED => 'Da hoan tien',
        ];
    }

    public function paymentStatusLabel(): string
    {
        return static::paymentStatusOptions()[$this->payment_status] ?? ucfirst(str_replace('_', ' ', (string) $this->payment_status));
    }

    public static function reservesInventoryForStatus(string $status): bool
    {
        return in_array($status, self::RESERVING_STATUSES, true);
    }
}
