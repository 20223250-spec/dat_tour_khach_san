@extends('layouts.admin')

@section('title', 'Chỉnh sửa tour - TourBooking Admin')

@section('content')
    <section class="panel-header">
        <h1 class="h3 mb-2">Chỉnh sửa tour #{{ $tour->id }}</h1>
        <p class="mb-0 text-white-50">Cập nhật thông tin tour để giữ dữ liệu mới nhất trên hệ thống.</p>
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

        <form method="POST" action="{{ route('admin.tours.update', $tour->id) }}" enctype="multipart/form-data" class="row g-4">
            @csrf
            @method('PUT')

            <div class="col-md-6">
                <label for="name" class="form-label fw-semibold">Tên tour</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $tour->name) }}" required>
            </div>

            <div class="col-md-6">
                <label for="destination" class="form-label fw-semibold">Điểm đến</label>
                <input type="text" class="form-control" id="destination" name="destination" value="{{ old('destination', $tour->destination) }}" required>
            </div>

            <div class="col-12">
                <label for="description" class="form-label fw-semibold">Mô tả</label>
                <textarea class="form-control" id="description" name="description" rows="4">{{ old('description', $tour->description) }}</textarea>
            </div>

            <div class="col-md-4">
                <label for="price" class="form-label fw-semibold">Giá (VND)</label>
                <input type="number" class="form-control" id="price" name="price" value="{{ old('price', $tour->price) }}" min="0" required>
            </div>

            <div class="col-md-4">
                <label for="duration_days" class="form-label fw-semibold">Thời gian (ngày)</label>
                <input type="number" class="form-control" id="duration_days" name="duration_days" value="{{ old('duration_days', $tour->duration_days) }}" min="1" required>
            </div>

            <div class="col-md-4">
                <label for="available_seats" class="form-label fw-semibold">Số chỗ trống</label>
                <input type="number" class="form-control" id="available_seats" name="available_seats" value="{{ old('available_seats', $tour->available_seats) }}" min="0" required>
            </div>

            <div class="col-md-6">
                <label for="start_date" class="form-label fw-semibold">Ngày khởi hành</label>
                <input type="date" class="form-control" id="start_date" name="start_date" value="{{ old('start_date', $tour->start_date) }}" required>
            </div>

            <div class="col-md-6">
                <label for="image" class="form-label fw-semibold">Hình ảnh tour</label>
                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                @if($tour->image)
                    <small class="text-muted d-block mt-2">Ảnh hiện tại:</small>
                    <img src="{{ \Illuminate\Support\Str::startsWith($tour->image, ['http://', 'https://']) ? $tour->image : asset($tour->image) }}" alt="{{ $tour->name }}" class="img-fluid rounded-3 border mt-1" style="max-width: 220px;">
                @endif
            </div>

            <div class="col-12 d-flex justify-content-end gap-2 pt-2">
                <a href="{{ route('admin.tours.index') }}" class="btn btn-ghost px-4">Hủy</a>
                <button type="submit" class="btn btn-brand px-4">Cập nhật</button>
            </div>
        </form>
    </section>
@endsection


