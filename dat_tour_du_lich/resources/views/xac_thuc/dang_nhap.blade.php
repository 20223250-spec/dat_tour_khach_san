@extends('bo_cuc.trang_web')

@section('title', 'Đăng nhập - TourBooking')

@section('hero')
    <div class="hero-panel p-4 p-lg-5">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <h1 class="display-6 fw-bold mb-0">Đăng nhập</h1>
            </div>
            <div class="col-lg-5">
                <div class="surface-card p-4">
                    <h2 class="h4 section-title mb-3 text-dark">Đăng nhập</h2>

                    @if ($errors->any())
                        <div class="alert alert-danger rounded-4 border-0">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login.perform') }}" class="d-grid gap-3">
                        @csrf
                        <div>
                            <label for="email" class="form-label fw-semibold">Email</label>
                            <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required autofocus>
                        </div>
                        <div>
                            <label for="password" class="form-label fw-semibold">Mật khẩu</label>
                            <input id="password" type="password" class="form-control" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-brand py-2">Đăng nhập</button>
                    </form>

                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <a href="{{ route('password.request') }}" class="text-decoration-none">Quên mật khẩu?</a>
                        <a href="{{ route('register') }}" class="text-decoration-none">Tạo tài khoản mới</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

