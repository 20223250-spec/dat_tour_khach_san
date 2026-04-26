@extends('layouts.admin')

@section('title', 'Quản lý đơn đặt - TourBooking Admin')

@section('content')
    <section class="panel-header">
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
            <div>
                <h1 class="h3 mb-2">Quản lý đơn đặt</h1>
                <p class="mb-0 text-white-50">Cập nhật trạng thái đơn đặt nhanh và theo dõi thông tin khách hàng.</p>
            </div>
            <a href="{{ route('admin.reports.bookings') }}" class="btn btn-light text-primary fw-semibold align-self-start">
                <i class="fa-solid fa-chart-column me-1"></i>Mở báo cáo đơn đặt
            </a>
        </div>
    </section>

    <section class="panel-card p-4">
        <form method="GET" action="{{ route('admin.bookings.index') }}" class="row g-3 mb-4 align-items-end">
            <div class="col-md-3">
                <label for="id" class="form-label fw-semibold">Tìm theo ID đơn</label>
                <input type="number" class="form-control" id="id" name="id" value="{{ request('id') }}" placeholder="Ví dụ: 15" min="1">
            </div>
            <div class="col-md-3">
                <label for="status" class="form-label fw-semibold">Tìm theo trạng thái</label>
                <select class="form-select" id="status" name="status">
                    <option value="">Tất cả trạng thái</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Chờ xác nhận</option>
                    <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Đã xác nhận</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Hoàn tất</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="payment_status" class="form-label fw-semibold">Thanh toán</label>
                <select class="form-select" id="payment_status" name="payment_status">
                    <option value="">Tất cả</option>
                    <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>Chưa thanh toán</option>
                    <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Đã thanh toán</option>
                    <option value="failed" {{ request('payment_status') === 'failed' ? 'selected' : '' }}>Thanh toán lỗi</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="destination" class="form-label fw-semibold">Tìm theo địa điểm tour</label>
                <input type="text" class="form-control" id="destination" name="destination" value="{{ request('destination') }}" placeholder="Ví dụ: Đà Nẵng, Sapa">
            </div>
            <div class="col-md-1 d-flex gap-2">
                <button type="submit" class="btn btn-brand flex-grow-1">
                    <i class="fa-solid fa-magnifying-glass me-1"></i>Tìm kiếm
                </button>
                <a href="{{ route('admin.bookings.index') }}" class="btn btn-ghost">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>
        </form>

        @if($bookings->count() > 0)
            <div class="table-responsive">
                <table class="table table-clean mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Khách hàng</th>
                            <th>Tour</th>
                            <th>Số người</th>
                            <th>Tổng tiền</th>
                            <th>Thanh toán</th>
                            <th>Cập nhật thanh toán</th>
                            <th>Trạng thái</th>
                            <th>Ngày đặt</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bookings as $booking)
                            @php
                                $currentStatusClass = match ($booking->status) {
                                    'pending' => 'chip chip-pending',
                                    'confirmed' => 'chip chip-confirmed',
                                    'cancelled' => 'chip chip-cancelled',
                                    'completed' => 'chip chip-completed',
                                    default => 'chip',
                                };
                            @endphp
                            <tr>
                                <td>#{{ $booking->id }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $booking->customer_name }}</div>
                                    <small class="text-muted">{{ $booking->customer_phone }}</small>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ Str::limit($booking->tour->name, 38) }}</div>
                                    <small class="text-muted">{{ $booking->tour->destination }}</small>
                                </td>
                                <td>{{ $booking->number_of_people }}</td>
                                <td class="fw-semibold text-danger">{{ number_format($booking->total_price, 0, ',', '.') }} VND</td>
                                <td>
                                    @if(($booking->payment_status ?? 'unpaid') === 'paid')
                                        <span class="chip chip-confirmed">Đã thanh toán</span>
                                    @elseif(($booking->payment_status ?? 'unpaid') === 'failed')
                                        <span class="chip chip-cancelled">Thanh toán lỗi</span>
                                    @else
                                        <span class="chip">Chưa thanh toán</span>
                                    @endif
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('admin.bookings.update-payment-status', $booking->id) }}" class="d-flex gap-2 align-items-center">
                                        @csrf
                                        <select name="payment_status" class="form-select form-select-sm" onchange="this.form.submit()">
                                            <option value="unpaid" {{ ($booking->payment_status ?? 'unpaid') === 'unpaid' ? 'selected' : '' }}>Chưa thanh toán</option>
                                            <option value="paid" {{ ($booking->payment_status ?? 'unpaid') === 'paid' ? 'selected' : '' }}>Đã thanh toán</option>
                                            <option value="failed" {{ ($booking->payment_status ?? 'unpaid') === 'failed' ? 'selected' : '' }}>Thanh toán lỗi</option>
                                        </select>
                                    </form>
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('admin.bookings.update-status', $booking->id) }}" class="d-flex gap-2 align-items-center">
                                        @csrf
                                        <span class="{{ $currentStatusClass }}">
                                            @if($booking->status === 'pending')
                                                Chờ xác nhận
                                            @elseif($booking->status === 'confirmed')
                                                Đã xác nhận
                                            @elseif($booking->status === 'cancelled')
                                                Đã hủy
                                            @elseif($booking->status === 'completed')
                                                Hoàn tất
                                            @else
                                                {{ ucfirst($booking->status) }}
                                            @endif
                                        </span>
                                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                            <option value="pending" {{ $booking->status === 'pending' ? 'selected' : '' }}>Chờ xác nhận</option>
                                            <option value="confirmed" {{ $booking->status === 'confirmed' ? 'selected' : '' }}>Đã xác nhận</option>
                                            <option value="cancelled" {{ $booking->status === 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                                            <option value="completed" {{ $booking->status === 'completed' ? 'selected' : '' }}>Hoàn tất</option>
                                        </select>
                                    </form>
                                </td>
                                <td>{{ $booking->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $bookings->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <div class="mb-3 text-primary" style="font-size: 2.8rem;">
                    <i class="fa-solid fa-receipt"></i>
                </div>
                <h2 class="h4 mb-2">Chưa có đơn đặt nào</h2>
                <p class="page-note mb-0">Dữ liệu đơn đặt sẽ hiển thị tại đây khi có người dùng đặt tour.</p>
            </div>
        @endif
    </section>
@endsection


