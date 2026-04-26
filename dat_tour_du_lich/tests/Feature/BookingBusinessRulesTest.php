<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Room;
use App\Models\RoomBooking;
use App\Models\Tour;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingBusinessRulesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    public function test_user_cannot_book_a_tour_that_has_closed_booking(): void
    {
        $user = User::factory()->create();
        $tour = Tour::create([
            'name' => 'Da Nang da ket thuc',
            'description' => 'Tour da qua ngay khoi hanh.',
            'destination' => 'Da Nang',
            'price' => 2500000,
            'duration_days' => 3,
            'available_seats' => 10,
            'start_date' => now()->subDay()->toDateString(),
        ]);

        $response = $this->actingAs($user)
            ->from(route('tours.show', $tour->id))
            ->post(route('bookings.store', $tour->id), [
                'customer_name' => 'Nguyen Van A',
                'customer_phone' => '0909999999',
                'number_of_people' => 2,
            ]);

        $response->assertRedirect(route('tours.show', $tour->id));
        $response->assertSessionHasErrors('message');
        $this->assertDatabaseCount('bookings', 0);
        $this->assertSame(10, $tour->fresh()->available_seats);
    }

    public function test_cancelling_tour_booking_restores_available_seats(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
            'role' => User::ROLE_ADMIN,
        ]);
        $customer = User::factory()->create();
        $tour = Tour::create([
            'name' => 'Tour Da Lat',
            'description' => 'Tour test.',
            'destination' => 'Da Lat',
            'price' => 3000000,
            'duration_days' => 2,
            'available_seats' => 7,
            'start_date' => now()->addDays(10)->toDateString(),
        ]);
        $booking = Booking::create([
            'user_id' => $customer->id,
            'tour_id' => $tour->id,
            'number_of_people' => 3,
            'total_price' => 9000000,
            'customer_name' => 'Le Thi B',
            'customer_phone' => '0901000001',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.bookings.update-status', $booking->id), [
            'status' => 'cancelled',
        ]);

        $response->assertRedirect(route('home', ['admin_active_tab' => 'don-dat']) . '#quan-tri-noi-bo');
        $this->assertSame('cancelled', $booking->fresh()->status);
        $this->assertSame(10, $tour->fresh()->available_seats);
    }

    public function test_tour_owner_can_manage_booking_for_their_own_tour(): void
    {
        $owner = User::factory()->tourOwner()->create();
        $customer = User::factory()->create();
        $tour = Tour::create([
            'owner_id' => $owner->id,
            'name' => 'Tour Quy Nhon',
            'description' => 'Tour test.',
            'destination' => 'Quy Nhon',
            'price' => 2800000,
            'duration_days' => 3,
            'available_seats' => 8,
            'start_date' => now()->addDays(7)->toDateString(),
        ]);
        $booking = Booking::create([
            'user_id' => $customer->id,
            'tour_id' => $tour->id,
            'number_of_people' => 2,
            'total_price' => 5600000,
            'customer_name' => 'Tran Thi C',
            'customer_phone' => '0901000002',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($owner)->post(route('partner.tours.bookings.update-status', $booking->id), [
            'status' => 'confirmed',
        ]);

        $response->assertRedirect(route('partner.tours.index'));
        $this->assertSame('confirmed', $booking->fresh()->status);
        $this->assertSame(8, $tour->fresh()->available_seats);
    }

    public function test_user_can_book_room_and_inventory_is_decremented(): void
    {
        $customer = User::factory()->create();
        $hotelOwner = User::factory()->hotelOwner()->create();
        $room = Room::create([
            'owner_id' => $hotelOwner->id,
            'title' => 'Phong Deluxe',
            'hotel_name' => 'Blue Ocean Hotel',
            'location' => 'Da Nang',
            'description' => 'Phong test.',
            'price_per_night' => 1200000,
            'guest_capacity' => 2,
            'available_rooms' => 3,
            'status' => 'active',
        ]);

        $response = $this->actingAs($customer)
            ->from(route('rooms.show', $room->id))
            ->post(route('room-bookings.store', $room->id), [
                'customer_name' => 'Pham Thi D',
                'customer_phone' => '0901000003',
                'number_of_guests' => 2,
                'number_of_rooms' => 1,
                'check_in_date' => now()->addDays(3)->toDateString(),
                'check_out_date' => now()->addDays(5)->toDateString(),
            ]);

        $response->assertRedirect(route('bookings.index'));
        $this->assertDatabaseCount('room_bookings', 1);
        $this->assertSame(2, $room->fresh()->available_rooms);
    }

    public function test_cancelling_room_booking_restores_available_rooms(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
            'role' => User::ROLE_ADMIN,
        ]);
        $customer = User::factory()->create();
        $hotelOwner = User::factory()->hotelOwner()->create();
        $room = Room::create([
            'owner_id' => $hotelOwner->id,
            'title' => 'Phong Suite',
            'hotel_name' => 'Blue Ocean Hotel',
            'location' => 'Da Nang',
            'description' => 'Phong test.',
            'price_per_night' => 1500000,
            'guest_capacity' => 4,
            'available_rooms' => 1,
            'status' => 'active',
        ]);
        $booking = RoomBooking::create([
            'user_id' => $customer->id,
            'room_id' => $room->id,
            'customer_name' => 'Vu Thi E',
            'customer_phone' => '0901000004',
            'number_of_guests' => 4,
            'number_of_rooms' => 2,
            'check_in_date' => now()->addDays(5)->toDateString(),
            'check_out_date' => now()->addDays(8)->toDateString(),
            'total_nights' => 3,
            'total_price' => 9000000,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.room-bookings.update-status', $booking->id), [
            'status' => 'cancelled',
        ]);

        $response->assertRedirect(route('home', ['admin_active_tab' => 'don-dat']) . '#quan-tri-noi-bo');
        $this->assertSame('cancelled', $booking->fresh()->status);
        $this->assertSame(3, $room->fresh()->available_rooms);
    }

    public function test_hotel_owner_can_manage_booking_for_their_own_room(): void
    {
        $hotelOwner = User::factory()->hotelOwner()->create();
        $customer = User::factory()->create();
        $room = Room::create([
            'owner_id' => $hotelOwner->id,
            'title' => 'Phong Riverside',
            'hotel_name' => 'Hoi An Riverside',
            'location' => 'Hoi An',
            'description' => 'Phong test.',
            'price_per_night' => 1100000,
            'guest_capacity' => 2,
            'available_rooms' => 2,
            'status' => 'active',
        ]);
        $booking = RoomBooking::create([
            'user_id' => $customer->id,
            'room_id' => $room->id,
            'customer_name' => 'Do Thi F',
            'customer_phone' => '0901000005',
            'number_of_guests' => 2,
            'number_of_rooms' => 1,
            'check_in_date' => now()->addDays(4)->toDateString(),
            'check_out_date' => now()->addDays(6)->toDateString(),
            'total_nights' => 2,
            'total_price' => 2200000,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($hotelOwner)->post(route('partner.rooms.bookings.update-status', $booking->id), [
            'status' => 'confirmed',
        ]);

        $response->assertRedirect(route('partner.rooms.index'));
        $this->assertSame('confirmed', $booking->fresh()->status);
        $this->assertSame(2, $room->fresh()->available_rooms);
    }
}
