<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin - TourBooking')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&family=Sora:wght@600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --admin-bg: #f2f6ff;
            --admin-ink: #12233f;
            --admin-muted: #64748b;
            --admin-brand: #0f4ad6;
            --admin-brand-2: #17a2c5;
            --admin-dark: #071935;
            --admin-line: rgba(18, 35, 63, 0.1);
            --admin-card: #ffffff;
            --admin-radius-xl: 22px;
            --admin-radius-lg: 16px;
            --admin-shadow: 0 18px 40px rgba(7, 25, 53, 0.14);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Be Vietnam Pro", "Segoe UI", sans-serif;
            color: var(--admin-ink);
            background:
                radial-gradient(circle at 14% 3%, rgba(23, 162, 197, 0.17), transparent 26%),
                radial-gradient(circle at 88% 5%, rgba(15, 74, 214, 0.14), transparent 28%),
                linear-gradient(180deg, #f8fbff 0%, var(--admin-bg) 100%);
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        .topbar-brand {
            font-family: "Sora", "Be Vietnam Pro", sans-serif;
            letter-spacing: -0.02em;
        }

        .admin-topbar {
            background: linear-gradient(110deg, #06122b 0%, #0c2e72 58%, #17a2c5 100%);
            box-shadow: 0 14px 32px rgba(6, 18, 43, 0.28);
        }

        .topbar-brand {
            color: #fff;
            font-weight: 700;
            text-decoration: none;
        }

        .topbar-brand:hover {
            color: #fff;
        }

        .admin-shell {
            min-height: calc(100vh - 74px);
        }

        .admin-sidebar {
            background: rgba(255, 255, 255, 0.94);
            border-right: 1px solid var(--admin-line);
            padding: 1.35rem 1rem;
        }

        .side-title {
            font-size: 0.78rem;
            color: var(--admin-muted);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin: 1.1rem 0 0.5rem;
            font-weight: 700;
        }

        .side-link {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            text-decoration: none;
            color: #27415f;
            font-weight: 600;
            border-radius: 12px;
            padding: 0.68rem 0.8rem;
            margin-bottom: 0.28rem;
            transition: all 0.2s ease;
        }

        .side-link:hover {
            background: rgba(15, 74, 214, 0.08);
            color: var(--admin-brand);
        }

        .side-link.active {
            color: #fff;
            background: linear-gradient(135deg, var(--admin-brand) 0%, var(--admin-brand-2) 100%);
            box-shadow: 0 10px 20px rgba(15, 74, 214, 0.24);
        }

        .admin-main {
            padding: 1.6rem;
        }

        .panel-card {
            border-radius: var(--admin-radius-xl);
            border: 1px solid var(--admin-line);
            background: var(--admin-card);
            box-shadow: var(--admin-shadow);
        }

        .panel-header {
            border-radius: var(--admin-radius-xl);
            color: #fff;
            padding: 1.6rem;
            margin-bottom: 1.2rem;
            background: linear-gradient(140deg, #071935 0%, #0f4ad6 65%, #17a2c5 100%);
            box-shadow: var(--admin-shadow);
        }

        .quick-grid {
            display: grid;
            gap: 0.85rem;
            grid-template-columns: repeat(auto-fit, minmax(165px, 1fr));
        }

        .quick-item {
            border-radius: var(--admin-radius-lg);
            border: 1px solid var(--admin-line);
            background: #fff;
            padding: 0.85rem 1rem;
        }

        .quick-item .label {
            font-size: 0.8rem;
            color: var(--admin-muted);
            margin-bottom: 0.2rem;
        }

        .quick-item .value {
            font-size: 1.25rem;
            font-weight: 700;
        }

        .btn {
            border-radius: 11px;
            font-weight: 600;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        .btn-brand {
            border: none;
            color: #fff;
            background: linear-gradient(135deg, var(--admin-brand) 0%, var(--admin-brand-2) 100%);
            box-shadow: 0 10px 22px rgba(15, 74, 214, 0.24);
        }

        .btn-brand:hover,
        .btn-brand:focus {
            color: #fff;
        }

        .btn-soft {
            border: 1px solid rgba(15, 74, 214, 0.24);
            color: var(--admin-brand);
            background: rgba(15, 74, 214, 0.08);
        }

        .btn-soft:hover,
        .btn-soft:focus {
            color: #0b3ea9;
            background: rgba(15, 74, 214, 0.14);
        }

        .btn-ghost {
            border: 1px solid var(--admin-line);
            color: var(--admin-ink);
            background: #fff;
        }

        .btn-ghost:hover,
        .btn-ghost:focus {
            color: var(--admin-brand);
            border-color: rgba(15, 74, 214, 0.34);
        }

        .table-clean th {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            border-top: 0;
            border-bottom: 1px solid rgba(18, 35, 63, 0.09);
            color: #4f627b;
            background: #f8fbff;
            padding: 0.95rem 0.8rem;
        }

        .table-clean td {
            border-bottom: 1px solid rgba(18, 35, 63, 0.08);
            vertical-align: middle;
            padding: 0.95rem 0.8rem;
        }

        .table-clean tbody tr:hover {
            background: rgba(15, 74, 214, 0.04);
        }

        .chip {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 0.35rem 0.75rem;
            font-size: 0.78rem;
            font-weight: 700;
        }

        .chip-pending {
            color: #8a5a00;
            background: #ffefc4;
        }

        .chip-confirmed {
            color: #0f7b6c;
            background: #d6f6ef;
        }

        .chip-cancelled {
            color: #a22525;
            background: #ffdede;
        }

        .chip-completed {
            color: #1259a9;
            background: #dce9ff;
        }

        .form-control,
        .form-select {
            border-radius: 12px;
            border: 1px solid rgba(18, 35, 63, 0.16);
            padding: 0.72rem 0.9rem;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: rgba(15, 74, 214, 0.45);
            box-shadow: 0 0 0 0.2rem rgba(15, 74, 214, 0.15);
        }

        .page-note {
            color: var(--admin-muted);
        }

        @media (max-width: 991.98px) {
            .admin-sidebar {
                border-right: 0;
                border-bottom: 1px solid var(--admin-line);
            }
        }
    </style>
    @stack('styles')
</head>
<body>
<nav class="admin-topbar navbar navbar-expand-lg navbar-dark sticky-top">
    <div class="container-fluid px-3 px-lg-4">
        <a href="{{ route('admin.dashboard') }}" class="topbar-brand">
            <i class="fa-solid fa-crown me-2"></i>Admin TourBooking
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminTopNav" aria-controls="adminTopNav" aria-expanded="false" aria-label="Chuyển điều hướng">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="adminTopNav">
            <div class="ms-auto d-flex flex-column flex-lg-row gap-2 mt-3 mt-lg-0">
                <a href="{{ route('home') }}" class="btn btn-outline-light btn-sm">
                    <i class="fa-solid fa-house me-1"></i>Trang chủ
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-light text-primary btn-sm">
                        <i class="fa-solid fa-right-from-bracket me-1"></i>Đăng xuất
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>

<div class="container-fluid admin-shell">
    <div class="row g-0">
        <aside class="col-12 col-lg-2 admin-sidebar">
            <div class="side-title mt-0">Quản lý</div>
            <a class="side-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                <i class="fa-solid fa-gauge"></i>
                <span>Tổng quan</span>
            </a>
            <a class="side-link {{ request()->routeIs('admin.bookings.*') ? 'active' : '' }}" href="{{ route('admin.bookings.index') }}">
                <i class="fa-solid fa-receipt"></i>
                <span>Đơn đặt</span>
            </a>
            <a class="side-link {{ request()->routeIs('admin.tours.*') ? 'active' : '' }}" href="{{ route('admin.tours.index') }}">
                <i class="fa-solid fa-route"></i>
                <span>Tour</span>
            </a>

            <div class="side-title">Báo cáo</div>
            <a class="side-link {{ request()->routeIs('admin.reports.dashboard') ? 'active' : '' }}" href="{{ route('admin.reports.dashboard') }}">
                <i class="fa-solid fa-chart-pie"></i>
                <span>Báo cáo tổng</span>
            </a>
            <a class="side-link {{ request()->routeIs('admin.reports.bookings') ? 'active' : '' }}" href="{{ route('admin.reports.bookings') }}">
                <i class="fa-solid fa-chart-column"></i>
                <span>Báo cáo đơn đặt</span>
            </a>
            <a class="side-link {{ request()->routeIs('admin.reports.tours') ? 'active' : '' }}" href="{{ route('admin.reports.tours') }}">
                <i class="fa-solid fa-chart-line"></i>
                <span>Báo cáo tour</span>
            </a>
        </aside>

        <main class="col-12 col-lg-10 admin-main">
            @include('partials.flash')
            @yield('content')
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>


