@extends('layouts.site')

@section('title', 'Đặt lại mật khẩu - TourBooking')

@section('hero')
    <div class="hero-panel p-4 p-lg-5">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <span class="badge badge-soft mb-3 px-3 py-2">Bảo mật tài khoản</span>
                <h1 class="display-6 fw-bold mb-3">Thiết lập mật khẩu mới để tiếp tục sử dụng tài khoản.</h1>
                <p class="lead text-white-50 mb-0">Chọn mật khẩu dễ nhớ với bạn nhưng đủ mạnh để bảo vệ dữ liệu.</p>
            </div>
            <div class="col-lg-5">
                <div class="surface-card p-4">
                    <h2 class="h4 section-title mb-3 text-dark">Đặt lại mật khẩu</h2>

                    <form method="POST" action="{{ route('password.update') }}" class="d-grid gap-3">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token ?? request()->route('token') }}">

                        <div>
                            <label for="email" class="form-label fw-semibold">Email</label>
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email', request('email')) }}" required autofocus>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label for="password" class="form-label fw-semibold">Mật khẩu mới</label>
                            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label for="password_confirmation" class="form-label fw-semibold">Xác nhận mật khẩu mới</label>
                            <input id="password_confirmation" type="password" class="form-control" name="password_confirmation" required>
                        </div>

                        <button type="submit" class="btn btn-brand py-2">Lưu mật khẩu mới</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection


