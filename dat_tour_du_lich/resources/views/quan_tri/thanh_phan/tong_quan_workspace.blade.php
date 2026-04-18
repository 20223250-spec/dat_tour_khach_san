<div class="workspace-panel is-active" data-admin-panel="tong-quan">
    <section class="quick-grid mb-4">
        <article class="quick-item">
            <div class="label">Tong tour</div>
            <div class="value">{{ number_format($stats['total_tours'], 0, ',', '.') }}</div>
        </article>
        <article class="quick-item">
            <div class="label">Tong phong</div>
            <div class="value">{{ number_format($stats['total_rooms'], 0, ',', '.') }}</div>
        </article>
        <article class="quick-item">
            <div class="label">Don tour</div>
            <div class="value">{{ number_format($stats['total_bookings'], 0, ',', '.') }}</div>
        </article>
        <article class="quick-item">
            <div class="label">Don phong</div>
            <div class="value">{{ number_format($stats['total_room_bookings'], 0, ',', '.') }}</div>
        </article>
        <article class="quick-item">
            <div class="label">Tong nguoi dung</div>
            <div class="value">{{ number_format($stats['total_users'], 0, ',', '.') }}</div>
        </article>
        <article class="quick-item">
            <div class="label">Chu tour</div>
            <div class="value">{{ number_format($stats['total_tour_owners'], 0, ',', '.') }}</div>
        </article>
        <article class="quick-item">
            <div class="label">Chu khach san</div>
            <div class="value">{{ number_format($stats['total_hotel_owners'], 0, ',', '.') }}</div>
        </article>
        <article class="quick-item">
            <div class="label">Doanh thu tour</div>
            <div class="value">{{ number_format($stats['total_revenue'], 0, ',', '.') }} VND</div>
        </article>
        <article class="quick-item">
            <div class="label">Doanh thu phong</div>
            <div class="value">{{ number_format($stats['total_room_revenue'], 0, ',', '.') }} VND</div>
        </article>
    </section>

    <section class="shortcut-grid mb-4">
        <article class="shortcut-card">
            <span class="eyebrow"><i class="fa-solid fa-receipt"></i>Don dat</span>
            <h2 class="h5 mb-2">Don dat</h2>
            <a href="{{ $adminHomeTabUrl('don-dat') }}" class="btn btn-soft" data-admin-switch="don-dat">Mo panel don dat</a>
        </article>
        <article class="shortcut-card">
            <span class="eyebrow"><i class="fa-solid fa-route"></i>Tour</span>
            <h2 class="h5 mb-2">Tour</h2>
            <a href="{{ $adminHomeTabUrl('tour-du-lich') }}" class="btn btn-soft" data-admin-switch="tour-du-lich" data-admin-open="tour-create">Mo panel tour</a>
        </article>
        <article class="shortcut-card">
            <span class="eyebrow"><i class="fa-solid fa-bed"></i>Phong</span>
            <h2 class="h5 mb-2">Phong</h2>
            <a href="{{ $adminHomeTabUrl('phong') }}" class="btn btn-soft" data-admin-switch="phong" data-admin-open="room-create">Mo panel phong</a>
        </article>
    </section>

    <section class="panel-card p-4 mb-4">
        <div class="panel-heading">
            <h2 class="h4 mb-0">Don dat tour gan day</h2>
            <a href="{{ $adminHomeTabUrl('don-dat') }}" class="btn btn-soft btn-sm" data-admin-switch="don-dat">Mo panel don dat</a>
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
                    @forelse($recent_bookings as $booking)
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
                            <td colspan="7" class="text-center py-4 page-note">Chua co don dat tour nao.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="panel-card p-4">
        <div class="panel-heading">
            <h2 class="h4 mb-0">Don dat phong gan day</h2>
            <a href="{{ $adminHomeTabUrl('don-dat') }}" class="btn btn-soft btn-sm" data-admin-switch="don-dat">Mo panel don dat</a>
        </div>

        <div class="table-responsive">
            <table class="table table-clean mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Khach hang</th>
                        <th>Phong</th>
                        <th>So khach / phong</th>
                        <th>Tong tien</th>
                        <th>Trang thai</th>
                        <th>Ngay dat</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentRoomBookings as $booking)
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
                            <td>
                                <div class="fw-semibold">{{ $booking->customer_name }}</div>
                                <small class="text-muted">{{ $booking->customer_phone }}</small>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ Str::limit($booking->room->title, 36) }}</div>
                                <small class="text-muted">{{ $booking->room->hotel_name }}</small>
                            </td>
                            <td>{{ $booking->number_of_guests }} / {{ $booking->number_of_rooms }}</td>
                            <td class="fw-semibold text-danger">{{ number_format($booking->total_price, 0, ',', '.') }} VND</td>
                            <td><span class="{{ $chipClass }}">{{ $statusLabel }}</span></td>
                            <td>{{ $booking->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 page-note">Chua co don dat phong nao.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
