<?php

namespace Tests\Feature;

use App\Models\Tour;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    public function test_registration_redirects_to_verification_and_sends_email(): void
    {
        Notification::fake();

        $response = $this->post(route('register.store'), [
            'name' => 'Nguyen Van A',
            'email' => 'nguyenvana@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::first();

        $response->assertRedirect(route('verification.notice'));
        $this->assertAuthenticatedAs($user);
        $this->assertNull($user->fresh()->email_verified_at);
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_unverified_user_is_redirected_to_verification_after_login(): void
    {
        $user = User::factory()->unverified()->create([
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post(route('login.perform'), [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('verification.notice'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_updating_email_requires_reverification(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'old@example.com',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'Nguyen Van B',
            'email' => 'new@example.com',
        ]);

        $response->assertRedirect(route('verification.notice'));

        $user->refresh();

        $this->assertSame('Nguyen Van B', $user->name);
        $this->assertSame('new@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_guest_can_view_tour_detail_page(): void
    {
        $tour = Tour::create([
            'name' => 'Hanh trinh Da Lat',
            'description' => 'Tour tham quan thanh pho Da Lat trong 3 ngay 2 dem.',
            'destination' => 'Da Lat',
            'price' => 3500000,
            'duration_days' => 3,
            'available_seats' => 12,
            'start_date' => now()->addDays(10)->toDateString(),
        ]);

        $response = $this->get(route('tours.show', $tour->id));

        $response->assertOk();
        $response->assertSee('Hanh trinh Da Lat');
    }

    public function test_unverified_user_can_logout_from_verification_flow(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect(route('home'));
        $this->assertGuest();
    }
}
