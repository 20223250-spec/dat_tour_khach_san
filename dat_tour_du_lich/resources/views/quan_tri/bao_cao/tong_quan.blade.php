@extends('bo_cuc.quan_tri')

@section('title', 'Báo cáo tổng quan - TourBooking Admin')

@push('styles')
    <style>
        .chart-panel {
            border-radius: var(--admin-radius-xl);
            border: 1px solid var(--admin-line);
            background: #fff;
            box-shadow: var(--admin-shadow);
            padding: 1.25rem;
            height: 100%;
        }
    </style>
@endpush

@section('content')
    <section class="panel-header">
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
            <h1 class="h3 mb-0">Báo cáo tổng quan</h1>
            <small class="align-self-start text-white-50">Cập nhật: {{ now()->format('d/m/Y H:i') }}</small>
        </div>
    </section>

    <section class="quick-grid mb-4">
        <article class="quick-item">
            <div class="label">Tổng tour</div>
            <div class="value">{{ number_format($stats['total_tours'], 0, ',', '.') }}</div>
        </article>
        <article class="quick-item">
            <div class="label">Tổng người dùng</div>
            <div class="value">{{ number_format($stats['total_users'], 0, ',', '.') }}</div>
        </article>
        <article class="quick-item">
            <div class="label">Tổng đơn đặt</div>
            <div class="value">{{ number_format($stats['total_bookings'], 0, ',', '.') }}</div>
        </article>
        <article class="quick-item">
            <div class="label">Doanh thu hoàn tất</div>
            <div class="value">{{ number_format($stats['total_revenue'], 0, ',', '.') }} VND</div>
        </article>
        <article class="quick-item">
            <div class="label">Đơn chờ xử lý</div>
            <div class="value">{{ number_format($stats['pending_bookings'], 0, ',', '.') }}</div>
        </article>
        <article class="quick-item">
            <div class="label">Đơn đã xác nhận</div>
            <div class="value">{{ number_format($stats['confirmed_bookings'], 0, ',', '.') }}</div>
        </article>
    </section>

    <div class="row g-4 mb-4">
        <div class="col-xl-6">
            <div class="chart-panel">
                <h2 class="h5 mb-3">Tỷ trọng trạng thái đơn đặt</h2>
                <canvas id="bookingStatusChart" height="220"></canvas>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="chart-panel">
                <h2 class="h5 mb-3">Doanh thu 6 tháng gần đây</h2>
                <canvas id="revenueChart" height="220"></canvas>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-6">
            <section class="panel-card p-4 h-100">
                <h2 class="h5 mb-3">Top tour có nhiều đơn đặt</h2>
                <div class="table-responsive">
                    <table class="table table-clean mb-0">
                        <thead>
                            <tr>
                                <th>Tour</th>
                                <th class="text-end">Số đơn</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topTours as $tour)
                                <tr>
                                    <td>{{ Str::limit($tour->name, 44) }}</td>
                                    <td class="text-end fw-semibold">{{ $tour->bookings_count }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center page-note">Chưa có dữ liệu.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <div class="col-xl-6">
            <section class="panel-card p-4 h-100">
                <h2 class="h5 mb-3">Đơn đặt mới nhất</h2>
                <div class="d-grid gap-2">
                    @forelse($recentBookings as $booking)
                        <article class="border rounded-4 p-3">
                            <div class="d-flex justify-content-between gap-2">
                                <div>
                                    <div class="fw-semibold">{{ $booking->user->name }}</div>
                                    <small class="text-muted">{{ Str::limit($booking->tour->name, 48) }}</small>
                                </div>
                                <span class="chip {{ $booking->status === 'pending' ? 'chip-pending' : ($booking->status === 'confirmed' ? 'chip-confirmed' : ($booking->status === 'cancelled' ? 'chip-cancelled' : 'chip-completed')) }}">
                                    @if($booking->status === 'pending')
                                        Chờ xác nhận
                                    @elseif($booking->status === 'confirmed')
                                        Đã xác nhận
                                    @elseif($booking->status === 'cancelled')
                                        Đã hủy
                                    @else
                                        Hoàn tất
                                    @endif
                                </span>
                            </div>
                            <small class="text-muted d-block mt-1">{{ $booking->created_at->diffForHumans() }}</small>
                        </article>
                    @empty
                        <p class="page-note mb-0">Chưa có đơn đặt mới.</p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const statusCtx = document.getElementById('bookingStatusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Chờ xác nhận', 'Đã xác nhận', 'Đã hủy', 'Hoàn tất'],
                datasets: [{
                    data: [
                        {{ $stats['pending_bookings'] }},
                        {{ $stats['confirmed_bookings'] }},
                        {{ $stats['cancelled_bookings'] }},
                        {{ $stats['total_bookings'] - $stats['pending_bookings'] - $stats['confirmed_bookings'] - $stats['cancelled_bookings'] }}
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
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        new Chart(revenueCtx, {
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
    </script>
@endpush



