@extends('bo_cuc.trang_web')

@section('title', 'Đặt lại mật khẩu - TourBooking')

@section('hero')
    <div class="hero-panel p-4 p-lg-5">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <h1 class="display-6 fw-bold mb-0">Đặt lại mật khẩu</h1>
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



