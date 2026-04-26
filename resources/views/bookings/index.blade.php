@extends('layouts.site')

@section('title', 'Đơn đặt của tôi - TourBooking')

@php
    $statusMap = [
        'pending' => ['label' => 'Chờ xác nhận', 'class' => 'bg-warning text-dark'],
        'confirmed' => ['label' => 'Đã xác nhận', 'class' => 'bg-success'],
        'cancelled' => ['label' => 'Đã hủy', 'class' => 'bg-danger'],
        'completed' => ['label' => 'Hoàn tất', 'class' => 'bg-primary'],
    ];

    $paymentStatusMap = [
        'unpaid' => ['label' => 'Chưa thanh toán', 'class' => 'bg-secondary'],
        'paid' => ['label' => 'Đã thanh toán', 'class' => 'bg-success'],
        'failed' => ['label' => 'Thanh toán lỗi', 'class' => 'bg-danger'],
    ];
@endphp

@push('styles')
    <style>
        .payment-qr-box {
            border: 1px dashed rgba(15, 74, 214, 0.35);
            border-radius: 16px;
            padding: 0.9rem;
            background: rgba(15, 74, 214, 0.04);
        }

        .payment-qr-image {
            width: 170px;
            height: 170px;
            object-fit: contain;
            border-radius: 12px;
            border: 1px solid rgba(17, 34, 64, 0.08);
            background: #fff;
        }
    </style>
@endpush

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
            $paymentStatus = $paymentStatusMap[$booking->payment_status ?? 'unpaid'] ?? ['label' => ucfirst($booking->payment_status ?? 'unpaid'), 'class' => 'bg-secondary'];
        @endphp

        <article class="surface-card p-4 p-lg-5 mb-4">
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                        <div>
                            <h2 class="h4 section-title mb-1">{{ $booking->tour->name }}</h2>
                            <p class="muted-copy mb-0">{{ Str::limit($booking->tour->description, 145) }}</p>
                        </div>
                        <div class="d-flex flex-column gap-2 align-items-end">
                            <span class="badge {{ $status['class'] }} px-3 py-2">{{ $status['label'] }}</span>
                            <span class="badge {{ $paymentStatus['class'] }} px-3 py-2">{{ $paymentStatus['label'] }}</span>
                        </div>
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

                        @if (($booking->payment_status ?? 'unpaid') === 'paid' && $booking->paid_at)
                            <small class="d-block text-success mt-2">Thanh toán lúc {{ $booking->paid_at->format('d/m/Y H:i') }}</small>
                        @endif

                        @if (($booking->payment_status ?? 'unpaid') !== 'paid' && !in_array($booking->status, ['cancelled', 'completed'], true))
                            <div class="d-grid gap-2 mt-3">
                                <button type="button" class="btn btn-brand w-100 js-open-payment" data-booking-id="{{ $booking->id }}">
                                    Thanh toán
                                </button>

                                <div id="payment_qr_wrap_{{ $booking->id }}" class="payment-qr-box d-none">
                                    <div class="small text-muted mb-2">Quét mã QR để thực hiện thanh toán</div>
                                    <div class="d-flex justify-content-center">
                                        <img src="{{ asset('images/payment_qr_demo.svg') }}" alt="QR thanh toán" class="payment-qr-image">
                                    </div>
                                    <button type="button" class="btn btn-soft w-100 mt-3 js-confirm-payment" data-booking-id="{{ $booking->id }}">
                                        Xác nhận đã thanh toán
                                    </button>
                                </div>

                            </div>
                        @endif
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

@push('scripts')
    <script>
        (function () {
            document.addEventListener('click', function (event) {
                const openButton = event.target.closest('.js-open-payment');

                if (openButton) {
                    const bookingId = openButton.dataset.bookingId;
                    const qrWrap = document.getElementById(`payment_qr_wrap_${bookingId}`);

                    if (qrWrap) {
                        qrWrap.classList.remove('d-none');
                        openButton.classList.add('d-none');
                    }

                    return;
                }

                const confirmButton = event.target.closest('.js-confirm-payment');

                if (confirmButton) {
                    confirmButton.disabled = true;
                    confirmButton.textContent = 'Đã xác nhận đã thanh toán';
                }
            });
        })();
    </script>
@endpush



