@extends('bo_cuc.quan_tri')

@section('title', 'Báo cáo đơn đặt - TourBooking Admin')

@section('content')
    <section class="panel-header">
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
            <h1 class="h3 mb-0">Báo cáo đơn đặt</h1>
            <a href="{{ route('admin.reports.export-bookings', request()->only(['status', 'start_date', 'end_date'])) }}" class="btn btn-light text-primary fw-semibold align-self-start">
                <i class="fa-solid fa-download me-1"></i>Xuất CSV
            </a>
        </div>
    </section>

    <section class="panel-card p-4 mb-4">
        <form method="GET" action="{{ route('admin.reports.bookings') }}" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-semibold">Trạng thái</label>
                <select name="status" class="form-select">
                    <option value="">Tất cả</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Chờ xác nhận</option>
                    <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Đã xác nhận</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Hoàn tất</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Từ ngày</label>
                <input type="date" class="form-control" name="start_date" value="{{ request('start_date') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Đến ngày</label>
                <input type="date" class="form-control" name="end_date" value="{{ request('end_date') }}">
            </div>
            <div class="col-md-2 d-grid">
                <button type="submit" class="btn btn-brand">
                    <i class="fa-solid fa-filter me-1"></i>Lọc
                </button>
            </div>
        </form>
    </section>

    <section class="quick-grid mb-4">
        <article class="quick-item">
            <div class="label">Chờ xác nhận</div>
            <div class="value">{{ number_format($statusStats['pending'] ?? 0, 0, ',', '.') }}</div>
        </article>
        <article class="quick-item">
            <div class="label">Đã xác nhận</div>
            <div class="value">{{ number_format($statusStats['confirmed'] ?? 0, 0, ',', '.') }}</div>
        </article>
        <article class="quick-item">
            <div class="label">Đã hủy</div>
            <div class="value">{{ number_format($statusStats['cancelled'] ?? 0, 0, ',', '.') }}</div>
        </article>
        <article class="quick-item">
            <div class="label">Hoàn tất</div>
            <div class="value">{{ number_format($statusStats['completed'] ?? 0, 0, ',', '.') }}</div>
        </article>
    </section>

    <section class="panel-card p-4">
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
                    @forelse($bookings as $booking)
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
                            <td>{{ $booking->user->name }}</td>
                            <td>{{ Str::limit($booking->tour->name, 44) }}</td>
                            <td>{{ $booking->number_of_people }}</td>
                            <td class="fw-semibold text-danger">{{ number_format($booking->total_price, 0, ',', '.') }} VND</td>
                            <td><span class="{{ $chipClass }}">{{ $statusLabel }}</span></td>
                            <td>{{ $booking->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center page-note py-4">Không có dữ liệu phù hợp bộ lọc.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $bookings->withQueryString()->links() }}
        </div>
    </section>
@endsection



