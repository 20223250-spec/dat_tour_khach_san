@extends('bo_cuc.trang_web')

@section('title', $room->title . ' - TourBooking')

@push('styles')
    <style>
        .room-cover {
            width: 100%;
            height: 340px;
            object-fit: cover;
            border-radius: 24px;
            border: 1px solid rgba(18, 34, 64, 0.1);
        }

        .room-cover-placeholder {
            width: 100%;
            height: 340px;
            border-radius: 24px;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, rgba(15, 74, 214, 0.16), rgba(20, 164, 199, 0.16));
            color: #0f4ad6;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 0.9rem;
        }

        .detail-item {
            border-radius: 16px;
            padding: 0.95rem 1rem;
            background: rgba(15, 74, 214, 0.07);
        }
    </style>
@endpush

@section('hero')
    <div class="hero-panel p-4 p-lg-5">
        <div class="row g-4 align-items-center">
            <div class="col-lg-6">
                @if ($room->image_url)
                    <img src="{{ $room->image_url }}" alt="{{ $room->title }}" class="room-cover">
                @else
                    <div class="room-cover-placeholder">
                        <i class="fa-solid fa-bed fa-4x"></i>
                    </div>
                @endif
            </div>
            <div class="col-lg-6">
                <span class="badge badge-soft mb-3 px-3 py-2">{{ $room->hotel_name }}</span>
                <h1 class="display-5 fw-bold mb-3">{{ $room->title }}</h1>
                <p class="lead text-white-50 mb-4">{{ Str::limit($room->description, 180) }}</p>

                <div class="d-flex flex-wrap gap-4 text-white-50 mb-4">
                    <span><i class="fa-solid fa-location-dot me-2"></i>{{ $room->location }}</span>
                    <span><i class="fa-solid fa-user-group me-2"></i>{{ $room->guest_capacity }} khách / phòng</span>
                    <span><i class="fa-solid fa-door-open me-2"></i>{{ $room->available_rooms }} phòng trống</span>
                    <span><i class="fa-regular fa-user me-2"></i>{{ $room->owner?->name ?? 'Đối tác hệ thống' }}</span>
                </div>

                <div class="d-flex flex-wrap align-items-end gap-3">
                    <div>
                        <div class="display-6 fw-bold mb-0">{{ number_format($room->price_per_night, 0, ',', '.') }} VND</div>
                        <small class="text-white-50">mỗi đêm</small>
                    </div>
                    <span class="badge bg-light text-primary px-3 py-2">
                        {{ $room->status === 'active' ? 'Dang mo nhan khach' : 'Tam an' }}
                    </span>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="row g-4">
        <div class="col-lg-8">
            <section class="surface-card p-4 p-lg-5">
                <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
                    <div>
                        <h2 class="section-title h3 mb-0">Thông tin phòng</h2>
                    </div>
                    <a href="{{ route('home') }}" class="btn btn-soft">Quay lại trang chủ</a>
                </div>

                <p class="mb-4">{{ $room->description ?: 'Chủ khách sạn chưa bổ sung thêm mô tả cho phòng này.' }}</p>

                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="text-primary fw-semibold mb-1">Khách sạn</div>
                        <div>{{ $room->hotel_name }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="text-primary fw-semibold mb-1">Vị trí</div>
                        <div>{{ $room->location }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="text-primary fw-semibold mb-1">Sức chứa</div>
                        <div>{{ $room->guest_capacity }} khách / phòng</div>
                    </div>
                    <div class="detail-item">
                        <div class="text-primary fw-semibold mb-1">Phòng trống</div>
                        <div>{{ $room->available_rooms }} phòng</div>
                    </div>
                </div>
            </section>
        </div>

        <div class="col-lg-4">
            @auth
                <section class="surface-card p-4 mb-4">
                    <h2 class="section-title h4 mb-3">Đặt phòng ngay</h2>

                    @if ($errors->any())
                        <div class="alert alert-danger rounded-4 border-0">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('room-bookings.store', $room->id) }}" class="d-grid gap-3">
                        @csrf
                        <div>
                            <label for="customer_name" class="form-label fw-semibold">Tên người nhận phòng</label>
                            <input id="customer_name" type="text" class="form-control" name="customer_name" value="{{ old('customer_name', auth()->user()->name) }}" required>
                        </div>
                        <div>
                            <label for="customer_phone" class="form-label fw-semibold">Số điện thoại</label>
                            <input id="customer_phone" type="text" class="form-control" name="customer_phone" value="{{ old('customer_phone') }}" required>
                        </div>
                        <div class="row g-3">
                            <div class="col-6">
                                <label for="number_of_guests" class="form-label fw-semibold">Số khách</label>
                                <input id="number_of_guests" type="number" class="form-control" name="number_of_guests" min="1" value="{{ old('number_of_guests', 1) }}" required>
                            </div>
                            <div class="col-6">
                                <label for="number_of_rooms" class="form-label fw-semibold">Số phòng</label>
                                <input id="number_of_rooms" type="number" class="form-control" name="number_of_rooms" min="1" max="{{ max($room->available_rooms, 1) }}" value="{{ old('number_of_rooms', 1) }}" required>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-6">
                                <label for="check_in_date" class="form-label fw-semibold">Nhận phòng</label>
                                <input id="check_in_date" type="date" class="form-control" name="check_in_date" value="{{ old('check_in_date', now()->addDay()->format('Y-m-d')) }}" min="{{ now()->format('Y-m-d') }}" required>
                            </div>
                            <div class="col-6">
                                <label for="check_out_date" class="form-label fw-semibold">Trả phòng</label>
                                <input id="check_out_date" type="date" class="form-control" name="check_out_date" value="{{ old('check_out_date', now()->addDays(2)->format('Y-m-d')) }}" min="{{ now()->addDay()->format('Y-m-d') }}" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-brand py-2" {{ $room->available_rooms < 1 || $room->status !== 'active' ? 'disabled' : '' }}>
                            Xác nhận đặt phòng
                        </button>
                    </form>
                </section>
            @else
                <section class="surface-card p-4 mb-4 text-center">
                    <div class="icon-pill mx-auto mb-3 text-primary">
                        <i class="fa-solid fa-right-to-bracket"></i>
                    </div>
                    <h2 class="h4 mb-2">Đăng nhập để đặt phòng</h2>
                    <p class="muted-copy mb-4">Bạn cần đăng nhập để gửi yêu cầu đặt phòng và theo dõi lịch sử đơn.</p>
                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                        <a href="{{ route('login') }}" class="btn btn-brand px-4">Đăng nhập</a>
                        <a href="{{ route('register') }}" class="btn btn-soft px-4">Tạo tài khoản</a>
                    </div>
                </section>
            @endauth

            <section class="surface-card p-4">
                <h2 class="section-title h4 mb-3">Thông tin liên quan</h2>
                <div class="d-grid gap-3">
                    <div class="border rounded-4 p-3">
                        <div class="text-primary fw-semibold mb-1">Giá niêm yết</div>
                        <div>{{ number_format($room->price_per_night, 0, ',', '.') }} VND / đêm</div>
                    </div>
                    <div class="border rounded-4 p-3">
                        <div class="text-primary fw-semibold mb-1">Người đăng</div>
                        <div>{{ $room->owner?->name ?? 'Đối tác hệ thống' }}</div>
                    </div>
                    <div class="border rounded-4 p-3">
                        <div class="text-primary fw-semibold mb-1">Trạng thái</div>
                        <div>{{ $room->status === 'active' ? 'Dang hien thi cong khai' : 'Tam an khoi danh sach' }}</div>
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection
