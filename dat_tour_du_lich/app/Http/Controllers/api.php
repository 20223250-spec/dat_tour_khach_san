<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TourController;
use App\Http\Controllers\BookingController;

// Lấy danh sách và chi tiết Tour
Route::get('/tours', [TourController::class, 'index']);
Route::get('/tours/{id}', [TourController::class, 'show']);

// Đặt tour
Route::post('/tours/{id}/book', [BookingController::class, 'store']);

// Xem lịch sử đặt tour
Route::get('/my-bookings', [BookingController::class, 'index']);