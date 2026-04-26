@extends('layouts.admin')

@section('title', 'Tổng quan quản trị - TourBooking')

@section('content')
    <section class="panel-header">
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
            <div>
                <h1 class="h3 mb-2">Trang tổng quan quản trị</h1>
                <p class="mb-0 text-white-50">Theo dõi nhanh số liệu vận hành và truy cập các thao tác quan trọng.</p>
            </div>
            <div class="d-flex flex-wrap gap-2 align-self-start">
                <a href="{{ route('admin.tours.create') }}" class="btn btn-light text-primary fw-semibold">
                    <i class="fa-solid fa-plus me-1"></i>Thêm tour
                </a>
                <a href="{{ route('admin.bookings.index') }}" class="btn btn-outline-light">
                    <i class="fa-solid fa-receipt me-1"></i>Xem đơn đặt
                </a>
            </div>
        </div>
    </section>

    <section class="quick-grid mb-4">
        <article class="quick-item">
            <div class="label">Tổng tour</div>
            <div class="value">{{ number_format($stats['total_tours'], 0, ',', '.') }}</div>
        </article>
        <article class="quick-item">
            <div class="label">Tổng đơn đặt</div>
            <div class="value">{{ number_format($stats['total_bookings'], 0, ',', '.') }}</div>
        </article>
        <article class="quick-item">
            <div class="label">Tổng người dùng</div>
            <div class="value">{{ number_format($stats['total_users'], 0, ',', '.') }}</div>
        </article>
        <article class="quick-item">
            <div class="label">Tổng doanh thu</div>
            <div class="value">{{ number_format($stats['total_revenue'], 0, ',', '.') }} VND</div>
        </article>
        <article class="quick-item">
            <div class="label">Đơn chờ xác nhận</div>
            <div class="value">{{ number_format($stats['pending_bookings'], 0, ',', '.') }}</div>
        </article>
        <article class="quick-item">
            <div class="label">Đơn đã xác nhận</div>
            <div class="value">{{ number_format($stats['confirmed_bookings'], 0, ',', '.') }}</div>
        </article>
    </section>

    <section class="panel-card p-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
            <h2 class="h4 mb-0">Đơn đặt gần đây</h2>
            <a href="{{ route('admin.bookings.index') }}" class="btn btn-soft btn-sm">Mở danh sách đầy đủ</a>
        </div>

        <div class="table-responsive">
            <table class="table table-clean mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Khách hàng</th>
                        <th>Tour</th>
                        <th>Số người</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th>Ngày đặt</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recent_bookings as $booking)
                        @php
                            $chipClass = match ($booking->status) {
                                'pending' => 'chip chip-pending',
                                'confirmed' => 'chip chip-confirmed',
                                'cancelled' => 'chip chip-cancelled',
                                'completed' => 'chip chip-completed',
                                default => 'chip',
                            };
                            $statusLabel = match ($booking->status) {
                                'pending' => 'Chờ xác nhận',
                                'confirmed' => 'Đã xác nhận',
                                'cancelled' => 'Đã hủy',
                                'completed' => 'Hoàn tất',
                                default => ucfirst($booking->status),
                            };
                        @endphp
                        <tr>
                            <td>#{{ $booking->id }}</td>
                            <td>
                                <div class="fw-semibold">{{ $booking->customer_name }}</div>
                                <small class="text-muted">{{ $booking->customer_phone }}</small>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ Str::limit($booking->tour->name, 36) }}</div>
                                <small class="text-muted">{{ $booking->tour->destination }}</small>
                            </td>
                            <td>{{ $booking->number_of_people }}</td>
                            <td class="fw-semibold text-danger">{{ number_format($booking->total_price, 0, ',', '.') }} VND</td>
                            <td><span class="{{ $chipClass }}">{{ $statusLabel }}</span></td>
                            <td>{{ $booking->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 page-note">Chưa có dữ liệu đơn đặt.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection


