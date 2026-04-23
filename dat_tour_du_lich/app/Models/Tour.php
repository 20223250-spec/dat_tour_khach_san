<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Tour extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'price' => 'decimal:2',
        'start_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (Tour $tour) {
            if (! array_key_exists('total_seats', $tour->attributes) || $tour->attributes['total_seats'] === null) {
                $tour->attributes['total_seats'] = (int) ($tour->attributes['available_seats'] ?? 0);
            }

            if (! array_key_exists('available_seats', $tour->attributes) || $tour->attributes['available_seats'] === null) {
                $tour->attributes['available_seats'] = (int) $tour->attributes['total_seats'];
            }
        });
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset(ltrim($this->image, '/')) : null;
    }

    public function scopeWithAvailabilityMetrics(Builder $query): Builder
    {
        return $query->addSelect([
            'reserved_seats' => Booking::query()
                ->selectRaw('COALESCE(SUM(number_of_people), 0)')
                ->whereColumn('tour_id', 'tours.id')
                ->whereIn('status', Booking::RESERVING_STATUSES),
        ]);
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->whereDate('start_date', '>', now()->toDateString());
    }

    public function scopeBookable(Builder $query): Builder
    {
        return $query->whereRaw(
            'COALESCE(total_seats, available_seats, 0) > COALESCE((select sum(number_of_people) from bookings where bookings.tour_id = tours.id and status in (?, ?)), 0)',
            Booking::RESERVING_STATUSES
        );
    }

    public function totalSeats(): int
    {
        return (int) ($this->attributes['total_seats'] ?? $this->attributes['available_seats'] ?? 0);
    }

    public function reservedSeats(): int
    {
        if (array_key_exists('reserved_seats', $this->attributes)) {
            return (int) $this->attributes['reserved_seats'];
        }

        return (int) $this->bookings()
            ->whereIn('status', Booking::RESERVING_STATUSES)
            ->sum('number_of_people');
    }

    public function availableSeats(): int
    {
        return max(0, $this->totalSeats() - $this->reservedSeats());
    }

    public function syncAvailabilityCache(): void
    {
        $this->forceFill([
            'available_seats' => $this->availableSeats(),
        ])->saveQuietly();
    }

    public function getTotalSeatsAttribute($value): int
    {
        return (int) ($value ?? $this->attributes['available_seats'] ?? 0);
    }

    public function getAvailableSeatsAttribute($value): int
    {
        if (array_key_exists('total_seats', $this->attributes)) {
            return $this->availableSeats();
        }

        return (int) $value;
    }
}
