<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\RoomBookingController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\TourController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', [TourController::class, 'indexWeb'])->name('home');
Route::get('/tours/{id}', [TourController::class, 'showWeb'])->name('tours.show');
Route::get('/rooms/{id}', [RoomController::class, 'showWeb'])->name('rooms.show');

Route::middleware('guest')->group(function () {
    Route::view('/login', 'xac_thuc.dang_nhap')->name('login');
    Route::post('/login', [AuthController::class, 'loginWeb'])->name('login.perform');

    Route::view('/register', 'xac_thuc.dang_ky')->name('register');
    Route::post('/register', [AuthController::class, 'registerWeb'])->name('register.store');

    Route::view('/forgot-password', 'xac_thuc.quen_mat_khau')->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');

    Route::get('/reset-password/{token}', function (string $token) {
        return view('xac_thuc.dat_lai_mat_khau', [
            'token' => $token,
            'email' => request('email'),
        ]);
    })->name('password.reset');

    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');

    Route::post('/logout', function (Request $request) {
        auth()->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('home')
            ->with('success', 'Bạn đã đăng xuất thành công.');
    })->name('logout');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

    Route::middleware('manage.tours')->prefix('/partner/tours')->name('partner.tours.')->group(function () {
        Route::get('/', [TourController::class, 'partnerIndex'])->name('index');
        Route::get('/create', [TourController::class, 'partnerCreate'])->name('create');
        Route::post('/', [TourController::class, 'partnerStore'])->name('store');
        Route::post('/bookings/{id}/status', [BookingController::class, 'updateStatus'])->name('bookings.update-status');
        Route::get('/{id}/edit', [TourController::class, 'partnerEdit'])->name('edit');
        Route::put('/{id}', [TourController::class, 'partnerUpdate'])->name('update');
        Route::delete('/{id}', [TourController::class, 'partnerDestroy'])->name('destroy');
    });

    Route::middleware('manage.rooms')->prefix('/partner/rooms')->name('partner.rooms.')->group(function () {
        Route::get('/', [RoomController::class, 'partnerIndex'])->name('index');
        Route::get('/create', [RoomController::class, 'partnerCreate'])->name('create');
        Route::post('/', [RoomController::class, 'partnerStore'])->name('store');
        Route::post('/bookings/{id}/status', [RoomBookingController::class, 'updateStatus'])->name('bookings.update-status');
        Route::get('/{id}/edit', [RoomController::class, 'partnerEdit'])->name('edit');
        Route::put('/{id}', [RoomController::class, 'partnerUpdate'])->name('update');
        Route::delete('/{id}', [RoomController::class, 'partnerDestroy'])->name('destroy');
    });
});

Route::middleware('auth')->group(function () {
    Route::post('/tours/{id}/book', [BookingController::class, 'storeWeb'])->name('bookings.store');
    Route::post('/rooms/{id}/book', [RoomBookingController::class, 'storeWeb'])->name('room-bookings.store');
    Route::get('/my-bookings', [BookingController::class, 'indexWeb'])->name('bookings.index');
    Route::post('/tours/{id}/review', [ReviewController::class, 'storeWeb'])->name('reviews.store');

    Route::middleware('admin')->group(function () {
        Route::get('/admin', [TourController::class, 'adminDashboard'])->name('admin.dashboard');
        Route::get('/admin/bookings', [BookingController::class, 'adminIndex'])->name('admin.bookings.index');
        Route::post('/admin/bookings/{id}/status', [BookingController::class, 'updateStatus'])->name('admin.bookings.update-status');
        Route::post('/admin/room-bookings/{id}/status', [RoomBookingController::class, 'updateStatus'])->name('admin.room-bookings.update-status');
        Route::get('/admin/tours', [TourController::class, 'adminIndex'])->name('admin.tours.index');
        Route::get('/admin/tours/create', [TourController::class, 'create'])->name('admin.tours.create');
        Route::post('/admin/tours', [TourController::class, 'store'])->name('admin.tours.store');
        Route::get('/admin/tours/{id}/edit', [TourController::class, 'edit'])->name('admin.tours.edit');
        Route::put('/admin/tours/{id}', [TourController::class, 'update'])->name('admin.tours.update');
        Route::delete('/admin/tours/{id}', [TourController::class, 'destroy'])->name('admin.tours.destroy');
        Route::get('/admin/rooms', [RoomController::class, 'adminIndex'])->name('admin.rooms.index');
        Route::get('/admin/rooms/create', [RoomController::class, 'create'])->name('admin.rooms.create');
        Route::post('/admin/rooms', [RoomController::class, 'store'])->name('admin.rooms.store');
        Route::get('/admin/rooms/{id}/edit', [RoomController::class, 'edit'])->name('admin.rooms.edit');
        Route::put('/admin/rooms/{id}', [RoomController::class, 'update'])->name('admin.rooms.update');
        Route::delete('/admin/rooms/{id}', [RoomController::class, 'destroy'])->name('admin.rooms.destroy');

        Route::get('/admin/reports', [ReportController::class, 'dashboard'])->name('admin.reports.dashboard');
        Route::get('/admin/reports/bookings', [ReportController::class, 'bookings'])->name('admin.reports.bookings');
        Route::get('/admin/reports/tours', [ReportController::class, 'tours'])->name('admin.reports.tours');
        Route::get('/admin/reports/export-bookings', [ReportController::class, 'exportBookings'])->name('admin.reports.export-bookings');
    });
});
