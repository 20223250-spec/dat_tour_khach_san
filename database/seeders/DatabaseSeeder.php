<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Notification;
use App\Models\Review;
use App\Models\Tour;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $faker = fake();

        User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
                'is_admin' => false,
                'email_verified_at' => now(),
            ]
        );

        $users = User::factory(120)->create();
        $tours = Tour::factory(160)->create();

        $userIds = $users->pluck('id')->all();
        $toursById = $tours->keyBy('id');
        $tourIds = $toursById->keys()->all();

        $bookingStatuses = ['pending', 'pending', 'confirmed', 'confirmed', 'confirmed', 'cancelled', 'completed'];
        $createdBookings = collect();

        for ($i = 0; $i < 1000; $i++) {
            $tourId = $tourIds[array_rand($tourIds)];
            $tour = $toursById[$tourId];
            $people = $faker->numberBetween(1, 6);
            $createdAt = $faker->dateTimeBetween('-10 months', 'now');

            $createdBookings->push(Booking::query()->create([
                'user_id' => $userIds[array_rand($userIds)],
                'tour_id' => $tourId,
                'number_of_people' => $people,
                'total_price' => (float) $tour->price * $people,
                'status' => $bookingStatuses[array_rand($bookingStatuses)],
                'customer_name' => $faker->name(),
                'customer_phone' => $faker->numerify('0#########'),
                'created_at' => $createdAt,
                'updated_at' => $faker->dateTimeBetween($createdAt, 'now'),
            ]));
        }

        $reviewRows = [];
        $usedPairs = [];
        $reviewTarget = min(900, count($userIds) * count($tourIds));

        while (count($reviewRows) < $reviewTarget) {
            $userId = $userIds[array_rand($userIds)];
            $tourId = $tourIds[array_rand($tourIds)];
            $pairKey = $userId . '_' . $tourId;

            if (isset($usedPairs[$pairKey])) {
                continue;
            }

            $usedPairs[$pairKey] = true;
            $createdAt = $faker->dateTimeBetween('-8 months', 'now');

            $reviewRows[] = [
                'user_id' => $userId,
                'tour_id' => $tourId,
                'rating' => $faker->numberBetween(3, 5),
                'comment' => $faker->optional(0.85)->sentence(18),
                'created_at' => $createdAt,
                'updated_at' => $faker->dateTimeBetween($createdAt, 'now'),
            ];
        }

        Review::query()->insert($reviewRows);

        $notificationRows = [];

        foreach ($createdBookings->shuffle()->take(1300) as $booking) {
            $tourName = $toursById[$booking->tour_id]->name ?? 'Tour';
            $type = match ($booking->status) {
                'pending' => 'booking_received',
                'confirmed', 'completed' => 'booking_confirmed',
                'cancelled' => 'booking_cancelled',
                default => 'booking_received',
            };

            $title = match ($type) {
                'booking_received' => 'Booking received',
                'booking_confirmed' => 'Booking confirmed',
                'booking_cancelled' => 'Booking cancelled',
                default => 'Booking update',
            };

            $message = match ($type) {
                'booking_received' => "Your booking for {$tourName} has been received.",
                'booking_confirmed' => "Your booking for {$tourName} has been confirmed.",
                'booking_cancelled' => "Your booking for {$tourName} has been cancelled.",
                default => "There is a new update for {$tourName}.",
            };

            $createdAt = $faker->dateTimeBetween($booking->created_at, 'now');
            $isRead = $faker->boolean(65);

            $notificationRows[] = [
                'user_id' => $booking->user_id,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'data' => json_encode([
                    'booking_id' => $booking->id,
                    'tour_id' => $booking->tour_id,
                    'tour_name' => $tourName,
                ], JSON_THROW_ON_ERROR),
                'is_read' => $isRead,
                'read_at' => $isRead ? $faker->dateTimeBetween($createdAt, 'now') : null,
                'created_at' => $createdAt,
                'updated_at' => $faker->dateTimeBetween($createdAt, 'now'),
            ];
        }

        for ($i = 0; $i < 400; $i++) {
            $tourId = $tourIds[array_rand($tourIds)];
            $tourName = $toursById[$tourId]->name ?? 'Tour';
            $createdAt = $faker->dateTimeBetween('-4 months', 'now');
            $isRead = $faker->boolean(55);

            $notificationRows[] = [
                'user_id' => $userIds[array_rand($userIds)],
                'type' => 'tour_updated',
                'title' => 'Tour updated',
                'message' => "{$tourName} has new information available.",
                'data' => json_encode([
                    'tour_id' => $tourId,
                    'tour_name' => $tourName,
                ], JSON_THROW_ON_ERROR),
                'is_read' => $isRead,
                'read_at' => $isRead ? $faker->dateTimeBetween($createdAt, 'now') : null,
                'created_at' => $createdAt,
                'updated_at' => $faker->dateTimeBetween($createdAt, 'now'),
            ];
        }

        Notification::query()->insert($notificationRows);
    }
}
