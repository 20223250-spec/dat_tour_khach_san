@extends('layouts.site')

@section('title', 'Đơn đặt của tôi - TourBooking')

@php
    $statusMap = [
        'pending' => ['label' => 'Chờ xác nhận', 'class' => 'bg-warning text-dark'],
        'confirmed' => ['label' => 'Đã xác nhận', 'class' => 'bg-success'],
        'cancelled' => ['label' => 'Đã hủy', 'class' => 'bg-danger'],
        'completed' => ['label' => 'Hoàn tất', 'class' => 'bg-primary'],
    ];
@endphp

@section('hero')
    <div class="hero-panel p-4 p-lg-5">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <span class="badge badge-soft mb-3 px-3 py-2">Khu vực khách hàng</span>
                <h1 class="display-6 fw-bold mb-3">Theo dõi toàn bộ đơn đặt tour trong một trang.</h1>
                <p class="lead text-white-50 mb-0">Kiểm tra trạng thái, tổng tiền, số lượng khách và lịch sử đặt cho nhanh gọn.</p>
            </div>
            <div class="col-lg-4">
                <div class="surface-card p-4">
                    <h2 class="h5 section-title mb-2 text-dark">Tổng quan</h2>
                    <p class="muted-copy mb-3">Bạn hiện có {{ $bookings->count() }} đơn đặt.</p>
                    <a href="{{ route('home') }}" class="btn btn-brand w-100">Đặt tour mới</a>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    @forelse ($bookings as $booking)
        @php
            $status = $statusMap[$booking->status] ?? ['label' => ucfirst($booking->status), 'class' => 'bg-secondary'];
        @endphp

        <article class="surface-card p-4 p-lg-5 mb-4">
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                        <div>
                            <h2 class="h4 section-title mb-1">{{ $booking->tour->name }}</h2>
                            <p class="muted-copy mb-0">{{ Str::limit($booking->tour->description, 145) }}</p>
                        </div>
                        <span class="badge {{ $status['class'] }} px-3 py-2">{{ $status['label'] }}</span>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="border rounded-4 p-3 h-100">
                                <div class="text-primary fw-semibold mb-1">Điểm đến</div>
                                <div>{{ $booking->tour->destination }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded-4 p-3 h-100">
                                <div class="text-primary fw-semibold mb-1">Ngày khởi hành</div>
                                <div>{{ \Carbon\Carbon::parse($booking->tour->start_date)->format('d/m/Y') }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded-4 p-3 h-100">
                                <div class="text-primary fw-semibold mb-1">Người đi</div>
                                <div>{{ $booking->customer_name }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded-4 p-3 h-100">
                                <div class="text-primary fw-semibold mb-1">Số điện thoại</div>
                                <div>{{ $booking->customer_phone }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="border rounded-4 p-4 h-100">
                        <div class="text-primary fw-semibold mb-1">Số lượng khách</div>
                        <div class="mb-3">{{ $booking->number_of_people }} người</div>

                        <div class="text-primary fw-semibold mb-1">Ngày đặt</div>
                        <div class="mb-3">{{ $booking->created_at->format('d/m/Y H:i') }}</div>

                        <div class="text-primary fw-semibold mb-1">Tổng tiền</div>
                        <div class="h4 text-danger mb-0">{{ number_format($booking->total_price, 0, ',', '.') }} VND</div>
                    </div>
                </div>
            </div>
        </article>
    @empty
        <div class="surface-card p-5 text-center">
            <div class="icon-pill mx-auto mb-3 text-primary">
                <i class="fa-solid fa-suitcase-rolling"></i>
            </div>
            <h2 class="h4 mb-2">Bạn chưa có đơn đặt nào</h2>
            <p class="muted-copy mb-4">Hãy chọn một tour phù hợp và bắt đầu hành trình của bạn.</p>
            <a href="{{ route('home') }}" class="btn btn-brand px-4">Khám phá tour</a>
        </div>
    @endforelse
@endsection


