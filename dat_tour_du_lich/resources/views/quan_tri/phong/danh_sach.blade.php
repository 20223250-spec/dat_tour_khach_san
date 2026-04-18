@extends('bo_cuc.quan_tri')

@section('title', ($isAdminArea ? 'Quản lý phòng' : 'Phòng của bạn') . ' - TourBooking')

@section('content')
    <section class="panel-header">
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
            <h1 class="h3 mb-0">{{ $isAdminArea ? 'Quản lý phòng khách sạn' : 'Phòng do bạn đăng' }}</h1>
            <div class="d-flex flex-wrap gap-2 align-self-start">
                <a href="{{ route($routePrefix . '.create') }}" class="btn btn-light text-primary fw-semibold">
                    <i class="fa-solid fa-plus me-1"></i>Đăng phòng
                </a>
            </div>
        </div>
    </section>

    <section class="panel-card p-4 mb-4">
        @if($rooms->count() > 0)
            <div class="table-responsive">
                <table class="table table-clean mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Ảnh</th>
                            <th>Tiêu đề</th>
                            @if($showOwnerColumn)
                                <th>Chủ khách sạn</th>
                            @endif
                            <th>Khách sạn</th>
                            <th>Vị trí</th>
                            <th>Giá/đêm</th>
                            <th>Trạng thái</th>
                            <th class="text-end">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rooms as $room)
                            <tr>
                                <td>#{{ $room->id }}</td>
                                <td>
                                    @if($room->image_url)
                                        <img src="{{ $room->image_url }}" alt="{{ $room->title }}" class="rounded-3 border" style="width: 72px; height: 52px; object-fit: cover;">
                                    @else
                                        <div class="rounded-3 border d-grid text-primary" style="width: 72px; height: 52px; background: rgba(15, 74, 214, 0.08); place-items: center;">
                                            <i class="fa-solid fa-bed"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ Str::limit($room->title, 42) }}</div>
                                    <small class="text-muted">{{ Str::limit($room->description, 56) }}</small>
                                </td>
                                @if($showOwnerColumn)
                                    <td>{{ $room->owner?->name ?? 'Hệ thống' }}</td>
                                @endif
                                <td>{{ $room->hotel_name }}</td>
                                <td>{{ $room->location }}</td>
                                <td class="fw-semibold text-danger">{{ number_format($room->price_per_night, 0, ',', '.') }} VND</td>
                                <td>
                                    <span class="chip {{ $room->status === 'active' ? 'chip-confirmed' : 'chip-cancelled' }}">
                                        {{ $room->status === 'active' ? 'Đang hiển thị' : 'Đang ẩn' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route($routePrefix . '.edit', $room->id) }}" class="btn btn-soft btn-sm">
                                            <i class="fa-solid fa-pen-to-square me-1"></i>Sửa
                                        </a>
                                        <form method="POST" action="{{ route($routePrefix . '.destroy', $room->id) }}" onsubmit="return confirm('Bạn có chắc muốn xóa phòng này?')">
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
                {{ $rooms->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <div class="mb-3 text-primary" style="font-size: 2.8rem;">
                    <i class="fa-solid fa-bed"></i>
                </div>
                <h2 class="h4 mb-2">Chưa có phòng nào</h2>
                <a href="{{ route($routePrefix . '.create') }}" class="btn btn-brand px-4">
                    <i class="fa-solid fa-plus me-1"></i>Đăng phòng
                </a>
            </div>
        @endif
    </section>

    <section class="panel-card p-4">
        <div class="panel-heading">
            <h2 class="h4 mb-0">{{ $isAdminArea ? 'Đơn đặt phòng' : 'Đơn đặt cho phòng của bạn' }}</h2>
        </div>

        @if(($roomBookings ?? collect())->count() > 0)
            <div class="table-responsive">
                <table class="table table-clean mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Khách hàng</th>
                            <th>Phòng</th>
                            <th>Lưu trú</th>
                            <th>Khách / phòng</th>
                            <th>Tổng tiền</th>
                            <th>Trạng thái</th>
                            <th>Ngày đặt</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($roomBookings as $booking)
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
                                    <div class="fw-semibold">{{ Str::limit($booking->room->title, 38) }}</div>
                                    <small class="text-muted">{{ $booking->room->hotel_name }}</small>
                                </td>
                                <td>
                                    <div>{{ $booking->check_in_date->format('d/m/Y') }} - {{ $booking->check_out_date->format('d/m/Y') }}</div>
                                    <small class="text-muted">{{ $booking->total_nights }} đêm</small>
                                </td>
                                <td>{{ $booking->number_of_guests }} / {{ $booking->number_of_rooms }}</td>
                                <td class="fw-semibold text-danger">{{ number_format($booking->total_price, 0, ',', '.') }} VND</td>
                                <td>
                                    <form method="POST" action="{{ route($roomBookingStatusRoute, $booking->id) }}" class="d-flex gap-2 align-items-center">
                                        @csrf
                                        <span class="{{ $currentStatusClass }}">
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
                {{ $roomBookings->links() }}
            </div>
        @else
            <div class="text-center py-4 page-note">Chưa có đơn đặt phòng nào.</div>
        @endif
    </section>
@endsection
