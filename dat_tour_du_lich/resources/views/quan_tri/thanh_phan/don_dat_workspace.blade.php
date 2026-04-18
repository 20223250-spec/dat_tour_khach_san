<div class="workspace-panel" data-admin-panel="don-dat">
    <section class="section-stack">
        <section class="panel-card p-4">
            <div class="panel-heading">
                <h2 class="h4 mb-0">Quan ly don dat tour</h2>
                <a href="{{ $adminHomeTabUrl('bao-cao') }}" class="btn btn-soft btn-sm" data-admin-switch="bao-cao">Chuyen sang bao cao</a>
            </div>

            <div class="status-grid mb-4">
                <article class="quick-item">
                    <div class="label">Cho xac nhan</div>
                    <div class="value">{{ number_format($stats['pending_bookings'], 0, ',', '.') }}</div>
                </article>
                <article class="quick-item">
                    <div class="label">Da xac nhan</div>
                    <div class="value">{{ number_format($stats['confirmed_bookings'], 0, ',', '.') }}</div>
                </article>
                <article class="quick-item">
                    <div class="label">Da huy</div>
                    <div class="value">{{ number_format($stats['cancelled_bookings'], 0, ',', '.') }}</div>
                </article>
                <article class="quick-item">
                    <div class="label">Hoan tat</div>
                    <div class="value">{{ number_format($stats['completed_bookings'], 0, ',', '.') }}</div>
                </article>
            </div>

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
                        @forelse($adminBookings as $booking)
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
                                <td>
                                    <form method="POST" action="{{ route('admin.bookings.update-status', $booking->id) }}" class="d-flex gap-2 align-items-center">
                                        @csrf
                                        <span class="{{ $currentStatusClass }}">
                                            @if($booking->status === 'pending')
                                                Cho xac nhan
                                            @elseif($booking->status === 'confirmed')
                                                Da xac nhan
                                            @elseif($booking->status === 'cancelled')
                                                Da huy
                                            @else
                                                Hoan tat
                                            @endif
                                        </span>
                                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                            <option value="pending" {{ $booking->status === 'pending' ? 'selected' : '' }}>Cho xac nhan</option>
                                            <option value="confirmed" {{ $booking->status === 'confirmed' ? 'selected' : '' }}>Da xac nhan</option>
                                            <option value="cancelled" {{ $booking->status === 'cancelled' ? 'selected' : '' }}>Da huy</option>
                                            <option value="completed" {{ $booking->status === 'completed' ? 'selected' : '' }}>Hoan tat</option>
                                        </select>
                                    </form>
                                </td>
                                <td>{{ $booking->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 page-note">Chua co don dat tour nao.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="panel-card p-4">
            <div class="panel-heading">
                <h2 class="h4 mb-0">Quan ly don dat phong</h2>
                <span class="badge badge-soft px-3 py-2">{{ number_format($stats['total_room_bookings'], 0, ',', '.') }} don</span>
            </div>

            <div class="status-grid mb-4">
                <article class="quick-item">
                    <div class="label">Cho xac nhan</div>
                    <div class="value">{{ number_format($stats['pending_room_bookings'], 0, ',', '.') }}</div>
                </article>
                <article class="quick-item">
                    <div class="label">Da xac nhan</div>
                    <div class="value">{{ number_format($stats['confirmed_room_bookings'], 0, ',', '.') }}</div>
                </article>
                <article class="quick-item">
                    <div class="label">Da huy</div>
                    <div class="value">{{ number_format($stats['cancelled_room_bookings'], 0, ',', '.') }}</div>
                </article>
                <article class="quick-item">
                    <div class="label">Hoan tat</div>
                    <div class="value">{{ number_format($stats['completed_room_bookings'], 0, ',', '.') }}</div>
                </article>
            </div>

            <div class="table-responsive">
                <table class="table table-clean mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Khach hang</th>
                            <th>Phong</th>
                            <th>Lich luu tru</th>
                            <th>Khach / phong</th>
                            <th>Tong tien</th>
                            <th>Trang thai</th>
                            <th>Ngay dat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($adminRoomBookings as $booking)
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
                                    <small class="text-muted">{{ $booking->total_nights }} dem</small>
                                </td>
                                <td>{{ $booking->number_of_guests }} / {{ $booking->number_of_rooms }}</td>
                                <td class="fw-semibold text-danger">{{ number_format($booking->total_price, 0, ',', '.') }} VND</td>
                                <td>
                                    <form method="POST" action="{{ route('admin.room-bookings.update-status', $booking->id) }}" class="d-flex gap-2 align-items-center">
                                        @csrf
                                        <span class="{{ $currentStatusClass }}">
                                            @if($booking->status === 'pending')
                                                Cho xac nhan
                                            @elseif($booking->status === 'confirmed')
                                                Da xac nhan
                                            @elseif($booking->status === 'cancelled')
                                                Da huy
                                            @else
                                                Hoan tat
                                            @endif
                                        </span>
                                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                            <option value="pending" {{ $booking->status === 'pending' ? 'selected' : '' }}>Cho xac nhan</option>
                                            <option value="confirmed" {{ $booking->status === 'confirmed' ? 'selected' : '' }}>Da xac nhan</option>
                                            <option value="cancelled" {{ $booking->status === 'cancelled' ? 'selected' : '' }}>Da huy</option>
                                            <option value="completed" {{ $booking->status === 'completed' ? 'selected' : '' }}>Hoan tat</option>
                                        </select>
                                    </form>
                                </td>
                                <td>{{ $booking->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 page-note">Chua co don dat phong nao.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </section>
</div>
