<?php

use App\Models\Booking;
use App\Models\Notification;
use App\Models\Tour;
use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('bookings:sync-status', function () {
    $today = now()->toDateString();

    $completedCount = Booking::query()
        ->where('status', 'confirmed')
        ->whereHas('tour', function ($query) use ($today) {
            $query->whereDate('start_date', '<=', $today);
        })
        ->update([
            'status' => 'completed',
            'updated_at' => now(),
        ]);

    $pendingBookingIds = Booking::query()
        ->where('status', 'pending')
        ->whereHas('tour', function ($query) use ($today) {
            $query->whereDate('start_date', '<=', $today);
        })
        ->pluck('id');

    $cancelledCount = 0;
    $refundedSeatCount = 0;

    foreach ($pendingBookingIds as $bookingId) {
        DB::transaction(function () use ($bookingId, $today, &$cancelledCount, &$refundedSeatCount) {
            $booking = Booking::query()->lockForUpdate()->find($bookingId);

            if (! $booking || $booking->status !== 'pending') {
                return;
            }

            $tour = Tour::query()->lockForUpdate()->find($booking->tour_id);

            if (! $tour || $tour->start_date > $today) {
                return;
            }

            $booking->status = 'cancelled';
            $booking->save();

            $tour->increment('available_seats', $booking->number_of_people);

            $booking->setRelation('tour', $tour);
            Notification::createBookingAutoCancelled($booking->user_id, $booking);

            $cancelledCount++;
            $refundedSeatCount += (int) $booking->number_of_people;
        });
    }

    $this->info("Bookings synced. completed={$completedCount}, auto_cancelled={$cancelledCount}, refunded_seats={$refundedSeatCount}");
})->purpose('Auto sync booking status when departure date is reached');

Artisan::command('bookings:send-reminders', function () {
    $totalNotifications = 0;

    foreach ([3, 1] as $daysLeft) {
        $targetDate = now()->addDays($daysLeft)->toDateString();

        $bookings = Booking::query()
            ->with('tour')
            ->where('status', 'confirmed')
            ->whereHas('tour', function ($query) use ($targetDate) {
                $query->whereDate('start_date', '=', $targetDate);
            })
            ->get();

        foreach ($bookings as $booking) {
            $alreadySent = Notification::query()
                ->where('user_id', $booking->user_id)
                ->where('type', 'departure_reminder')
                ->where('data->booking_id', $booking->id)
                ->where('data->reminder_days', $daysLeft)
                ->exists();

            if ($alreadySent) {
                continue;
            }

            Notification::createDepartureReminder($booking->user_id, $booking, $daysLeft);
            $totalNotifications++;
        }
    }

    $this->info("Departure reminders sent={$totalNotifications}");
})->purpose('Send automatic web notifications before tour departure');

