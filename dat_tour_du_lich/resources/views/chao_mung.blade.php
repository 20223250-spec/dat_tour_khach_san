@extends('bo_cuc.trang_web')

@section('title', 'Chào mừng - TourBooking')

@section('hero')
    <div class="hero-panel p-4 p-lg-5 text-center">
        <h1 class="display-5 fw-bold mb-4">TourBooking</h1>
        <a href="{{ route('home') }}" class="btn btn-light text-primary fw-semibold px-4">
            <i class="fa-solid fa-compass me-2"></i>Đến trang chủ
        </a>
    </div>
@endsection



