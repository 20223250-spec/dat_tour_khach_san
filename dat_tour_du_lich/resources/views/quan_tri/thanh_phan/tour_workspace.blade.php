<div class="workspace-panel" data-admin-panel="tour-du-lich">
    <section class="embedded-card">
        <div class="panel-heading">
            <h2 class="h4 mb-0">Tạo tour</h2>
            <button class="btn btn-soft" type="button" data-bs-toggle="collapse" data-bs-target="#tourCreateCollapse" aria-expanded="{{ $tourCreateHasErrors ? 'true' : 'false' }}" aria-controls="tourCreateCollapse">
                <i class="fa-solid fa-plus me-1"></i>Mở form tạo tour
            </button>
        </div>

        <div class="collapse {{ $tourCreateHasErrors ? 'show' : '' }}" id="tourCreateCollapse">
            @if($tourCreateHasErrors)
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
                <input type="hidden" name="admin_active_tab" value="tour-du-lich">
                <input type="hidden" name="admin_context" value="tour-create">

                <div class="col-md-6">
                    <label for="tour_create_name" class="form-label fw-semibold">Tên tour</label>
                    <input type="text" class="form-control" id="tour_create_name" name="name" value="{{ $adminContext === 'tour-create' ? old('name') : '' }}" required>
                </div>

                <div class="col-md-6">
                    <label for="tour_create_destination" class="form-label fw-semibold">Điểm đến</label>
                    <input type="text" class="form-control" id="tour_create_destination" name="destination" value="{{ $adminContext === 'tour-create' ? old('destination') : '' }}" required>
                </div>

                <div class="col-md-6">
                    <label for="tour_create_owner" class="form-label fw-semibold">Chủ tour</label>
                    <select id="tour_create_owner" name="owner_id" class="form-select" required>
                        <option value="">Chọn tài khoản chủ tour</option>
                        @foreach($tourOwnerOptions as $owner)
                            <option value="{{ $owner->id }}" @selected($adminContext === 'tour-create' && (string) old('owner_id') === (string) $owner->id)>{{ $owner->name }} - {{ $owner->roleLabel() }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12">
                    <label for="tour_create_description" class="form-label fw-semibold">Mô tả</label>
                    <textarea class="form-control" id="tour_create_description" name="description" rows="4">{{ $adminContext === 'tour-create' ? old('description') : '' }}</textarea>
                </div>

                <div class="col-md-4">
                    <label for="tour_create_price" class="form-label fw-semibold">Giá (VND)</label>
                    <input type="number" class="form-control" id="tour_create_price" name="price" value="{{ $adminContext === 'tour-create' ? old('price') : '' }}" min="0" required>
                </div>

                <div class="col-md-4">
                    <label for="tour_create_duration" class="form-label fw-semibold">Thời gian (ngày)</label>
                    <input type="number" class="form-control" id="tour_create_duration" name="duration_days" value="{{ $adminContext === 'tour-create' ? old('duration_days') : '' }}" min="1" required>
                </div>

                <div class="col-md-4">
                    <label for="tour_create_seats" class="form-label fw-semibold">Số chỗ trống</label>
                    <input type="number" class="form-control" id="tour_create_seats" name="available_seats" value="{{ $adminContext === 'tour-create' ? old('available_seats') : '' }}" min="0" required>
                </div>

                <div class="col-md-6">
                    <label for="tour_create_start" class="form-label fw-semibold">Ngày khởi hành</label>
                    <input type="date" class="form-control" id="tour_create_start" name="start_date" value="{{ $adminContext === 'tour-create' ? old('start_date') : '' }}" required>
                </div>

                <div class="col-md-6">
                    <label for="tour_create_image" class="form-label fw-semibold">Hình ảnh tour</label>
                    <input type="file" class="form-control" id="tour_create_image" name="image" accept="image/*" data-preview-target="tour-create-preview">
                    <small class="text-muted d-block mt-2">Định dạng JPG, PNG, GIF, WEBP. Tối đa 4MB.</small>
                    <div class="mt-3">
                        <img id="tour-create-preview" src="" alt="Xem trước ảnh tour" class="img-fluid rounded-3 border d-none" style="max-width: 260px;">
                    </div>
                </div>

                <div class="col-12 d-flex justify-content-end">
                    <button type="submit" class="btn btn-brand px-4">Lưu tour</button>
                </div>
            </form>
        </div>
    </section>

    <section class="embedded-card">
        <div class="panel-heading">
            <h2 class="h4 mb-0">Danh sách tour</h2>
        </div>

        <div class="table-responsive">
            <table class="table table-clean mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ảnh</th>
                        <th>Tour</th>
                        <th>Chủ tour</th>
                        <th>Điểm đến</th>
                        <th>Giá</th>
                        <th>Chỗ trống</th>
                        <th>Khởi hành</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($adminTours as $tour)
                        <tr>
                            <td>#{{ $tour->id }}</td>
                            <td>
                                @if($tour->image_url)
                                    <img src="{{ $tour->image_url }}" alt="{{ $tour->name }}" class="rounded-3 border" style="width: 72px; height: 52px; object-fit: cover;">
                                @else
                                    <div class="image-thumb-placeholder">
                                        <i class="fa-regular fa-image"></i>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold">{{ Str::limit($tour->name, 42) }}</div>
                                <small class="text-muted">{{ Str::limit($tour->description, 56) }}</small>
                            </td>
                            <td>{{ $tour->owner?->name ?? 'Hệ thống' }}</td>
                            <td>{{ $tour->destination }}</td>
                            <td class="fw-semibold text-danger">{{ number_format($tour->price, 0, ',', '.') }} VND</td>
                            <td>{{ $tour->available_seats }}</td>
                            <td>{{ \Carbon\Carbon::parse($tour->start_date)->format('d/m/Y') }}</td>
                            <td>
                                <div class="d-flex justify-content-end gap-2">
                                    <button type="button" class="btn btn-soft btn-sm" data-bs-toggle="modal" data-bs-target="#tourEditModal{{ $tour->id }}">
                                        <i class="fa-solid fa-pen-to-square me-1"></i>Sửa
                                    </button>
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
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 page-note">Chưa có tour nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @foreach($adminTours as $tour)
        @php
            $tourUsesOldInput = $adminContext === 'tour-edit' && (string) $adminItemId === (string) $tour->id;
        @endphp
        <div class="modal fade" id="tourEditModal{{ $tour->id }}" tabindex="-1" aria-labelledby="tourEditModalLabel{{ $tour->id }}" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h2 class="modal-title h5 mb-1" id="tourEditModalLabel{{ $tour->id }}">Chỉnh sửa tour #{{ $tour->id }}</h2>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" action="{{ route('admin.tours.update', $tour->id) }}" enctype="multipart/form-data">
                        <div class="modal-body">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="admin_active_tab" value="tour-du-lich">
                            <input type="hidden" name="admin_context" value="tour-edit">
                            <input type="hidden" name="admin_item_id" value="{{ $tour->id }}">

                            @if($tourUsesOldInput && $errors->any())
                                <div class="alert alert-danger rounded-4 border-0">
                                    <ul class="mb-0 ps-3">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Tên tour</label>
                                    <input type="text" class="form-control" name="name" value="{{ $tourUsesOldInput ? old('name') : $tour->name }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Điểm đến</label>
                                    <input type="text" class="form-control" name="destination" value="{{ $tourUsesOldInput ? old('destination') : $tour->destination }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Chủ tour</label>
                                    <select name="owner_id" class="form-select" required>
                                        <option value="">Chọn tài khoản chủ tour</option>
                                        @foreach($tourOwnerOptions as $owner)
                                            @php
                                                $selectedOwner = $tourUsesOldInput ? old('owner_id') : $tour->owner_id;
                                            @endphp
                                            <option value="{{ $owner->id }}" @selected((string) $selectedOwner === (string) $owner->id)>{{ $owner->name }} - {{ $owner->roleLabel() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Mô tả</label>
                                    <textarea class="form-control" name="description" rows="4">{{ $tourUsesOldInput ? old('description') : $tour->description }}</textarea>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Giá (VND)</label>
                                    <input type="number" class="form-control" name="price" value="{{ $tourUsesOldInput ? old('price') : $tour->price }}" min="0" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Thời gian (ngày)</label>
                                    <input type="number" class="form-control" name="duration_days" value="{{ $tourUsesOldInput ? old('duration_days') : $tour->duration_days }}" min="1" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Số chỗ trống</label>
                                    <input type="number" class="form-control" name="available_seats" value="{{ $tourUsesOldInput ? old('available_seats') : $tour->available_seats }}" min="0" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Ngày khởi hành</label>
                                    <input type="date" class="form-control" name="start_date" value="{{ $tourUsesOldInput ? old('start_date') : $tour->start_date }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Hình ảnh tour</label>
                                    <input type="file" class="form-control" name="image" accept="image/*" data-preview-target="tour-preview-{{ $tour->id }}">
                                    <small class="text-muted d-block mt-2">Có thể tải ảnh mới để thay thế ảnh cũ.</small>

                                    @if($tour->image_url)
                                        <div class="form-check mt-3">
                                            <input class="form-check-input" type="checkbox" value="1" id="tour_remove_image_{{ $tour->id }}" name="remove_image">
                                            <label class="form-check-label" for="tour_remove_image_{{ $tour->id }}">Xóa ảnh hiện tại</label>
                                        </div>
                                    @endif

                                    <div class="mt-3">
                                        <img id="tour-preview-{{ $tour->id }}" src="{{ $tour->image_url ?? '' }}" alt="{{ $tour->name }}" class="img-fluid rounded-3 border {{ $tour->image_url ? '' : 'd-none' }}" style="max-width: 260px;">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Đóng</button>
                            <button type="submit" class="btn btn-brand">Cập nhật tour</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
</div>
