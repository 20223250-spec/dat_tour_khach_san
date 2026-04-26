<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Tour;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tour>
 */
class TourFactory extends Factory
{
    protected $model = Tour::class;

    public function definition(): array
    {
        $imageUrls = [
            'https://picsum.photos/seed/tour-hanoi/1200/800',
            'https://picsum.photos/seed/tour-danang/1200/800',
            'https://picsum.photos/seed/tour-phuquoc/1200/800',
            'https://picsum.photos/seed/tour-sapa/1200/800',
            'https://picsum.photos/seed/tour-dalat/1200/800',
            'https://picsum.photos/seed/tour-hoian/1200/800',
        ];

        $destinations = [
            'Hà Nội',
            'Hội An',
            'Đà Nẵng',
            'Nha Trang',
            'Đà Lạt',
            'Phú Quốc',
            'Hạ Long',
            'Huế',
            'Sapa',
            'Cần Thơ',
        ];

        $tourNames = [
            'Hành trình di sản',
            'Trải nghiệm miền Trung',
            'Tour biển đảo cao cấp',
            'Du ngoạn sông nước',
            'Kỳ nghỉ cuối tuần sang trọng',
            'Tour ẩm thực Việt Nam',
            'Khám phá thiên nhiên',
            'Hành trình hoàng hôn',
            'Tour văn hóa đặc sắc',
            'Chuyến đi gia đình hoàn hảo',
        ];

        return [
            'name' => $tourNames[array_rand($tourNames)] . ' tại ' . $destinations[array_rand($destinations)],
            'description' => fake()->realTextBetween(120, 180),
            'image' => $imageUrls[array_rand($imageUrls)],
            'destination' => $destinations[array_rand($destinations)],
            'price' => fake()->randomFloat(0, 1500000, 8500000),
            'duration_days' => fake()->numberBetween(2, 10),
            'available_seats' => fake()->numberBetween(8, 40),
            'start_date' => fake()->dateTimeBetween('+5 days', '+2 months')->format('Y-m-d'),
        ];
    }
}