@extends('layouts.site')

@section('title', 'Hồ sơ cá nhân - TourBooking')

@section('hero')
    <div class="hero-panel p-4 p-lg-5">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <span class="badge badge-soft mb-3 px-3 py-2">Quản lý tài khoản</span>
                <h1 class="display-6 fw-bold mb-2">{{ auth()->user()->name }}</h1>
                <p class="lead text-white-50 mb-3">{{ auth()->user()->email }}</p>
                <div class="d-flex flex-wrap gap-3">
                    <span class="badge bg-success px-3 py-2">Tài khoản đang hoạt động</span>
                    <span class="badge bg-light text-primary px-3 py-2">
                        Tham gia từ {{ auth()->user()->created_at->format('d/m/Y') }}
                    </span>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="surface-card p-4">
                    <h2 class="h5 section-title mb-3 text-dark">Trạng thái tài khoản</h2>
                    <p class="mb-0 text-success fw-semibold">
                        <i class="fa-solid fa-circle-check me-2"></i>Tài khoản đã sẵn sàng đầy đủ tính năng.
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="surface-card p-4 h-100">
                <h2 class="section-title h4 mb-3">Thông tin nhanh</h2>
                <div class="d-grid gap-3">
                    <div class="border rounded-4 p-3">
                        <div class="text-primary fw-semibold mb-1">Ngày tham gia</div>
                        <div>{{ auth()->user()->created_at->format('d/m/Y') }}</div>
                    </div>
                    <div class="border rounded-4 p-3">
                        <div class="text-primary fw-semibold mb-1">Cập nhật gần nhất</div>
                        <div>{{ auth()->user()->updated_at->format('d/m/Y H:i') }}</div>
                    </div>
                    <div class="border rounded-4 p-3">
                        <div class="text-primary fw-semibold mb-1">Email hiện tại</div>
                        <div>{{ auth()->user()->email }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="surface-card p-4 p-lg-5">
                <div class="mb-4">
                    <h2 class="section-title h3 mb-1">Cập nhật hồ sơ</h2>
                    <p class="muted-copy mb-0">Thông tin này được dùng khi đặt tour và nhận thông báo.</p>
                </div>

                <form method="POST" action="{{ route('profile.update') }}" class="row g-4">
                    @csrf
                    @method('PUT')

                    <div class="col-md-6">
                        <label for="name" class="form-label fw-semibold">Họ và tên</label>
                        <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', auth()->user()->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="email" class="form-label fw-semibold">Email</label>
                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email', auth()->user()->email) }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <hr class="my-2">
                        <div class="mb-2">
                            <h3 class="h5 section-title mb-1">Đổi mật khẩu</h3>
                            <p class="muted-copy mb-0">Để trống nếu bạn chỉ muốn cập nhật tên hoặc email.</p>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label for="current_password" class="form-label fw-semibold">Mật khẩu hiện tại</label>
                        <input id="current_password" type="password" class="form-control @error('current_password') is-invalid @enderror" name="current_password" autocomplete="current-password">
                        @error('current_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="password" class="form-label fw-semibold">Mật khẩu mới</label>
                        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" autocomplete="new-password">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="password_confirmation" class="form-label fw-semibold">Xác nhận mật khẩu mới</label>
                        <input id="password_confirmation" type="password" class="form-control" name="password_confirmation" autocomplete="new-password">
                    </div>

                    <div class="col-12 d-flex justify-content-end">
                        <button type="submit" class="btn btn-brand px-4 py-2">Lưu thay đổi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
