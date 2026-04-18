<div class="workspace-panel" data-admin-panel="bao-cao">
    <section class="quick-grid mb-4">
        <article class="quick-item">
            <div class="label">Tong tour</div>
            <div class="value">{{ number_format($stats['total_tours'], 0, ',', '.') }}</div>
        </article>
        <article class="quick-item">
            <div class="label">Tong don tour</div>
            <div class="value">{{ number_format($stats['total_bookings'], 0, ',', '.') }}</div>
        </article>
        <article class="quick-item">
            <div class="label">Doanh thu tour</div>
            <div class="value">{{ number_format($stats['total_revenue'], 0, ',', '.') }} VND</div>
        </article>
        <article class="quick-item">
            <div class="label">Cho xac nhan</div>
            <div class="value">{{ number_format($statusStats['pending'] ?? 0, 0, ',', '.') }}</div>
        </article>
        <article class="quick-item">
            <div class="label">Da xac nhan</div>
            <div class="value">{{ number_format($statusStats['confirmed'] ?? 0, 0, ',', '.') }}</div>
        </article>
        <article class="quick-item">
            <div class="label">Da huy</div>
            <div class="value">{{ number_format($statusStats['cancelled'] ?? 0, 0, ',', '.') }}</div>
        </article>
    </section>

    <div class="row g-4 mb-4">
        <div class="col-xl-6">
            <div class="chart-panel">
                <h2 class="h5 mb-3">Ty trong trang thai don tour</h2>
                <canvas id="bookingStatusChart" height="220"></canvas>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="chart-panel">
                <h2 class="h5 mb-3">Doanh thu tour 6 thang gan day</h2>
                <canvas id="revenueChart" height="220"></canvas>
            </div>
        </div>
    </div>

    <section class="embedded-card">
        <div class="panel-heading">
            <h2 class="h4 mb-0">Loc bao cao don tour</h2>
            <a href="{{ route('admin.reports.export-bookings', array_filter(['report_status' => $reportStatus, 'report_start_date' => $reportStartDate, 'report_end_date' => $reportEndDate])) }}" class="btn btn-soft">
                <i class="fa-solid fa-download me-1"></i>Xuat CSV
            </a>
        </div>

        <form method="GET" action="{{ route('home') }}#quan-tri-noi-bo" class="row g-3 align-items-end mb-4">
            <input type="hidden" name="admin_active_tab" value="bao-cao">

            <div class="col-md-4">
                <label class="form-label fw-semibold">Trang thai</label>
                <select name="report_status" class="form-select">
                    <option value="">Tat ca</option>
                    <option value="pending" {{ $reportStatus === 'pending' ? 'selected' : '' }}>Cho xac nhan</option>
                    <option value="confirmed" {{ $reportStatus === 'confirmed' ? 'selected' : '' }}>Da xac nhan</option>
                    <option value="cancelled" {{ $reportStatus === 'cancelled' ? 'selected' : '' }}>Da huy</option>
                    <option value="completed" {{ $reportStatus === 'completed' ? 'selected' : '' }}>Hoan tat</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Tu ngay</label>
                <input type="date" class="form-control" name="report_start_date" value="{{ $reportStartDate }}">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Den ngay</label>
                <input type="date" class="form-control" name="report_end_date" value="{{ $reportEndDate }}">
            </div>
            <div class="col-md-2 d-grid">
                <button type="submit" class="btn btn-brand">
                    <i class="fa-solid fa-filter me-1"></i>Loc
                </button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-clean mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Khach hang</th>
                        <th>Tour</th>
                        <th>So nguoi</th>
                        <th>Tong tien</th>
                        <th>Trang thai</th>
                        <th>Ngay dat</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reportBookings as $booking)
                        @php
                            $chipClass = match ($booking->status) {
                                'pending' => 'chip chip-pending',
                                'confirmed' => 'chip chip-confirmed',
                                'cancelled' => 'chip chip-cancelled',
                                default => 'chip chip-completed',
                            };
                            $statusLabel = match ($booking->status) {
                                'pending' => 'Cho xac nhan',
                                'confirmed' => 'Da xac nhan',
                                'cancelled' => 'Da huy',
                                default => 'Hoan tat',
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
                            <td colspan="7" class="text-center page-note py-4">Khong co du lieu phu hop bo loc.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <div class="row g-4 mt-1">
        <div class="col-xl-5">
            <section class="panel-card p-4 h-100">
                <h2 class="h5 mb-3">Top tour co nhieu don dat</h2>
                <div class="table-responsive">
                    <table class="table table-clean mb-0">
                        <thead>
                            <tr>
                                <th>Tour</th>
                                <th class="text-end">So don</th>
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
                                    <td colspan="2" class="text-center page-note py-4">Chua co du lieu.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <div class="col-xl-7">
            <section class="panel-card p-4 h-100">
                <h2 class="h5 mb-3">Doanh thu theo tour</h2>
                <div class="row g-3">
                    @forelse($reportTours as $tour)
                        <div class="col-lg-6">
                            <article class="border rounded-4 p-3 h-100">
                                <div class="d-flex justify-content-between gap-2">
                                    <div>
                                        <h3 class="h6 mb-1">{{ Str::limit($tour->name, 40) }}</h3>
                                        <p class="text-muted small mb-1"><i class="fa-solid fa-location-dot me-1"></i>{{ $tour->destination }}</p>
                                        <p class="mb-0 small">So don dat: <span class="fw-semibold">{{ $tour->bookings_count }}</span></p>
                                    </div>
                                    <span class="chip chip-confirmed align-self-start">
                                        {{ number_format($tourRevenue[$tour->id] ?? 0, 0, ',', '.') }} VND
                                    </span>
                                </div>
                                <small class="text-muted d-block mt-2">Gia tour: {{ number_format($tour->price, 0, ',', '.') }} VND</small>
                            </article>
                        </div>
                    @empty
                        <div class="col-12">
                            <p class="page-note mb-0">Chua co tour de thong ke.</p>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</div>
