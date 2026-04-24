@extends('layouts.site')

@section('title', 'Đăng ký - TourBooking')

@section('hero')
    <div class="hero-panel p-4 p-lg-5">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <span class="badge badge-soft mb-3 px-3 py-2">Tạo tài khoản mới</span>
                <h1 class="display-6 fw-bold mb-3">Đăng ký nhanh để đặt tour và lưu lịch sử hành trình.</h1>
                <p class="lead text-white-50 mb-0">Đăng ký xong là có thể sử dụng đầy đủ tính năng ngay.</p>
            </div>
            <div class="col-lg-5">
                <div class="surface-card p-4">
                    <h2 class="h4 section-title mb-3 text-dark">Đăng ký tài khoản</h2>

                    @if ($errors->any())
                        <div class="alert alert-danger rounded-4 border-0">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('register.store') }}" class="d-grid gap-3">
                        @csrf
                        <div>
                            <label for="name" class="form-label fw-semibold">Họ và tên</label>
                            <input id="name" type="text" class="form-control" name="name" value="{{ old('name') }}" required>
                        </div>
                        <div>
                            <label for="email" class="form-label fw-semibold">Email</label>
                            <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required>
                        </div>
                        <div>
                            <label for="password" class="form-label fw-semibold">Mật khẩu</label>
                            <input id="password" type="password" class="form-control" name="password" required>
                        </div>
                        <div>
                            <label for="password_confirmation" class="form-label fw-semibold">Xác nhận mật khẩu</label>
                            <input id="password_confirmation" type="password" class="form-control" name="password_confirmation" required>
                        </div>
                        <button type="submit" class="btn btn-brand py-2">Tạo tài khoản</button>
                    </form>

                    <div class="text-center mt-3">
                        <a href="{{ route('login') }}" class="text-decoration-none">Đã có tài khoản? Đăng nhập</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
