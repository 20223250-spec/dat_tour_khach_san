@extends('bo_cuc.trang_web')

@section('title', 'Quên mật khẩu - TourBooking')

@section('hero')
    <div class="hero-panel p-4 p-lg-5">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <h1 class="display-6 fw-bold mb-0">Quên mật khẩu</h1>
            </div>
            <div class="col-lg-5">
                <div class="surface-card p-4">
                    <h2 class="h4 section-title mb-3 text-dark">Gửi yêu cầu đặt lại</h2>

                    <form method="POST" action="{{ route('password.email') }}" class="d-grid gap-3">
                        @csrf
                        <div>
                            <label for="email" class="form-label fw-semibold">Email tài khoản</label>
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autofocus>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-brand py-2">Gửi liên kết đặt lại</button>
                    </form>

                    <div class="text-center mt-3">
                        <a href="{{ route('login') }}" class="text-decoration-none">Quay lại đăng nhập</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection



