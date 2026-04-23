@extends('bo_cuc.quan_tri')

@section('title', ($isAdminArea ? 'Quản lý tour' : 'Tour của tôi') . ' - TourBooking')

@section('content')
    <section class="panel-header">
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
            <h1 class="h3 mb-0">{{ $isAdminArea ? 'Quản lý danh sách tour' : 'Tour của bạn' }}</h1>
            <div class="d-flex flex-wrap gap-2 align-self-start">
                <a href="{{ route($routePrefix . '.create') }}" class="btn btn-light text-primary fw-semibold">
                    <i class="fa-solid fa-plus me-1"></i>Thêm tour
                </a>
            </div>
        </div>
    </section>

    <section class="panel-card p-4 mb-4">
        @if($tours->count() > 0)
            <div class="table-responsive">
                <table class="table table-clean mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Ảnh</th>
                            <th>Tour</th>
                            @if($showOwnerColumn)
                                <th>Chủ tour</th>
                            @endif
                            <th>Điểm đến</th>
                            <th>Giá</th>
                            <th>Còn trống / tổng</th>
                            <th>Khởi hành</th>
                            <th class="text-end">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tours as $tour)
                            <tr>
                                <td>#{{ $tour->id }}</td>
                                <td>
                                    @if($tour->image_url)
                                        <img src="{{ $tour->image_url }}" alt="{{ $tour->name }}" class="rounded-3 border" style="width: 72px; height: 52px; object-fit: cover;">
                                    @else
                                        <div class="rounded-3 border d-grid place-items-center text-primary" style="width: 72px; height: 52px; background: rgba(15, 74, 214, 0.08); display: grid;">
                                            <i class="fa-regular fa-image"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ Str::limit($tour->name, 42) }}</div>
                                    <small class="text-muted">{{ Str::limit($tour->description, 56) }}</small>
                                </td>
                                @if($showOwnerColumn)
                                    <td>{{ $tour->owner?->name ?? 'Hệ thống' }}</td>
                                @endif
                                <td>{{ $tour->destination }}</td>
                                <td class="fw-semibold text-danger">{{ number_format($tour->price, 0, ',', '.') }} VND</td>
                                <td>{{ $tour->available_seats }} / {{ $tour->total_seats }}</td>
                                <td>{{ \Carbon\Carbon::parse($tour->start_date)->format('d/m/Y') }}</td>
                                <td>
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route($routePrefix . '.edit', $tour->id) }}" class="btn btn-soft btn-sm">
                                            <i class="fa-solid fa-pen-to-square me-1"></i>Sửa
                                        </a>
                                        <form method="POST" action="{{ route($routePrefix . '.destroy', $tour->id) }}" onsubmit="return confirm('Bạn có chắc muốn xóa tour này?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-ghost btn-sm">
                                                <i class="fa-solid fa-trash me-1"></i>Xóa
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $tours->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <div class="mb-3 text-primary" style="font-size: 2.8rem;">
                    <i class="fa-solid fa-route"></i>
                </div>
                <h2 class="h4 mb-2">Chưa có tour nào</h2>
                <p class="page-note mb-4">Hãy tạo tour đầu tiên để bắt đầu hiển thị trên hệ thống.</p>
                <a href="{{ route($routePrefix . '.create') }}" class="btn btn-brand px-4">
                    <i class="fa-solid fa-plus me-1"></i>Tạo tour
                </a>
            </div>
        @endif
    </section>

    <section class="panel-card p-4">
        <div class="panel-heading">
            <h2 class="h4 mb-0">{{ $isAdminArea ? 'Đơn đặt tour' : 'Đơn đặt cho tour của bạn' }}</h2>
        </div>

        @if(($tourBookings ?? collect())->count() > 0)
            <div class="table-responsive">
                <table class="table table-clean mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Khách hàng</th>
                            <th>Tour</th>
                            <th>Số người</th>
                            <th>Tổng tiền</th>
                            <th>Đã thu</th>
                            <th>Quản lý</th>
                            <th>Ngày đặt</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tourBookings as $booking)
                            @php
                                $currentStatusClass = match ($booking->status) {
                                    'pending' => 'chip chip-pending',
                                    'confirmed' => 'chip chip-confirmed',
                                    'cancelled' => 'chip chip-cancelled',
                                    default => 'chip chip-completed',
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
                                <td class="fw-semibold text-success">{{ number_format($booking->paid_amount ?? 0, 0, ',', '.') }} VND</td>
                                <td>
                                    <form method="POST" action="{{ route($bookingStatusRoute, $booking->id) }}" class="d-grid gap-2" style="min-width: 220px;">
                                        @csrf
                                        <span class="{{ $currentStatusClass }}">{{ $booking->statusLabel() }}</span>
                                        <select name="status" class="form-select form-select-sm">
                                            @foreach(\App\Models\Booking::statusOptions() as $statusValue => $statusLabel)
                                                <option value="{{ $statusValue }}" {{ $booking->status === $statusValue ? 'selected' : '' }}>{{ $statusLabel }}</option>
                                            @endforeach
                                        </select>
                                        <select name="payment_status" class="form-select form-select-sm">
                                            @foreach(\App\Models\Booking::paymentStatusOptions() as $statusValue => $statusLabel)
                                                <option value="{{ $statusValue }}" {{ $booking->payment_status === $statusValue ? 'selected' : '' }}>{{ $statusLabel }}</option>
                                            @endforeach
                                        </select>
                                        <input type="number" class="form-control form-control-sm" name="paid_amount" min="0" step="1000" value="{{ (int) ($booking->paid_amount ?? 0) }}" placeholder="Số tiền đã thu">
                                        <input type="text" class="form-control form-control-sm" name="payment_method" value="{{ $booking->payment_method }}" placeholder="Tiền mặt / chuyển khoản">
                                        <button type="submit" class="btn btn-soft btn-sm">Lưu</button>
                                    </form>
                                </td>
                                <td>{{ $booking->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $tourBookings->links() }}
            </div>
        @else
            <div class="text-center py-4 page-note">Chưa có đơn đặt tour nào.</div>
        @endif
    </section>
@endsection
