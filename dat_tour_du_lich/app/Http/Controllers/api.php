<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TourController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\NotificationController;

// Authentication
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Lấy danh sách và chi tiết Tour
Route::get('/tours', [TourController::class, 'index']);
Route::get('/tours/{id}', [TourController::class, 'show']);

// Các API yêu cầu người dùng phải đăng nhập
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    
    Route::post('/tours/{id}/book', [BookingController::class, 'store']); // Đặt tour
    Route::get('/my-bookings', [BookingController::class, 'index']); // Xem lịch sử đặt
    Route::post('/tours/{id}/review', [ReviewController::class, 'store']); // Đánh giá tour
    Route::get('/tours/{id}/reviews', [ReviewController::class, 'index']); // Xem đánh giá tour

    // Notification APIs
    Route::get('/notifications/unread-count', [NotificationController::class, 'getUnreadCount']);
    Route::get('/notifications', [NotificationController::class, 'getNotifications']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
});
