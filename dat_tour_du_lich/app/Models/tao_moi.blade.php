@extends('bo_cuc.quan_tri')

@section('title', ($isAdminArea ? 'Thêm tour' : 'Đăng tour mới') . ' - TourBooking')

@section('content')
    <section class="panel-header">
        <h1 class="h3 mb-0">{{ $isAdminArea ? 'Tạo tour mới' : 'Đăng tour mới' }}</h1>
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

        <form method="POST" action="{{ route($routePrefix . '.store') }}" enctype="multipart/form-data" class="row g-4">
            @csrf

            <div class="col-md-6">
                <label for="name" class="form-label fw-semibold">Tên tour</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
            </div>

            <div class="col-md-6">
                <label for="destination" class="form-label fw-semibold">Điểm đến</label>
                <input type="text" class="form-control" id="destination" name="destination" value="{{ old('destination') }}" required>
            </div>

            @if($isAdminArea)
                <div class="col-md-6">
                    <label for="owner_id" class="form-label fw-semibold">Chủ tour</label>
                    <select id="owner_id" name="owner_id" class="form-select" required>
                        <option value="">Chọn tài khoản chủ tour</option>
                        @foreach($ownerOptions as $owner)
                            <option value="{{ $owner->id }}" @selected((string) old('owner_id') === (string) $owner->id)>{{ $owner->name }} - {{ $owner->roleLabel() }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted d-block mt-2">Admin chỉ quản trị và phân công, không đứng tên tour.</small>
                </div>
            @endif

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
                <label for="total_seats" class="form-label fw-semibold">Tổng sức chứa</label>
                <input type="number" class="form-control" id="total_seats" name="total_seats" value="{{ old('total_seats', old('available_seats')) }}" min="0" required>
            </div>

            <div class="col-md-6">
                <label for="start_date" class="form-label fw-semibold">Ngày khởi hành</label>
                <input type="date" class="form-control" id="start_date" name="start_date" value="{{ old('start_date') }}" required>
            </div>

            <div class="col-md-6">
                <label for="image" class="form-label fw-semibold">Hình ảnh tour</label>
                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                <small class="text-muted d-block mt-2">Định dạng: JPG, PNG, GIF, WEBP. Tối đa 4MB.</small>
                <div class="mt-3">
                    <img id="image-preview" src="" alt="Xem trước ảnh tour" class="img-fluid rounded-3 border d-none" style="max-width: 260px;">
                </div>
            </div>

            <div class="col-12 d-flex justify-content-end gap-2 pt-2">
                <a href="{{ route($routePrefix . '.index') }}" class="btn btn-ghost px-4">Hủy</a>
                <button type="submit" class="btn btn-brand px-4">Lưu tour</button>
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

