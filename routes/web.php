<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\TourController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', [TourController::class, 'indexWeb'])->name('home');
Route::get('/tours/{id}', [TourController::class, 'showWeb'])->name('tours.show');

Route::middleware('guest')->group(function () {
    Route::view('/login', 'auth.login')->name('login');
    Route::post('/login', [AuthController::class, 'loginWeb'])->name('login.perform');

    Route::view('/register', 'auth.register')->name('register');
    Route::post('/register', [AuthController::class, 'registerWeb'])->name('register.store');

    Route::view('/forgot-password', 'auth.forgot-password')->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');

    Route::get('/reset-password/{token}', function (string $token) {
        return view('auth.reset-password', [
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
        Auth::logout();

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
});

Route::middleware('auth')->group(function () {
    Route::post('/tours/{id}/book', [BookingController::class, 'storeWeb'])->name('bookings.store');
    Route::get('/my-bookings', [BookingController::class, 'indexWeb'])->name('bookings.index');
    Route::get('/chat', [ChatController::class, 'userIndex'])->name('chat.index');
    Route::post('/chat/send', [ChatController::class, 'userSend'])->name('chat.send');
    Route::post('/my-bookings/{id}/pay', [BookingController::class, 'simulatePaymentWeb'])->name('bookings.pay');
    Route::post('/my-bookings/{id}/pay-fail', [BookingController::class, 'simulatePaymentFailedWeb'])->name('bookings.pay-fail');
    Route::post('/tours/{id}/review', [ReviewController::class, 'storeWeb'])->name('reviews.store');

    Route::middleware('admin')->group(function () {
        Route::get('/admin', [TourController::class, 'adminDashboard'])->name('admin.dashboard');
        Route::get('/admin/chat', [ChatController::class, 'adminIndex'])->name('admin.chat.index');
        Route::get('/admin/chat/{userId}', [ChatController::class, 'adminShow'])->name('admin.chat.show');
        Route::post('/admin/chat/{userId}', [ChatController::class, 'adminSend'])->name('admin.chat.send');
        Route::get('/admin/bookings', [BookingController::class, 'adminIndex'])->name('admin.bookings.index');
        Route::post('/admin/bookings/{id}/status', [BookingController::class, 'updateStatus'])->name('admin.bookings.update-status');
        Route::post('/admin/bookings/{id}/payment-status', [BookingController::class, 'updatePaymentStatus'])->name('admin.bookings.update-payment-status');
        Route::get('/admin/tours', [TourController::class, 'adminIndex'])->name('admin.tours.index');
        Route::get('/admin/tours/create', [TourController::class, 'create'])->name('admin.tours.create');
        Route::post('/admin/tours', [TourController::class, 'store'])->name('admin.tours.store');
        Route::get('/admin/tours/{id}/edit', [TourController::class, 'edit'])->name('admin.tours.edit');
        Route::put('/admin/tours/{id}', [TourController::class, 'update'])->name('admin.tours.update');
        Route::delete('/admin/tours/{id}', [TourController::class, 'destroy'])->name('admin.tours.destroy');

        Route::get('/admin/reports', [ReportController::class, 'dashboard'])->name('admin.reports.dashboard');
        Route::get('/admin/reports/bookings', [ReportController::class, 'bookings'])->name('admin.reports.bookings');
        Route::get('/admin/reports/tours', [ReportController::class, 'tours'])->name('admin.reports.tours');
        Route::get('/admin/reports/export-bookings', [ReportController::class, 'exportBookings'])->name('admin.reports.export-bookings');
    });
});
