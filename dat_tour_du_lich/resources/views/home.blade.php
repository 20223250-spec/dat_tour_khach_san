@extends('layouts.site')

@section('title', 'Trang chủ - TourBooking')

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

        .tour-card {
            position: relative;
            overflow: hidden;
            border-radius: 22px;
            border: 1px solid rgba(18, 34, 64, 0.09);
            background: rgba(255, 255, 255, 0.95);
            box-shadow: var(--site-shadow);
            transition: transform 0.24s ease, box-shadow 0.24s ease;
        }

        .tour-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 26px 54px rgba(8, 24, 56, 0.18);
        }

        .tour-media {
            height: 220px;
            width: 100%;
            object-fit: cover;
            border-bottom: 1px solid rgba(18, 34, 64, 0.09);
        }

        .tour-placeholder {
            height: 220px;
            display: grid;
            place-items: center;
            color: #0f4ad6;
            background: linear-gradient(135deg, rgba(15, 74, 214, 0.12), rgba(20, 164, 199, 0.14));
        }

        .tour-meta {
            color: var(--site-muted);
            font-size: 0.93rem;
        }
    </style>
@endpush

@section('hero')
    <div class="hero-panel p-4 p-lg-5">
        <div class="row g-4 align-items-center">
            <div class="col-lg-7">
                <span class="badge badge-soft px-3 py-2 mb-3">Đặt tour theo nhiều tiêu chí</span>
                <h1 class="display-6 fw-bold mb-3">Chọn hành trình phù hợp với lịch trình và ngân sách của bạn.</h1>
                <p class="lead text-white-50 mb-4">
                    Lọc nhanh theo tên tour, địa điểm, ngày khởi hành, giá, thời lượng và số chỗ trống. Thông tin tour, đơn đặt và thông báo được tập trung tại một nơi.
                </p>
                <div class="d-flex flex-wrap gap-3 text-white-50">
                    <span><i class="fa-solid fa-shield-halved me-2"></i>Quy trình đặt tour an toàn</span>
                    <span><i class="fa-solid fa-bell me-2"></i>Theo dõi thông báo liên tục</span>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="search-panel">
                    <form method="GET" action="{{ route('home') }}" class="row g-3">
                        <div class="col-12">
                            <label for="search" class="form-label text-white fw-semibold">Tên tour</label>
                            <input id="search" type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Ví dụ: Tour Sapa">
                        </div>
                        <div class="col-12">
                            <label for="destination" class="form-label text-white fw-semibold">Điểm đến</label>
                            <input id="destination" type="text" class="form-control" name="destination" value="{{ request('destination') }}" placeholder="Ví dụ: Đà Lạt, Phú Quốc">
                        </div>
                        <div class="col-sm-6">
                            <label for="start_date_from" class="form-label text-white fw-semibold">Ngày khởi hành từ</label>
                            <input id="start_date_from" type="date" class="form-control" name="start_date_from" value="{{ request('start_date_from') }}">
                        </div>
                        <div class="col-sm-6">
                            <label for="start_date_to" class="form-label text-white fw-semibold">Ngày khởi hành đến</label>
                            <input id="start_date_to" type="date" class="form-control" name="start_date_to" value="{{ request('start_date_to') }}">
                        </div>
                        <div class="col-sm-6">
                            <label for="min_price" class="form-label text-white fw-semibold">Giá từ</label>
                            <input id="min_price" type="number" class="form-control" name="min_price" value="{{ request('min_price') }}" placeholder="1000000">
                        </div>
                        <div class="col-sm-6">
                            <label for="max_price" class="form-label text-white fw-semibold">Giá đến</label>
                            <input id="max_price" type="number" class="form-control" name="max_price" value="{{ request('max_price') }}" placeholder="10000000">
                        </div>
                        <div class="col-sm-6">
                            <label for="min_duration_days" class="form-label text-white fw-semibold">Thời lượng từ</label>
                            <input id="min_duration_days" type="number" class="form-control" name="min_duration_days" value="{{ request('min_duration_days') }}" placeholder="2" min="1">
                        </div>
                        <div class="col-sm-6">
                            <label for="max_duration_days" class="form-label text-white fw-semibold">Thời lượng đến</label>
                            <input id="max_duration_days" type="number" class="form-control" name="max_duration_days" value="{{ request('max_duration_days') }}" placeholder="10" min="1">
                        </div>
                        <div class="col-12">
                            <label for="min_seats" class="form-label text-white fw-semibold">Số chỗ trống tối thiểu</label>
                            <input id="min_seats" type="number" class="form-control" name="min_seats" value="{{ request('min_seats') }}" placeholder="5" min="1">
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
@endsection

