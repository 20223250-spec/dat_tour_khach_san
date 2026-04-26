@extends('layouts.admin')

@section('title', 'Báo cáo tour - TourBooking Admin')

@section('content')
    <section class="panel-header">
        <h1 class="h3 mb-2">Báo cáo tour</h1>
        <p class="mb-0 text-white-50">Theo dõi số đơn đặt và doanh thu hoàn tất theo từng tour.</p>
    </section>

    <section class="panel-card p-4 mb-4">
        <div class="row g-3">
            @forelse($tours as $tour)
                <div class="col-lg-6">
                    <article class="border rounded-4 p-3 h-100">
                        <div class="d-flex justify-content-between gap-2">
                            <div>
                                <h2 class="h6 mb-1">{{ Str::limit($tour->name, 44) }}</h2>
                                <p class="text-muted small mb-1"><i class="fa-solid fa-location-dot me-1"></i>{{ $tour->destination }}</p>
                                <p class="mb-0 small">Số đơn đặt: <span class="fw-semibold">{{ $tour->bookings_count }}</span></p>
                            </div>
                            <span class="chip chip-confirmed align-self-start">
                                {{ number_format($tourRevenue[$tour->id] ?? 0, 0, ',', '.') }} VND
                            </span>
                        </div>
                        <small class="text-muted d-block mt-2">Giá tour: {{ number_format($tour->price, 0, ',', '.') }} VND</small>
                    </article>
                </div>
            @empty
                <div class="col-12">
                    <p class="page-note mb-0">Chưa có tour để thống kê.</p>
                </div>
            @endforelse
        </div>
    </section>

    <section class="panel-card p-4">
        <div class="table-responsive">
            <table class="table table-clean mb-0">
                <thead>
                    <tr>
                        <th>Tour</th>
                        <th>Điểm đến</th>
                        <th>Số đơn đặt</th>
                        <th>Doanh thu hoàn tất</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tours as $tour)
                        <tr>
                            <td>{{ Str::limit($tour->name, 50) }}</td>
                            <td>{{ $tour->destination }}</td>
                            <td>{{ $tour->bookings_count }}</td>
                            <td class="fw-semibold text-danger">{{ number_format($tourRevenue[$tour->id] ?? 0, 0, ',', '.') }} VND</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center page-note py-4">Không có dữ liệu.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $tours->links() }}
        </div>
    </section>
@endsection


