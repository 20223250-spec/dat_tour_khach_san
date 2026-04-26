@extends('layouts.admin')

@section('title', 'Thêm tour - TourBooking Admin')

@section('content')
    <section class="panel-header">
        <h1 class="h3 mb-2">Tạo tour mới</h1>
        <p class="mb-0 text-white-50">Nhập đầy đủ thông tin để tour có thể hiển thị ngay trên trang đặt.</p>
    </section>

    <section class="panel-card p-4 p-lg-5">
        @if($errors->any())
            <div class="alert alert-danger rounded-4 border-0">
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.tours.store') }}" enctype="multipart/form-data" class="row g-4">
            @csrf

            <div class="col-md-6">
                <label for="name" class="form-label fw-semibold">Tên tour</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
            </div>

            <div class="col-md-6">
                <label for="destination" class="form-label fw-semibold">Điểm đến</label>
                <input type="text" class="form-control" id="destination" name="destination" value="{{ old('destination') }}" required>
            </div>

            <div class="col-12">
                <label for="description" class="form-label fw-semibold">Mô tả</label>
                <textarea class="form-control" id="description" name="description" rows="4">{{ old('description') }}</textarea>
            </div>

            <div class="col-md-4">
                <label for="price" class="form-label fw-semibold">Giá (VND)</label>
                <input type="number" class="form-control" id="price" name="price" value="{{ old('price') }}" min="0" required>
            </div>

            <div class="col-md-4">
                <label for="duration_days" class="form-label fw-semibold">Thời gian (ngày)</label>
                <input type="number" class="form-control" id="duration_days" name="duration_days" value="{{ old('duration_days') }}" min="1" required>
            </div>

            <div class="col-md-4">
                <label for="available_seats" class="form-label fw-semibold">Số chỗ trống</label>
                <input type="number" class="form-control" id="available_seats" name="available_seats" value="{{ old('available_seats') }}" min="0" required>
            </div>

            <div class="col-md-6">
                <label for="start_date" class="form-label fw-semibold">Ngày khởi hành</label>
                <input type="date" class="form-control" id="start_date" name="start_date" value="{{ old('start_date') }}" required>
            </div>

            <div class="col-md-6">
                <label for="image" class="form-label fw-semibold">Hình ảnh tour</label>
                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                <small class="text-muted">Định dạng: JPG, PNG, GIF. Tối đa 2MB.</small>
            </div>

            <div class="col-12 d-flex justify-content-end gap-2 pt-2">
                <a href="{{ route('admin.tours.index') }}" class="btn btn-ghost px-4">Hủy</a>
                <button type="submit" class="btn btn-brand px-4">Tạo tour</button>
            </div>
        </form>
    </section>
@endsection