@section('content')
    @php
        $activeFilters = [];

        if (request()->filled('search')) {
            $activeFilters[] = 'Từ khóa: ' . request('search');
        }
        if (request()->filled('destination')) {
            $activeFilters[] = 'Điểm đến: ' . request('destination');
        }
        if (request()->filled('start_date_from')) {
            $activeFilters[] = 'Khởi hành từ: ' . \Carbon\Carbon::parse(request('start_date_from'))->format('d/m/Y');
        }
        if (request()->filled('start_date_to')) {
            $activeFilters[] = 'Khởi hành đến: ' . \Carbon\Carbon::parse(request('start_date_to'))->format('d/m/Y');
        }
        if (request()->filled('min_price')) {
            $activeFilters[] = 'Giá từ: ' . number_format((int) request('min_price'), 0, ',', '.') . ' VND';
        }
        if (request()->filled('max_price')) {
            $activeFilters[] = 'Giá đến: ' . number_format((int) request('max_price'), 0, ',', '.') . ' VND';
        }
        if (request()->filled('min_duration_days')) {
            $activeFilters[] = 'Thời lượng từ: ' . request('min_duration_days') . ' ngày';
        }
        if (request()->filled('max_duration_days')) {
            $activeFilters[] = 'Thời lượng đến: ' . request('max_duration_days') . ' ngày';
        }
        if (request()->filled('min_seats')) {
            $activeFilters[] = 'Chỗ trống tối thiểu: ' . request('min_seats');
        }
    @endphp

    <div class="metrics-grid">
        <div class="metric-item">
            <div class="label">Tổng kết quả</div>
            <div class="value">{{ number_format($tours->total(), 0, ',', '.') }}</div>
        </div>
        <div class="metric-item">
            <div class="label">Đang hiển thị</div>
            <div class="value">{{ number_format($tours->count(), 0, ',', '.') }}</div>
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
            <h2 class="section-title mb-1">Danh sách tour đang mở bán</h2>
            <p class="muted-copy mb-0">Các tour còn chỗ và sắp khởi hành được ưu tiên hiển thị trước.</p>
        </div>
        <span class="badge badge-soft px-3 py-2">{{ $tours->total() }} tour phù hợp</span>
    </div>

    <div class="row g-4">
        @forelse ($tours as $tour)
            <div class="col-md-6 col-xl-4">
                <article class="tour-card h-100">
                    <a href="{{ route('tours.show', $tour->id) }}" class="d-block" aria-label="Xem chi tiết tour {{ $tour->name }}">
                        @if ($tour->image)
                            <img src="{{ \Illuminate\Support\Str::startsWith($tour->image, ['http://', 'https://']) ? $tour->image : asset($tour->image) }}" alt="{{ $tour->name }}" class="tour-media">
                        @else
                            <div class="tour-placeholder">
                                <i class="fa-regular fa-image fa-3x"></i>
                            </div>
                        @endif
                    </a>

                    <div class="p-4 d-flex flex-column h-100">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                            <h3 class="h5 mb-0">
                                <a href="{{ route('tours.show', $tour->id) }}" class="text-primary text-decoration-none">{{ $tour->name }}</a>
                            </h3>
                            <span class="badge badge-soft">{{ $tour->duration_days }} ngày</span>
                        </div>

                        <div class="small fw-semibold text-body-secondary mb-3">
                            <i class="fa-regular fa-calendar-days me-2 text-primary"></i>
                            Ngày khởi hành: {{ \Carbon\Carbon::parse($tour->start_date)->format('d/m/Y') }}
                        </div>

                        <div class="small fw-semibold text-danger mb-3">
                            <i class="fa-solid fa-tag me-2 text-primary"></i>
                            Giá: {{ number_format($tour->price, 0, ',', '.') }} VND / người
                        </div>

                        <p class="muted-copy flex-grow-1 mb-3">{{ Str::limit($tour->description, 120) }}</p>

                        <div class="tour-meta d-grid gap-2 mb-3">
                            <span><i class="fa-solid fa-location-dot me-2 text-primary"></i>{{ $tour->destination }}</span>
                            <span><i class="fa-solid fa-users me-2 text-primary"></i>{{ $tour->available_seats }} chỗ trống</span>
                        </div>

                        <div class="d-flex justify-content-end align-items-end gap-3">
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
                    <p class="muted-copy mb-4">Thử nới rộng bộ lọc hoặc xóa từ khóa để xem thêm lựa chọn.</p>
                    <a href="{{ route('home') }}" class="btn btn-brand px-4">Xem tất cả tour</a>
                </div>
            </div>
        @endforelse
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $tours->appends(request()->query())->links() }}
    </div>
@endsection


