<?php

namespace Database\Factories;

use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Room>
 */
class RoomFactory extends Factory
{
    protected $model = Room::class;

    public function definition(): array
    {
        $hotels = [
            'Blue Ocean Hotel',
            'Sapa Valley Retreat',
            'Da Lat Pine View',
            'Hoi An Riverside Stay',
            'Sunset Beach Resort',
            'Hanoi Old Quarter Suites',
        ];

        $roomTypes = [
            'Phòng Deluxe',
            'Phòng Superior',
            'Phòng Gia đình',
            'Suite hướng biển',
            'Suite hướng núi',
            'Phòng đôi tiêu chuẩn',
        ];

        $locations = [
            'Đà Nẵng',
            'Nha Trang',
            'Đà Lạt',
            'Hội An',
            'Sa Pa',
            'Phú Quốc',
        ];

        return [
            'title' => $roomTypes[array_rand($roomTypes)] . ' - ' . $hotels[array_rand($hotels)],
            'hotel_name' => $hotels[array_rand($hotels)],
            'location' => $locations[array_rand($locations)],
            'description' => fake()->realTextBetween(120, 180),
            'price_per_night' => fake()->randomFloat(0, 550000, 3500000),
            'guest_capacity' => fake()->numberBetween(1, 6),
            'available_rooms' => fake()->numberBetween(1, 12),
            'status' => fake()->randomElement(['active', 'active', 'active', 'hidden']),
        ];
    }
}
