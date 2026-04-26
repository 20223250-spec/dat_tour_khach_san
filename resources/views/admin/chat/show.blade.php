@extends('layouts.admin')

@section('title', 'Chat khách hàng - TourBooking Admin')

@section('content')
    <section class="panel-header d-flex flex-column flex-lg-row justify-content-between gap-3">
        <div>
            <h1 class="h3 mb-2">Trao đổi với {{ $user->name }}</h1>
            <p class="mb-0 text-white-50">{{ $user->email }}</p>
        </div>
        <a href="{{ route('admin.chat.index') }}" class="btn btn-light text-primary fw-semibold align-self-start">Quay lại hộp thư</a>
    </section>

    <section class="panel-card p-4">
        <div class="border rounded-4 p-3 mb-3" style="max-height: 500px; overflow-y: auto; background: #f8fbff;">
            @forelse($messages as $message)
                @php
                    $isAdmin = $message->sender_id === auth()->id();
                    $bubbleClass = $isAdmin ? 'text-white bg-primary border-0' : 'bg-white border';
                @endphp
                <div class="d-flex mb-3 {{ $isAdmin ? 'justify-content-end' : 'justify-content-start' }}">
                    <div class="p-3 rounded-4 {{ $bubbleClass }}" style="max-width: 76%; border-color: rgba(18,35,63,.12);">
                        <div class="small {{ $isAdmin ? 'text-white-50' : 'text-muted' }} mb-1">
                            {{ $isAdmin ? 'Quản trị viên' : $user->name }}
                        </div>
                        <div>{{ $message->message }}</div>
                        <div class="small mt-1 {{ $isAdmin ? 'text-white-50' : 'text-muted' }}">{{ $message->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                </div>
            @empty
                <p class="page-note mb-0">Khách hàng chưa gửi tin nhắn nào.</p>
            @endforelse
        </div>

        <form method="POST" action="{{ route('admin.chat.send', $user->id) }}" class="d-grid gap-2">
            @csrf
            <label for="message" class="form-label fw-semibold mb-0">Phản hồi khách hàng</label>
            <textarea id="message" name="message" rows="3" maxlength="1500" class="form-control" placeholder="Nhập phản hồi..." required>{{ old('message') }}</textarea>
            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-brand">Gửi phản hồi</button>
            </div>
        </form>
    </section>
@endsection
