<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'TourBooking')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&family=Sora:wght@600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --site-bg: #f4f7ff;
            --site-ink: #112240;
            --site-muted: #60708a;
            --site-card: #ffffff;
            --site-line: rgba(18, 34, 64, 0.09);
            --site-brand: #0f4ad6;
            --site-brand-2: #14a4c7;
            --site-dark: #06122b;
            --site-radius-xl: 26px;
            --site-radius-lg: 18px;
            --site-shadow: 0 20px 48px rgba(8, 24, 56, 0.14);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Be Vietnam Pro", "Segoe UI", sans-serif;
            color: var(--site-ink);
            background:
                radial-gradient(circle at 12% 0%, rgba(20, 164, 199, 0.18), transparent 25%),
                radial-gradient(circle at 90% 8%, rgba(15, 74, 214, 0.16), transparent 28%),
                linear-gradient(180deg, #f8fbff 0%, var(--site-bg) 100%);
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        .section-title,
        .navbar-brand {
            font-family: "Sora", "Be Vietnam Pro", sans-serif;
            letter-spacing: -0.02em;
        }

        .navbar-shell {
            background: linear-gradient(110deg, #06122b 0%, #0d2f76 55%, #14a4c7 100%);
            box-shadow: 0 14px 36px rgba(6, 18, 43, 0.28);
        }

        .navbar-shell .navbar-brand {
            font-weight: 700;
        }

        .navbar-shell .nav-link {
            color: rgba(255, 255, 255, 0.84);
            font-weight: 500;
        }

        .navbar-shell .nav-link:hover,
        .navbar-shell .nav-link.active {
            color: #fff;
        }

        .page-hero {
            padding: 2.8rem 0 1.5rem;
        }

        .hero-panel {
            position: relative;
            overflow: hidden;
            border-radius: var(--site-radius-xl);
            padding: 2rem;
            color: #fff;
            background: linear-gradient(140deg, #06122b 0%, #0f4ad6 64%, #14a4c7 100%);
            box-shadow: var(--site-shadow);
        }

        .hero-panel::after {
            content: "";
            position: absolute;
            inset: auto -18% -30% auto;
            width: 260px;
            height: 260px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.26), transparent 72%);
            pointer-events: none;
        }

        .surface-card {
            border-radius: var(--site-radius-xl);
            border: 1px solid var(--site-line);
            background: rgba(255, 255, 255, 0.95);
            box-shadow: var(--site-shadow);
            color: var(--site-ink);
        }

        .surface-card .form-label {
            color: #2d3f5d;
        }

        .surface-card .form-control::placeholder {
            color: #8b9cb2;
            opacity: 1;
        }

        .section-title {
            font-weight: 700;
        }

        .muted-copy {
            color: var(--site-muted);
        }

        .btn {
            border-radius: 12px;
            font-weight: 600;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        .btn-brand {
            border: none;
            color: #fff;
            background: linear-gradient(135deg, var(--site-brand) 0%, var(--site-brand-2) 100%);
            box-shadow: 0 12px 26px rgba(15, 74, 214, 0.26);
        }

        .btn-brand:hover,
        .btn-brand:focus {
            color: #fff;
            box-shadow: 0 15px 28px rgba(15, 74, 214, 0.32);
        }

        .btn-soft {
            color: var(--site-brand);
            border: 1px solid rgba(15, 74, 214, 0.18);
            background: rgba(15, 74, 214, 0.08);
        }

        .btn-soft:hover,
        .btn-soft:focus {
            color: #0a3dac;
            background: rgba(15, 74, 214, 0.14);
        }

        .btn-ghost {
            color: var(--site-ink);
            border: 1px solid var(--site-line);
            background: #fff;
        }

        .btn-ghost:hover,
        .btn-ghost:focus {
            color: var(--site-brand);
            border-color: rgba(15, 74, 214, 0.3);
        }

        .badge-soft {
            background: rgba(20, 164, 199, 0.12);
            color: #036f89;
            border: 1px solid rgba(20, 164, 199, 0.2);
        }

        .form-control,
        .form-select {
            border-radius: 12px;
            border: 1px solid rgba(17, 34, 64, 0.14);
            padding: 0.78rem 0.95rem;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: rgba(15, 74, 214, 0.45);
            box-shadow: 0 0 0 0.2rem rgba(15, 74, 214, 0.15);
        }

        .icon-pill {
            width: 3rem;
            height: 3rem;
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(15, 74, 214, 0.12);
        }

        .site-main {
            padding-bottom: 1.5rem;
        }

        .footer-shell {
            margin-top: 4rem;
            background: var(--site-dark);
        }

        @media (max-width: 991.98px) {
            .page-hero {
                padding-top: 2rem;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
@php
    $currentUser = auth()->user();
    $hasVerifiedEmail = (bool) $currentUser;
    $unreadCount = $currentUser ? $currentUser->notifications()->unread()->count() : 0;
@endphp

<nav class="navbar navbar-expand-lg navbar-dark navbar-shell sticky-top">
    <div class="container py-2">
        <a class="navbar-brand" href="{{ route('home') }}">
            <i class="fa-solid fa-plane-departure me-2"></i>TourBooking
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Chuyển điều hướng">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto mb-3 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Trang chủ</a>
                </li>
                @if($currentUser && $currentUser->isAdmin() && $hasVerifiedEmail)
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.*') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">Quản trị</a>
                    </li>
                @endif
                @if($currentUser)
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('profile') ? 'active' : '' }}" href="{{ route('profile') }}">Hồ sơ</a>
                    </li>
                    @if($hasVerifiedEmail)
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('bookings.*') ? 'active' : '' }}" href="{{ route('bookings.index') }}">Đơn đặt</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('chat.*') ? 'active' : '' }}" href="{{ route('chat.index') }}">Chat hỗ trợ</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}" href="{{ route('notifications.index') }}">Thông báo</a>
                        </li>
                    @endif
                @endif
            </ul>

            <div class="d-flex flex-column flex-lg-row gap-2 align-items-lg-center">
                @auth
                    @if($hasVerifiedEmail)
                        <a href="{{ route('notifications.index') }}" class="btn btn-outline-light position-relative">
                            <i class="fa-regular fa-bell"></i>
                            @if($unreadCount > 0)
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                                </span>
                            @endif
                        </a>
                    @endif

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-light text-primary fw-semibold">Đăng xuất</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline-light">Đăng nhập</a>
                    <a href="{{ route('register') }}" class="btn btn-light text-primary fw-semibold">Đăng ký</a>
                @endauth
            </div>
        </div>
    </div>
</nav>

@hasSection('hero')
    <section class="page-hero">
        <div class="container">
            @yield('hero')
        </div>
    </section>
@endif

<main class="site-main">
    <div class="container">
        @include('partials.flash')
        @yield('content')
    </div>
</main>

<footer class="footer-shell text-white py-4">
    <div class="container d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <p class="mb-0">TourBooking giúp bạn đặt tour nhanh, rõ ràng và dễ theo dõi.</p>
        <p class="mb-0 text-white-50">&copy; {{ now()->year }} TourBooking</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>



