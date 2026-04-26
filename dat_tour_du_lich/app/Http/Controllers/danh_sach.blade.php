@extends('bo_cuc.trang_web')

@section('title', 'Đơn đặt của tôi - TourBooking')

@php
    $statusMap = [
        'pending' => 'bg-warning text-dark',
        'confirmed' => 'bg-success',
        'checked_in' => 'bg-info text-dark',
        'no_show' => 'bg-secondary',
        'cancelled' => 'bg-danger',
        'completed' => 'bg-primary',
    ];
    $tourBookings = $tourBookings ?? collect();
    $roomBookings = $roomBookings ?? collect();
    $totalBookings = $tourBookings->count() + $roomBookings->count();
@endphp

@section('hero')
    <div class="hero-panel p-4 p-lg-5">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <h1 class="display-6 fw-bold mb-0">Đơn đặt của tôi</h1>
            </div>
            <div class="col-lg-4">
                <div class="surface-card p-4">
                    <h2 class="h5 section-title mb-2 text-dark">Tổng quan</h2>
                    <div class="mb-2 fw-semibold">{{ $totalBookings }} đơn đặt</div>
                    <div class="text-muted small mb-3">{{ $tourBookings->count() }} tour và {{ $roomBookings->count() }} phòng</div>
                    <a href="{{ route('home') }}" class="btn btn-brand w-100">Tiếp tục đặt dịch vụ</a>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    @if ($totalBookings === 0)
        <div class="surface-card p-5 text-center">
            <div class="icon-pill mx-auto mb-3 text-primary">
                <i class="fa-solid fa-suitcase-rolling"></i>
            </div>
            <h2 class="h4 mb-2">Bạn chưa có đơn đặt nào</h2>
            <a href="{{ route('home') }}" class="btn btn-brand px-4">Khám phá dịch vụ</a>
        </div>
    @endif

    @if ($tourBookings->isNotEmpty())
        <section class="mb-5">
            <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                <h2 class="section-title h3 mb-0">Đơn đặt tour</h2>
                <span class="badge badge-soft px-3 py-2">{{ $tourBookings->count() }} đơn</span>
            </div>

            @foreach ($tourBookings as $booking)
                @php
                    $statusClass = $statusMap[$booking->status] ?? 'bg-secondary';
                @endphp

                <article class="surface-card p-4 p-lg-5 mb-4">
                    <div class="row g-4">
                        <div class="col-lg-8">
                            <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                <div>
                                    <h3 class="h4 section-title mb-1">{{ $booking->tour->name }}</h3>
                                    <p class="muted-copy mb-0">{{ Str::limit($booking->tour->description, 145) }}</p>
                                </div>
                                <span class="badge {{ $statusClass }} px-3 py-2">{{ $booking->statusLabel() }}</span>
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

                                <div class="text-primary fw-semibold mb-1">Thanh toán</div>
                                <div class="mb-3">{{ $booking->paymentStatusLabel() }} - {{ number_format($booking->paid_amount ?? 0, 0, ',', '.') }} VND</div>

                                <div class="text-primary fw-semibold mb-1">Tổng tiền</div>
                                <div class="h4 text-danger mb-0">{{ number_format($booking->total_price, 0, ',', '.') }} VND</div>
                            </div>
                        </div>
                    </div>
                </article>
            @endforeach
        </section>
    @endif

    @if ($roomBookings->isNotEmpty())
        <section>
            <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                <h2 class="section-title h3 mb-0">Đơn đặt phòng</h2>
                <span class="badge badge-soft px-3 py-2">{{ $roomBookings->count() }} đơn</span>
            </div>

            @foreach ($roomBookings as $booking)
                @php
                    $statusClass = $statusMap[$booking->status] ?? 'bg-secondary';
                @endphp

                <article class="surface-card p-4 p-lg-5 mb-4">
                    <div class="row g-4">
                        <div class="col-lg-8">
                            <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                <div>
                                    <h3 class="h4 section-title mb-1">{{ $booking->room->title }}</h3>
                                    <p class="muted-copy mb-0">{{ $booking->room->hotel_name }} - {{ $booking->room->location }}</p>
                                </div>
                                <span class="badge {{ $statusClass }} px-3 py-2">{{ $booking->statusLabel() }}</span>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="border rounded-4 p-3 h-100">
                                        <div class="text-primary fw-semibold mb-1">Nhận phòng</div>
                                        <div>{{ $booking->check_in_date->format('d/m/Y') }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="border rounded-4 p-3 h-100">
                                        <div class="text-primary fw-semibold mb-1">Trả phòng</div>
                                        <div>{{ $booking->check_out_date->format('d/m/Y') }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="border rounded-4 p-3 h-100">
                                        <div class="text-primary fw-semibold mb-1">Người nhận phòng</div>
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
                                <div class="text-primary fw-semibold mb-1">Số khách</div>
                                <div class="mb-3">{{ $booking->number_of_guests }} khách</div>

                                <div class="text-primary fw-semibold mb-1">Số phòng và số đêm</div>
                                <div class="mb-3">{{ $booking->number_of_rooms }} phòng / {{ $booking->total_nights }} đêm</div>

                                <div class="text-primary fw-semibold mb-1">Ngày đặt</div>
                                <div class="mb-3">{{ $booking->created_at->format('d/m/Y H:i') }}</div>

                                <div class="text-primary fw-semibold mb-1">Thanh toán</div>
                                <div class="mb-3">{{ $booking->paymentStatusLabel() }} - {{ number_format($booking->paid_amount ?? 0, 0, ',', '.') }} VND</div>

                                @if ($booking->checked_in_at)
                                    <div class="text-primary fw-semibold mb-1">Đã nhận phòng lúc</div>
                                    <div class="mb-3">{{ $booking->checked_in_at->format('d/m/Y H:i') }}</div>
                                @endif

                                @if ($booking->checked_out_at)
                                    <div class="text-primary fw-semibold mb-1">Đã trả phòng lúc</div>
                                    <div class="mb-3">{{ $booking->checked_out_at->format('d/m/Y H:i') }}</div>
                                @endif

                                @if ($booking->no_show_marked_at)
                                    <div class="text-primary fw-semibold mb-1">Đánh dấu không đến</div>
                                    <div class="mb-3">{{ $booking->no_show_marked_at->format('d/m/Y H:i') }}</div>
                                @endif

                                <div class="text-primary fw-semibold mb-1">Tổng tiền</div>
                                <div class="h4 text-danger mb-0">{{ number_format($booking->total_price, 0, ',', '.') }} VND</div>
                            </div>
                        </div>
                    </div>
                </article>
            @endforeach
        </section>
    @endif
@endsection
