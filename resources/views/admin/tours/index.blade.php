@extends('layouts.admin')

@section('title', 'Quản lý tour - TourBooking Admin')

@section('content')
    <section class="panel-header">
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
            <div>
                <h1 class="h3 mb-2">Quản lý danh sách tour</h1>
                <p class="mb-0 text-white-50">Cập nhật thông tin tour, giá và lịch khởi hành ngay trong một bảng.</p>
            </div>
            <div class="d-flex flex-wrap gap-2 align-self-start">
                <a href="{{ route('admin.tours.create') }}" class="btn btn-light text-primary fw-semibold">
                    <i class="fa-solid fa-plus me-1"></i>Thêm tour
                </a>
            </div>
        </div>
    </section>

    <section class="panel-card p-4">
        <form method="GET" action="{{ route('admin.tours.index') }}" class="row g-3 mb-4 align-items-end">
            <div class="col-md-3">
                <label for="id" class="form-label fw-semibold">Tìm theo ID</label>
                <input type="number" class="form-control" id="id" name="id" value="{{ request('id') }}" placeholder="Ví dụ: 12" min="1">
            </div>
            <div class="col-md-3">
                <label for="start_date" class="form-label fw-semibold">Tìm theo ngày khởi hành</label>
                <input type="date" class="form-control" id="start_date" name="start_date" value="{{ request('start_date') }}">
            </div>
            <div class="col-md-4">
                <label for="destination" class="form-label fw-semibold">Tìm theo địa điểm</label>
                <input type="text" class="form-control" id="destination" name="destination" value="{{ request('destination') }}" placeholder="Ví dụ: Đà Nẵng, Sapa">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-brand flex-grow-1">
                    <i class="fa-solid fa-magnifying-glass me-1"></i>Tìm kiếm
                </button>
                <a href="{{ route('admin.tours.index') }}" class="btn btn-ghost">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>
        </form>

        @if($tours->count() > 0)
            <div class="table-responsive">
                <table class="table table-clean mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tour</th>
                            <th>Điểm đến</th>
                            <th>Giá</th>
                            <th>Chỗ trống</th>
                            <th>Khởi hành</th>
                            <th class="text-end">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tours as $tour)
                            <tr>
                                <td>#{{ $loop->iteration + (($tours->currentPage() - 1) * $tours->perPage()) }}</td>
                                <td>
                                    <div class="fw-semibold">{{ Str::limit($tour->name, 42) }}</div>
                                    <small class="text-muted">{{ Str::limit($tour->description, 56) }}</small>
                                </td>
                                <td>{{ $tour->destination }}</td>
                                <td class="fw-semibold text-danger">{{ number_format($tour->price, 0, ',', '.') }} VND</td>
                                <td>{{ $tour->available_seats }}</td>
                                <td>{{ \Carbon\Carbon::parse($tour->start_date)->format('d/m/Y') }}</td>
                                <td>
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('admin.tours.edit', $tour->id) }}" class="btn btn-soft btn-sm">
                                            <i class="fa-solid fa-pen-to-square me-1"></i>Sửa
                                        </a>
                                        <form method="POST" action="{{ route('admin.tours.destroy', $tour->id) }}" onsubmit="return confirm('Bạn có chắc muốn xóa tour này?')">
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
                <p class="page-note mb-4">Hãy tạo tour đầu tiên để bắt đầu bán trên hệ thống.</p>
                <a href="{{ route('admin.tours.create') }}" class="btn btn-brand px-4">
                    <i class="fa-solid fa-plus me-1"></i>Tạo tour
                </a>
            </div>
        @endif
    </section>
@endsection