Artisan::command('notifications:demo-admin {--email=} {--count=10}', function () {
    $count = max((int) $this->option('count'), 1);
    $email = $this->option('email');

    $adminsQuery = User::query()->where('is_admin', true);

    if (! empty($email)) {
        $adminsQuery->where('email', $email);
    }

    $admins = $adminsQuery->get();

    if ($admins->isEmpty()) {
        $this->error('Không tìm thấy tài khoản admin phù hợp để tạo thông báo demo.');

        return;
    }

    $templates = [
        [
            'type' => 'departure_reminder',
            'title' => 'Nhắc lịch hệ thống',
            'message' => 'Bạn có 3 tour khởi hành trong tuần này. Vui lòng kiểm tra điều phối.',
            'data' => ['demo' => true, 'channel' => 'admin'],
        ],
        [
            'type' => 'booking_received',
            'title' => 'Đơn đặt mới cần kiểm tra',
            'message' => 'Có đơn đặt mới vừa phát sinh và đang chờ admin xác nhận.',
            'data' => ['demo' => true, 'priority' => 'normal'],
        ],
        [
            'type' => 'booking_cancelled',
            'title' => 'Cảnh báo hủy đơn',
            'message' => 'Một số đơn pending đã bị tự động hủy do quá ngày khởi hành.',
            'data' => ['demo' => true, 'source' => 'auto_sync'],
        ],
        [
            'type' => 'tour_updated',
            'title' => 'Cập nhật nội dung tour',
            'message' => 'Có tour vừa được chỉnh sửa thông tin giá hoặc lịch khởi hành.',
            'data' => ['demo' => true, 'action' => 'review'],
        ],
        [
            'type' => 'admin_demo',
            'title' => 'Thông báo demo cho admin',
            'message' => 'Đây là thông báo mẫu để kiểm tra giao diện và bộ lọc thông báo.',
            'data' => ['demo' => true, 'tag' => 'ui-test'],
        ],
    ];

    $created = 0;

    foreach ($admins as $admin) {
        for ($i = 0; $i < $count; $i++) {
            $template = $templates[$i % count($templates)];
            $createdAt = now()->subMinutes(($i + 1) * 7);

            Notification::create([
                'user_id' => $admin->id,
                'type' => $template['type'],
                'title' => $template['title'],
                'message' => $template['message'],
                'data' => $template['data'],
                'is_read' => $i % 3 === 0,
                'read_at' => $i % 3 === 0 ? $createdAt : null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            $created++;
        }
    }

    $this->info("Đã tạo {$created} thông báo demo cho {$admins->count()} admin.");
})->purpose('Generate demo web notifications for admin users');

Artisan::command('notifications:demo-user {user_id} {--count=8}', function () {
    $userId = (int) $this->argument('user_id');
    $count = max((int) $this->option('count'), 1);

    $user = User::query()->find($userId);

    if (! $user) {
        $this->error("Không tìm thấy người dùng có id={$userId}.");

        return;
    }

    $templates = [
        [
            'type' => 'departure_reminder',
            'title' => 'Nhắc lịch khởi hành',
            'message' => 'Tour của bạn sắp đến ngày khởi hành. Vui lòng chuẩn bị hành lý và giấy tờ cần thiết.',
            'data' => ['demo' => true, 'channel' => 'web', 'group' => 'reminder'],
        ],
        [
            'type' => 'booking_confirmed',
            'title' => 'Đơn đặt đã xác nhận',
            'message' => 'Đơn đặt tour của bạn đã được xác nhận thành công. Cảm ơn bạn đã sử dụng dịch vụ.',
            'data' => ['demo' => true, 'channel' => 'web', 'group' => 'booking'],
        ],
        [
            'type' => 'booking_received',
            'title' => 'Đã nhận yêu cầu đặt tour',
            'message' => 'Hệ thống đã ghi nhận đơn đặt tour của bạn và đang chờ xử lý.',
            'data' => ['demo' => true, 'channel' => 'web', 'group' => 'booking'],
        ],
        [
            'type' => 'tour_updated',
            'title' => 'Tour có cập nhật mới',
            'message' => 'Tour bạn quan tâm vừa được cập nhật lịch trình và thông tin dịch vụ.',
            'data' => ['demo' => true, 'channel' => 'web', 'group' => 'tour'],
        ],
        [
            'type' => 'booking_cancelled',
            'title' => 'Thông báo hủy đơn',
            'message' => 'Đơn đặt tour đã được hủy theo điều kiện vận hành. Vui lòng kiểm tra chi tiết.',
            'data' => ['demo' => true, 'channel' => 'web', 'group' => 'booking'],
        ],
    ];

    $created = 0;

    for ($i = 0; $i < $count; $i++) {
        $template = $templates[$i % count($templates)];
        $createdAt = now()->subMinutes(($i + 1) * 6);

        Notification::create([
            'user_id' => $user->id,
            'type' => $template['type'],
            'title' => $template['title'],
            'message' => $template['message'],
            'data' => array_merge($template['data'], ['demo_user_id' => $user->id]),
            'is_read' => $i % 4 === 0,
            'read_at' => $i % 4 === 0 ? $createdAt : null,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);

        $created++;
    }

    $this->info("Đã tạo {$created} thông báo demo cho user {$user->name} (id={$user->id}).");
})->purpose('Generate demo web notifications for a specific user');

Schedule::command('bookings:sync-status')->hourly();
Schedule::command('bookings:send-reminders')->dailyAt('08:00');
