@extends('layouts.site')

@section('title', 'Chat với quản trị viên - TourBooking')

@section('hero')
    <div class="hero-panel p-4 p-lg-5">
        <div class="row g-3 align-items-center">
            <div class="col-lg-8">
                <span class="badge badge-soft mb-3 px-3 py-2">Hỗ trợ khách hàng</span>
                <h1 class="display-6 fw-bold mb-2">Chat với quản trị viên</h1>
                <p class="lead text-white-50 mb-0">Trao đổi trực tiếp về booking, thanh toán và lịch trình tour.</p>
            </div>
            <div class="col-lg-4">
                <div class="surface-card p-3">
                    <div class="small text-muted">Bạn đang chat với</div>
                    <div class="fw-semibold text-dark">{{ $admin->name }}</div>
                    <small class="text-muted">{{ $admin->email }}</small>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <section class="surface-card p-4 p-lg-5">
        <div class="border rounded-4 p-3 mb-3" style="max-height: 460px; overflow-y: auto; background: #f8fbff;">
            @forelse($messages as $message)
                @php
                    $isMine = $message->sender_id === auth()->id();
                    $bubbleClass = $isMine ? 'text-white bg-primary border-0' : 'bg-white border';
                @endphp
                <div class="d-flex mb-3 {{ $isMine ? 'justify-content-end' : 'justify-content-start' }}">
                    <div class="p-3 rounded-4 {{ $bubbleClass }}" style="max-width: 76%; border-color: rgba(17,34,64,.12);">
                        <div class="small {{ $isMine ? 'text-white-50' : 'text-muted' }} mb-1">
                            {{ $isMine ? 'Bạn' : ($message->sender->name ?? 'Quản trị viên') }}
                        </div>
                        <div>{{ $message->message }}</div>
                        <div class="small mt-1 {{ $isMine ? 'text-white-50' : 'text-muted' }}">
                            {{ $message->created_at->format('d/m/Y H:i') }}
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-muted mb-0">Chưa có tin nhắn nào. Bạn hãy gửi câu hỏi đầu tiên.</p>
            @endforelse
        </div>

        <form method="POST" action="{{ route('chat.send') }}" class="d-grid gap-2">
            @csrf
            <label for="message" class="form-label fw-semibold mb-0">Nội dung tin nhắn</label>
            <textarea id="message" name="message" rows="3" maxlength="1500" class="form-control" placeholder="Nhập nội dung cần hỗ trợ..." required>{{ old('message') }}</textarea>
            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-brand">Gửi cho quản trị viên</button>
            </div>
        </form>
    </section>
@endsection
