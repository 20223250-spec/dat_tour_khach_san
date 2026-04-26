@extends('bo_cuc.quan_tri')

@section('title', 'Chỉnh sửa phòng - TourBooking')

@section('content')
    <section class="panel-header">
        <h1 class="h3 mb-2">Chỉnh sửa phòng #{{ $room->id }}</h1>
        <p class="mb-0 text-white-50">Cập nhật thông tin phòng, thay ảnh hoặc gán lại chủ khách sạn nếu cần.</p>
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

        <form method="POST" action="{{ route($routePrefix . '.update', $room->id) }}" enctype="multipart/form-data" class="row g-4">
            @csrf
            @method('PUT')

            <div class="col-md-6">
                <label for="title" class="form-label fw-semibold">Tiêu đề phòng</label>
                <input type="text" class="form-control" id="title" name="title" value="{{ old('title', $room->title) }}" required>
            </div>

            <div class="col-md-6">
                <label for="hotel_name" class="form-label fw-semibold">Tên khách sạn</label>
                <input type="text" class="form-control" id="hotel_name" name="hotel_name" value="{{ old('hotel_name', $room->hotel_name) }}" required>
            </div>

            @if($isAdminArea)
                <div class="col-md-6">
                    <label for="owner_id" class="form-label fw-semibold">Chủ khách sạn</label>
                    <select id="owner_id" name="owner_id" class="form-select" required>
                        <option value="">Chọn tài khoản chủ khách sạn</option>
                        @foreach($ownerOptions as $owner)
                            <option value="{{ $owner->id }}" @selected((string) old('owner_id', $room->owner_id) === (string) $owner->id)>{{ $owner->name }} - {{ $owner->roleLabel() }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="col-md-6">
                <label for="location" class="form-label fw-semibold">Vị trí</label>
                <input type="text" class="form-control" id="location" name="location" value="{{ old('location', $room->location) }}" required>
            </div>

            <div class="col-12">
                <label for="description" class="form-label fw-semibold">Mô tả</label>
                <textarea class="form-control" id="description" name="description" rows="4">{{ old('description', $room->description) }}</textarea>
            </div>

            <div class="col-md-4">
                <label for="price_per_night" class="form-label fw-semibold">Giá mỗi đêm (VND)</label>
                <input type="number" class="form-control" id="price_per_night" name="price_per_night" value="{{ old('price_per_night', $room->price_per_night) }}" min="0" required>
            </div>

            <div class="col-md-4">
                <label for="guest_capacity" class="form-label fw-semibold">Sức chứa</label>
                <input type="number" class="form-control" id="guest_capacity" name="guest_capacity" value="{{ old('guest_capacity', $room->guest_capacity) }}" min="1" required>
            </div>

            <div class="col-md-4">
                <label for="available_rooms" class="form-label fw-semibold">Số phòng trống</label>
                <input type="number" class="form-control" id="available_rooms" name="available_rooms" value="{{ old('available_rooms', $room->available_rooms) }}" min="0" required>
            </div>

            <div class="col-md-6">
                <label for="status" class="form-label fw-semibold">Trạng thái</label>
                <select id="status" name="status" class="form-select" required>
                    <option value="active" @selected(old('status', $room->status) === 'active')>Hiển thị</option>
                    <option value="hidden" @selected(old('status', $room->status) === 'hidden')>Ẩn tạm thời</option>
                </select>
            </div>

            <div class="col-md-6">
                <label for="image" class="form-label fw-semibold">Hình ảnh phòng</label>
                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                <small class="text-muted d-block mt-2">Có thể tải ảnh mới để thay thế ảnh cũ.</small>

                @if($room->image_url)
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" value="1" id="remove_image" name="remove_image">
                        <label class="form-check-label" for="remove_image">
                            Xóa ảnh hiện tại
                        </label>
                    </div>
                    <div class="mt-3">
                        <small class="text-muted d-block mb-2">Ảnh hiện tại:</small>
                        <img id="image-preview" src="{{ $room->image_url }}" alt="{{ $room->title }}" class="img-fluid rounded-3 border" style="max-width: 260px;">
                    </div>
                @else
                    <div class="mt-3">
                        <img id="image-preview" src="" alt="Xem trước ảnh phòng" class="img-fluid rounded-3 border d-none" style="max-width: 260px;">
                    </div>
                @endif
            </div>

            <div class="col-12 d-flex justify-content-end gap-2 pt-2">
                <a href="{{ route($routePrefix . '.index') }}" class="btn btn-ghost px-4">Hủy</a>
                <button type="submit" class="btn btn-brand px-4">Cập nhật</button>
            </div>
        </form>
    </section>
@endsection

@push('scripts')
    <script>
        document.getElementById('image')?.addEventListener('change', function (event) {
            const [file] = event.target.files;
            const preview = document.getElementById('image-preview');

            if (!file || !preview) {
                return;
            }

            preview.src = URL.createObjectURL(file);
            preview.classList.remove('d-none');
        });
    </script>
@endpush

