@extends('bo_cuc.trang_web')

@section('title', 'Trang chủ - TourBooking')

@php
    $isAdminViewingHome = request()->user()?->isAdmin();
    $showAdminWorkspaceOnly = $isAdminViewingHome && request()->filled('admin_active_tab');
    $initialAdminTab = old('admin_active_tab', request('admin_active_tab', 'tong-quan'));
    $adminContext = old('admin_context');
    $adminItemId = old('admin_item_id');
    $tourCreateHasErrors = $errors->any() && $adminContext === 'tour-create';
    $roomCreateHasErrors = $errors->any() && $adminContext === 'room-create';
    $adminHomeTabUrl = function (string $tab, array $query = []) {
        $filteredQuery = array_filter($query, fn ($value) => $value !== null && $value !== '');

        return route('home', array_merge($filteredQuery, ['admin_active_tab' => $tab])) . '#quan-tri-noi-bo';
    };
@endphp

@push('styles')
    <style>
        .search-panel {
            background: rgba(255, 255, 255, 0.14);
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 20px;
            padding: 1rem;
            backdrop-filter: blur(8px);
        }

        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
            gap: 0.9rem;
            margin-bottom: 1.1rem;
        }

        .metric-item {
            border-radius: 16px;
            border: 1px solid rgba(15, 74, 214, 0.16);
            background: rgba(255, 255, 255, 0.94);
            padding: 0.95rem 1rem;
        }

        .metric-item .label {
            color: var(--site-muted);
            font-size: 0.82rem;
        }

        .metric-item .value {
            font-size: 1.25rem;
            font-weight: 700;
            margin-top: 0.2rem;
        }

        .listing-card {
            overflow: hidden;
            border-radius: 22px;
            border: 1px solid rgba(18, 34, 64, 0.09);
            background: rgba(255, 255, 255, 0.95);
            box-shadow: var(--site-shadow);
            transition: transform 0.24s ease, box-shadow 0.24s ease;
        }

        .listing-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 26px 54px rgba(8, 24, 56, 0.18);
        }

        .listing-media {
            height: 220px;
            width: 100%;
            object-fit: cover;
            border-bottom: 1px solid rgba(18, 34, 64, 0.09);
        }

        .listing-placeholder {
            height: 220px;
            display: grid;
            place-items: center;
            color: #0f4ad6;
            background: linear-gradient(135deg, rgba(15, 74, 214, 0.12), rgba(20, 164, 199, 0.14));
        }

        .listing-meta {
            color: var(--site-muted);
            font-size: 0.93rem;
        }

        #quan-tri-noi-bo {
            scroll-margin-top: 110px;
        }

        .admin-inline-section {
            margin-top: 4rem;
        }

        .workspace-panel {
            display: none;
        }

        .workspace-panel.is-active {
            display: block;
        }

        .workspace-mobile-nav {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(145px, 1fr));
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .workspace-jump {
            display: flex;
            align-items: center;
            gap: 0.7rem;
            border-radius: 18px;
            border: 1px solid rgba(18, 35, 63, 0.08);
            background: #fff;
            color: #12233f;
            text-decoration: none;
            padding: 0.95rem 1rem;
            font-weight: 600;
        }

        .workspace-jump:hover {
            color: #0f4ad6;
            border-color: rgba(15, 74, 214, 0.2);
        }

        .workspace-jump.active {
            color: #fff;
            border-color: transparent;
            background: linear-gradient(135deg, #0f4ad6 0%, #17a2c5 100%);
            box-shadow: 0 10px 20px rgba(15, 74, 214, 0.18);
        }

        .workspace-jump i {
            width: 2.25rem;
            height: 2.25rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            background: rgba(15, 74, 214, 0.1);
        }

        .workspace-jump.active i {
            background: rgba(255, 255, 255, 0.18);
        }

        .section-stack {
            display: grid;
            gap: 1rem;
        }

        .workspace-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .shortcut-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1rem;
        }

        .shortcut-card {
            border-radius: 22px;
            border: 1px solid rgba(18, 35, 63, 0.1);
            background: #fff;
            box-shadow: 0 18px 40px rgba(7, 25, 53, 0.14);
            padding: 1.1rem 1.15rem;
        }

        .shortcut-card .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            color: #0f4ad6;
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 0.55rem;
        }

        .shortcut-card p {
            margin-bottom: 1rem;
            color: #64748b;
        }

        .embedded-card,
        .panel-card,
        .chart-panel {
            border-radius: 22px;
            border: 1px solid rgba(18, 35, 63, 0.1);
            background: #fff;
            box-shadow: 0 18px 40px rgba(7, 25, 53, 0.14);
        }

        .embedded-card,
        .chart-panel {
            padding: 1.25rem;
        }

        .embedded-card + .embedded-card {
            margin-top: 1rem;
        }

        .panel-card {
            padding: 1.5rem;
        }

        .panel-header {
            border-radius: 26px;
            color: #fff;
            padding: 1.6rem;
            margin-bottom: 1.2rem;
            background: linear-gradient(140deg, #071935 0%, #0f4ad6 65%, #17a2c5 100%);
            box-shadow: 0 18px 40px rgba(7, 25, 53, 0.14);
        }

        .quick-grid {
            display: grid;
            gap: 0.85rem;
            grid-template-columns: repeat(auto-fit, minmax(165px, 1fr));
        }

        .quick-item {
            border-radius: 16px;
            border: 1px solid rgba(18, 35, 63, 0.1);
            background: #fff;
            padding: 0.85rem 1rem;
        }

        .quick-item .label {
            font-size: 0.8rem;
            color: #64748b;
            margin-bottom: 0.2rem;
        }

        .quick-item .value {
            font-size: 1.25rem;
            font-weight: 700;
        }

        .panel-heading {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: flex-start;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .panel-heading p {
            color: #64748b;
            margin-bottom: 0;
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

        .page-note {
            color: #64748b;
        }

        .chart-panel {
            height: 100%;
        }

        .image-thumb-placeholder {
            width: 72px;
            height: 52px;
            display: grid;
            place-items: center;
            border-radius: 14px;
            border: 1px solid rgba(15, 74, 214, 0.12);
            background: rgba(15, 74, 214, 0.08);
            color: #0f4ad6;
        }

        .modal-header {
            border-bottom: 1px solid rgba(18, 35, 63, 0.1);
        }

        .modal-footer {
            border-top: 1px solid rgba(18, 35, 63, 0.1);
        }

        .modal-content {
            border: 0;
            border-radius: 24px;
            box-shadow: 0 24px 56px rgba(7, 25, 53, 0.2);
        }

        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(165px, 1fr));
            gap: 0.85rem;
        }
    </style>
@endpush

@section('hero')
    @unless ($showAdminWorkspaceOnly)
        <div class="hero-panel p-4 p-lg-5">
            <div class="row g-4 align-items-center">
                <div class="col-lg-7">
                    <h1 class="display-6 fw-bold mb-0">Trang chủ</h1>
                </div>

                <div class="col-lg-5">
                    <div class="search-panel">
                        <form method="GET" action="{{ route('home') }}" class="row g-3">
                            <div class="col-12">
                                <label for="search" class="form-label text-white fw-semibold">Tên tour hoặc điểm đến</label>
                                <input id="search" type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Ví dụ: Đà Lạt, Phú Quốc">
                            </div>
                            <div class="col-sm-6">
                                <label for="min_price" class="form-label text-white fw-semibold">Giá từ</label>
                                <input id="min_price" type="number" class="form-control" name="min_price" value="{{ request('min_price') }}" placeholder="1000000">
                            </div>
                            <div class="col-sm-6">
                                <label for="max_price" class="form-label text-white fw-semibold">Giá đến</label>
                                <input id="max_price" type="number" class="form-control" name="max_price" value="{{ request('max_price') }}" placeholder="10000000">
                            </div>
                            <div class="col-12 d-flex flex-wrap gap-2 pt-1">
                                <button type="submit" class="btn btn-light text-primary fw-semibold px-4">Tìm tour</button>
                                <a href="{{ route('home') }}" class="btn btn-outline-light px-4">Xóa bộ lọc</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endunless
@endsection

@section('content')
    @php
        $activeFilters = [];

        if (request()->filled('search')) {
            $activeFilters[] = 'Từ khóa: ' . request('search');
        }
        if (request()->filled('min_price')) {
            $activeFilters[] = 'Giá từ: ' . number_format((int) request('min_price'), 0, ',', '.') . ' VND';
        }
        if (request()->filled('max_price')) {
            $activeFilters[] = 'Giá đến: ' . number_format((int) request('max_price'), 0, ',', '.') . ' VND';
        }
    @endphp

    @if (! $showAdminWorkspaceOnly)
        <div class="metrics-grid">
            <div class="metric-item">
                <div class="label">Tổng tour phù hợp</div>
                <div class="value">{{ number_format($tours->total(), 0, ',', '.') }}</div>
            </div>
            <div class="metric-item">
                <div class="label">Tour đang hiển thị</div>
                <div class="value">{{ number_format($tours->count(), 0, ',', '.') }}</div>
            </div>
            <div class="metric-item">
                <div class="label">Phòng mới nhất</div>
                <div class="value">{{ number_format($featuredRooms->count(), 0, ',', '.') }}</div>
            </div>
            <div class="metric-item">
                <div class="label">Bộ lọc đang bật</div>
                <div class="value">{{ count($activeFilters) }}</div>
            </div>
        </div>

        @if (count($activeFilters) > 0)
            <div class="surface-card p-3 mb-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                <div class="d-flex flex-wrap gap-2">
                    @foreach ($activeFilters as $filter)
                        <span class="badge badge-soft px-3 py-2">{{ $filter }}</span>
                    @endforeach
                </div>
                <a href="{{ route('home') }}" class="btn btn-soft">Làm mới kết quả</a>
            </div>
        @endif

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h2 class="section-title mb-0">Danh sách tour</h2>
            </div>
            <span class="badge badge-soft px-3 py-2">{{ $tours->total() }} tour phù hợp</span>
        </div>

        <div class="row g-4">
            @forelse ($tours as $tour)
                <div class="col-md-6 col-xl-4">
                    <article class="listing-card h-100">
                        @if ($tour->image_url)
                            <img src="{{ $tour->image_url }}" alt="{{ $tour->name }}" class="listing-media">
                        @else
                            <div class="listing-placeholder">
                                <i class="fa-regular fa-image fa-3x"></i>
                            </div>
                        @endif

                        <div class="p-4 d-flex flex-column h-100">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                                <h3 class="h5 text-primary mb-0">{{ $tour->name }}</h3>
                                <span class="badge badge-soft">{{ $tour->duration_days }} ngày</span>
                            </div>

                            <p class="muted-copy flex-grow-1 mb-3">{{ Str::limit($tour->description, 120) }}</p>

                            <div class="listing-meta d-grid gap-2 mb-3">
                                <span><i class="fa-solid fa-location-dot me-2 text-primary"></i>{{ $tour->destination }}</span>
                                <span><i class="fa-regular fa-calendar me-2 text-primary"></i>{{ \Carbon\Carbon::parse($tour->start_date)->format('d/m/Y') }}</span>
                                <span><i class="fa-solid fa-users me-2 text-primary"></i>{{ $tour->available_seats }} chỗ trống</span>
                                <span><i class="fa-regular fa-user me-2 text-primary"></i>{{ $tour->owner?->name ?? 'Hệ thống' }}</span>
                            </div>

                            <div class="d-flex justify-content-between align-items-end gap-3">
                                <div>
                                    <div class="h4 text-danger mb-0">{{ number_format($tour->price, 0, ',', '.') }} VND</div>
                                    <small class="muted-copy">mỗi người</small>
                                </div>
                                <a href="{{ route('tours.show', $tour->id) }}" class="btn btn-brand px-4">Xem chi tiết</a>
                            </div>
                        </div>
                    </article>
                </div>
            @empty
                <div class="col-12">
                    <div class="surface-card p-5 text-center">
                        <div class="icon-pill mx-auto mb-3 text-primary">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </div>
                        <h3 class="h4 mb-2">Không tìm thấy tour phù hợp</h3>
                        <a href="{{ route('home') }}" class="btn btn-brand px-4">Xem tất cả tour</a>
                    </div>
                </div>
            @endforelse
        </div>

        <div class="d-flex justify-content-center mt-4">
            {{ $tours->appends(request()->query())->links() }}
        </div>

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mt-5 mb-4">
            <div>
                <h2 class="section-title mb-0">Phòng khách sạn</h2>
            </div>
            <span class="badge badge-soft px-3 py-2">{{ $featuredRooms->count() }} phòng mới</span>
        </div>

        <div class="row g-4">
            @forelse ($featuredRooms as $room)
                <div class="col-md-6 col-xl-4">
                    <article class="listing-card h-100">
                        @if ($room->image_url)
                            <img src="{{ $room->image_url }}" alt="{{ $room->title }}" class="listing-media">
                        @else
                            <div class="listing-placeholder">
                                <i class="fa-solid fa-bed fa-3x"></i>
                            </div>
                        @endif

                        <div class="p-4 d-flex flex-column h-100">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                                <div>
                                    <h3 class="h5 text-primary mb-1">{{ $room->title }}</h3>
                                    <div class="muted-copy">{{ $room->hotel_name }}</div>
                                </div>
                                <span class="badge {{ $room->status === 'active' ? 'badge-soft' : 'bg-secondary' }}">
                                    {{ $room->status === 'active' ? 'Đang hiển thị' : 'Ẩn' }}
                                </span>
                            </div>

                            <p class="muted-copy flex-grow-1 mb-3">{{ Str::limit($room->description, 120) }}</p>

                            <div class="listing-meta d-grid gap-2 mb-3">
                                <span><i class="fa-solid fa-location-dot me-2 text-primary"></i>{{ $room->location }}</span>
                                <span><i class="fa-solid fa-user-group me-2 text-primary"></i>{{ $room->guest_capacity }} khách / phòng</span>
                                <span><i class="fa-solid fa-door-open me-2 text-primary"></i>{{ $room->available_rooms }} phòng trống</span>
                                <span><i class="fa-regular fa-user me-2 text-primary"></i>{{ $room->owner?->name ?? 'Đối tác hệ thống' }}</span>
                            </div>

                            <div class="d-flex justify-content-between align-items-end gap-3">
                                <div>
                                    <div class="h4 text-danger mb-0">{{ number_format($room->price_per_night, 0, ',', '.') }} VND</div>
                                    <small class="muted-copy">mỗi đêm</small>
                                </div>
                                <a href="{{ route('rooms.show', $room->id) }}" class="btn btn-brand px-4">Xem phòng</a>
                            </div>
                        </div>
                    </article>
                </div>
            @empty
                <div class="col-12">
                    <div class="surface-card p-5 text-center">
                        <div class="icon-pill mx-auto mb-3 text-primary">
                            <i class="fa-solid fa-hotel"></i>
                        </div>
                        <h3 class="h4 mb-2">Chưa có phòng nào được đăng</h3>
                    </div>
                </div>
            @endforelse
        </div>
    @endif

    @if ($showAdminWorkspaceOnly)
        <section id="quan-tri-noi-bo">
            <section class="panel-header">
                <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                    <h2 class="h3 mb-0">Quản trị</h2>
                    <div class="workspace-actions">
                        <a href="{{ route('home') }}" class="btn btn-light text-primary fw-semibold">
                            <i class="fa-solid fa-house me-1"></i>Trang công khai
                        </a>
                        <a href="{{ $adminHomeTabUrl('tour-du-lich') }}" class="btn btn-outline-light" data-admin-switch="tour-du-lich" data-admin-open="tour-create">
                            <i class="fa-solid fa-plus me-1"></i>Thêm tour
                        </a>
                        <a href="{{ $adminHomeTabUrl('phong') }}" class="btn btn-outline-light" data-admin-switch="phong" data-admin-open="room-create">
                            <i class="fa-solid fa-bed me-1"></i>Đăng phòng
                        </a>
                        <a href="{{ $adminHomeTabUrl('bao-cao') }}" class="btn btn-outline-light" data-admin-switch="bao-cao">
                            <i class="fa-solid fa-chart-line me-1"></i>Báo cáo
                        </a>
                    </div>
                </div>
            </section>

            <div class="workspace-mobile-nav d-lg-none">
                <a href="{{ $adminHomeTabUrl('tong-quan') }}" class="workspace-jump" data-admin-switch="tong-quan" data-admin-tab-link="tong-quan">
                    <i class="fa-solid fa-gauge-high"></i>
                    <span>Tổng quan</span>
                </a>
                <a href="{{ $adminHomeTabUrl('don-dat') }}" class="workspace-jump" data-admin-switch="don-dat" data-admin-tab-link="don-dat">
                    <i class="fa-solid fa-receipt"></i>
                    <span>Đơn đặt</span>
                </a>
                <a href="{{ $adminHomeTabUrl('tour-du-lich') }}" class="workspace-jump" data-admin-switch="tour-du-lich" data-admin-tab-link="tour-du-lich">
                    <i class="fa-solid fa-route"></i>
                    <span>Tour</span>
                </a>
                <a href="{{ $adminHomeTabUrl('phong') }}" class="workspace-jump" data-admin-switch="phong" data-admin-tab-link="phong">
                    <i class="fa-solid fa-bed"></i>
                    <span>Phòng</span>
                </a>
                <a href="{{ $adminHomeTabUrl('bao-cao') }}" class="workspace-jump" data-admin-switch="bao-cao" data-admin-tab-link="bao-cao">
                    <i class="fa-solid fa-chart-pie"></i>
                    <span>Báo cáo</span>
                </a>
            </div>

            @include('quan_tri.thanh_phan.tong_quan_workspace')
            @include('quan_tri.thanh_phan.don_dat_workspace')
            @include('quan_tri.thanh_phan.tour_workspace')
            @include('quan_tri.thanh_phan.phong_workspace')
            @include('quan_tri.thanh_phan.bao_cao_workspace')
        </section>
    @endif
@endsection

@if ($isAdminViewingHome)
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const workspaceRoot = document.getElementById('quan-tri-noi-bo');

                if (!workspaceRoot) {
                    return;
                }

                const panels = Array.from(workspaceRoot.querySelectorAll('[data-admin-panel]'));

                if (panels.length === 0) {
                    return;
                }

                const homePath = new URL(@json(route('home')), window.location.origin).pathname;
                const switchLinks = Array.from(document.querySelectorAll('[data-admin-switch], [data-admin-target]'));
                const tabLinks = Array.from(document.querySelectorAll('[data-admin-tab-link]'));
                const validTargets = panels.map((panel) => panel.dataset.adminPanel);
                const fallbackTarget = @json($initialAdminTab);
                const formContext = @json($adminContext);
                const formItemId = @json($adminItemId);
                let chartsReady = false;

                const syncUrl = (target) => {
                    const url = new URL(window.location.href);
                    url.searchParams.set('admin_active_tab', target);
                    url.hash = 'quan-tri-noi-bo';
                    window.history.replaceState(null, '', url);
                };

                const syncTabLinks = (target) => {
                    tabLinks.forEach((link) => {
                        link.classList.toggle('active', link.dataset.adminTabLink === target);
                    });
                };

                const renderCharts = () => {
                    if (chartsReady) {
                        return;
                    }

                    const statusCanvas = document.getElementById('bookingStatusChart');
                    const revenueCanvas = document.getElementById('revenueChart');

                    if (!statusCanvas || !revenueCanvas) {
                        return;
                    }

                    chartsReady = true;

                    new Chart(statusCanvas.getContext('2d'), {
                        type: 'doughnut',
                        data: {
                            labels: ['Chờ xác nhận', 'Đã xác nhận', 'Đã hủy', 'Hoàn tất'],
                            datasets: [{
                                data: [
                                    {{ (int) ($statusStats['pending'] ?? 0) }},
                                    {{ (int) ($statusStats['confirmed'] ?? 0) }},
                                    {{ (int) ($statusStats['cancelled'] ?? 0) }},
                                    {{ (int) ($statusStats['completed'] ?? 0) }}
                                ],
                                backgroundColor: ['#f3c45f', '#25b6a5', '#ee7878', '#6b93ff']
                            }]
                        },
                        options: {
                            plugins: {
                                legend: {
                                    position: 'bottom'
                                }
                            }
                        }
                    });

                    const revenueData = @json($revenueByMonth);

                    new Chart(revenueCanvas.getContext('2d'), {
                        type: 'line',
                        data: {
                            labels: revenueData.map((item) => `${item.month}/${item.year}`),
                            datasets: [{
                                label: 'Doanh thu (VND)',
                                data: revenueData.map((item) => item.revenue),
                                borderColor: '#0f4ad6',
                                backgroundColor: 'rgba(15, 74, 214, 0.12)',
                                fill: true,
                                tension: 0.35
                            }]
                        },
                        options: {
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback(value) {
                                            return new Intl.NumberFormat('vi-VN').format(value);
                                        }
                                    }
                                }
                            }
                        }
                    });
                };

                const activatePanel = (target, updateUrl = true) => {
                    const nextTarget = validTargets.includes(target) ? target : 'tong-quan';

                    panels.forEach((panel) => {
                        panel.classList.toggle('is-active', panel.dataset.adminPanel === nextTarget);
                    });

                    syncTabLinks(nextTarget);

                    if (updateUrl) {
                        syncUrl(nextTarget);
                    }

                    if (nextTarget === 'bao-cao') {
                        renderCharts();
                    }
                };

                const openContextForm = () => {
                    if (formContext === 'tour-create') {
                        activatePanel('tour-du-lich', false);
                        bootstrap.Collapse.getOrCreateInstance(document.getElementById('tourCreateCollapse')).show();
                    }

                    if (formContext === 'room-create') {
                        activatePanel('phong', false);
                        bootstrap.Collapse.getOrCreateInstance(document.getElementById('roomCreateCollapse')).show();
                    }

                    if (formContext === 'tour-edit' && formItemId) {
                        activatePanel('tour-du-lich', false);
                        const modal = document.getElementById(`tourEditModal${formItemId}`);

                        if (modal) {
                            bootstrap.Modal.getOrCreateInstance(modal).show();
                        }
                    }

                    if (formContext === 'room-edit' && formItemId) {
                        activatePanel('phong', false);
                        const modal = document.getElementById(`roomEditModal${formItemId}`);

                        if (modal) {
                            bootstrap.Modal.getOrCreateInstance(modal).show();
                        }
                    }
                };

                const scrollToWorkspace = () => {
                    workspaceRoot.scrollIntoView({ behavior: 'smooth', block: 'start' });
                };

                const initialHashTarget = window.location.hash.replace('#', '');
                const initialTarget = validTargets.includes(initialHashTarget)
                    ? initialHashTarget
                    : (validTargets.includes(fallbackTarget) ? fallbackTarget : 'tong-quan');

                switchLinks.forEach((link) => {
                    link.addEventListener('click', (event) => {
                        const switchTarget = link.dataset.adminSwitch || link.dataset.adminTarget;

                        if (!switchTarget) {
                            return;
                        }

                        const destination = new URL(link.href, window.location.origin);

                        if (destination.pathname === homePath) {
                            event.preventDefault();
                            activatePanel(switchTarget);

                            if (link.dataset.adminOpen === 'tour-create') {
                                bootstrap.Collapse.getOrCreateInstance(document.getElementById('tourCreateCollapse')).show();
                            }

                            if (link.dataset.adminOpen === 'room-create') {
                                bootstrap.Collapse.getOrCreateInstance(document.getElementById('roomCreateCollapse')).show();
                            }

                            scrollToWorkspace();
                        }
                    });
                });

                document.querySelectorAll('input[type="file"][data-preview-target]').forEach((input) => {
                    input.addEventListener('change', (event) => {
                        const [file] = event.target.files;
                        const preview = document.getElementById(event.target.dataset.previewTarget);

                        if (!file || !preview) {
                            return;
                        }

                        preview.src = URL.createObjectURL(file);
                        preview.classList.remove('d-none');
                    });
                });

                activatePanel(initialTarget, false);
                openContextForm();

                window.addEventListener('hashchange', () => {
                    const target = window.location.hash.replace('#', '');

                    if (validTargets.includes(target)) {
                        activatePanel(target, false);
                    }
                });
            });
        </script>
    @endpush
@endif

