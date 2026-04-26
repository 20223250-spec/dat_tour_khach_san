@extends('layouts.site')

@section('title', 'Thông báo - TourBooking')

@php
    $iconMap = [
        'booking_received' => ['wrapper' => 'bg-primary-subtle text-primary', 'icon' => 'fa-regular fa-clock'],
        'booking_confirmed' => ['wrapper' => 'bg-success-subtle text-success', 'icon' => 'fa-solid fa-circle-check'],
        'booking_cancelled' => ['wrapper' => 'bg-danger-subtle text-danger', 'icon' => 'fa-solid fa-circle-xmark'],
        'booking_auto_cancelled' => ['wrapper' => 'bg-danger-subtle text-danger', 'icon' => 'fa-solid fa-triangle-exclamation'],
        'departure_reminder' => ['wrapper' => 'bg-warning-subtle text-warning', 'icon' => 'fa-regular fa-bell'],
        'payment_received' => ['wrapper' => 'bg-success-subtle text-success', 'icon' => 'fa-solid fa-credit-card'],
        'payment_failed' => ['wrapper' => 'bg-danger-subtle text-danger', 'icon' => 'fa-solid fa-credit-card'],
        'tour_updated' => ['wrapper' => 'bg-info-subtle text-info', 'icon' => 'fa-solid fa-pen'],
    ];
@endphp

@section('hero')
    <div class="hero-panel p-4 p-lg-5">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <span class="badge badge-soft mb-3 px-3 py-2">Trung tâm thông báo</span>
                <h1 class="display-6 fw-bold mb-3">Theo dõi cập nhật đơn đặt và tour theo thời gian thực.</h1>
                <p class="lead text-white-50 mb-0">Đánh dấu đã đọc, xóa thông báo cũ và giữ lại các mục quan trọng.</p>
            </div>
            <div class="col-lg-4">
                <div class="surface-card p-4">
                    <h2 class="h5 section-title mb-2 text-dark">Tổng thông báo</h2>
                    <p class="muted-copy mb-3">{{ $notifications->total() }} mục hiện có.</p>
                    @if ($notifications->where('is_read', false)->count() > 0)
                        <form method="POST" action="{{ route('notifications.mark-all-read') }}">
                            @csrf
                            <button type="submit" class="btn btn-brand w-100">Đánh dấu tất cả đã đọc</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    @forelse ($notifications as $notification)
        @php
            $icon = $iconMap[$notification->type] ?? ['wrapper' => 'bg-secondary-subtle text-secondary', 'icon' => 'fa-regular fa-bell'];
        @endphp
        <article class="surface-card p-4 mb-3 {{ $notification->is_read ? '' : 'border-primary border-2' }}">
            <div class="d-flex flex-column flex-lg-row gap-3 justify-content-between align-items-lg-center">
                <div class="d-flex gap-3 align-items-start">
                    <div class="icon-pill {{ $icon['wrapper'] }}">
                        <i class="{{ $icon['icon'] }}"></i>
                    </div>
                    <div>
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                            <h2 class="h5 mb-0">{{ $notification->title }}</h2>
                            @if (! $notification->is_read)
                                <span class="badge bg-primary">Mới</span>
                            @endif
                        </div>
                        <p class="muted-copy mb-1">{{ $notification->message }}</p>
                        <small class="text-secondary">{{ $notification->created_at->diffForHumans() }}</small>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    @if (! $notification->is_read)
                        <form method="POST" action="{{ route('notifications.mark-read', $notification->id) }}">
                            @csrf
                            <button type="submit" class="btn btn-soft">Đánh dấu đã đọc</button>
                        </form>
                    @endif

                    <form method="POST" action="{{ route('notifications.destroy', $notification->id) }}" onsubmit="return confirm('Bạn có chắc muốn xóa thông báo này?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-ghost">Xóa</button>
                    </form>
                </div>
            </div>
        </article>
    @empty
        <div class="surface-card p-5 text-center">
            <div class="icon-pill mx-auto mb-3 text-primary">
                <i class="fa-regular fa-bell-slash"></i>
            </div>
            <h2 class="h4 mb-2">Chưa có thông báo nào</h2>
            <p class="muted-copy mb-0">Khi có thay đổi về đơn đặt hoặc tour, hệ thống sẽ hiển thị tại đây.</p>
        </div>
    @endforelse

    <div class="d-flex justify-content-center mt-4">
        {{ $notifications->links() }}
    </div>
@endsection


