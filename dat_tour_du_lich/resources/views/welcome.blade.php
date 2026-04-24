@extends('layouts.site')

@section('title', 'Chào mừng - TourBooking')

@section('hero')
    <div class="hero-panel p-4 p-lg-5 text-center">
        <span class="badge badge-soft mb-3 px-3 py-2">Nền tảng đặt tour</span>
        <h1 class="display-5 fw-bold mb-3">Chào mừng đến với TourBooking</h1>
        <p class="lead text-white-50 mb-4">Đặt tour, theo dõi đơn và quản lý tài khoản trong một giao diện thống nhất.</p>
        <a href="{{ route('home') }}" class="btn btn-light text-primary fw-semibold px-4">
            <i class="fa-solid fa-compass me-2"></i>Đến trang chủ
        </a>
    </div>
@endsection


