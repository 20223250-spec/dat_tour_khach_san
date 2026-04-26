@extends('layouts.admin')

@section('title', 'Hộp thư khách hàng - TourBooking Admin')

@section('content')
    <section class="panel-header">
        <h1 class="h3 mb-2">Hộp thư khách hàng</h1>
        <p class="mb-0 text-white-50">Danh sách khách hàng đã liên hệ với quản trị viên.</p>
    </section>

    <section class="panel-card p-4">
        @if($users->isEmpty())
            <p class="page-note mb-0">Chưa có hội thoại nào.</p>
        @else
            <div class="table-responsive">
                <table class="table table-clean mb-0">
                    <thead>
                        <tr>
                            <th>Khách hàng</th>
                            <th>Email</th>
                            <th>Tin chưa đọc</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr>
                                <td class="fw-semibold">{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @if(($user->unread_chat_count ?? 0) > 0)
                                        <span class="chip chip-cancelled">{{ $user->unread_chat_count }} tin mới</span>
                                    @else
                                        <span class="chip">0</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.chat.show', $user->id) }}" class="btn btn-soft btn-sm">
                                        Mở hội thoại
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
