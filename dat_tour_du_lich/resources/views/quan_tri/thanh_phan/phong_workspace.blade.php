<div class="workspace-panel" data-admin-panel="phong">
    <section class="embedded-card">
        <div class="panel-heading">
            <h2 class="h4 mb-0">Đăng phòng</h2>
            <button class="btn btn-soft" type="button" data-bs-toggle="collapse" data-bs-target="#roomCreateCollapse" aria-expanded="{{ $roomCreateHasErrors ? 'true' : 'false' }}" aria-controls="roomCreateCollapse">
                <i class="fa-solid fa-plus me-1"></i>Mở form đăng phòng
            </button>
        </div>

        <div class="collapse {{ $roomCreateHasErrors ? 'show' : '' }}" id="roomCreateCollapse">
            @if($roomCreateHasErrors)
                <div class="alert alert-danger rounded-4 border-0">
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.rooms.store') }}" enctype="multipart/form-data" class="row g-4">
                @csrf
                <input type="hidden" name="admin_active_tab" value="phong">
                <input type="hidden" name="admin_context" value="room-create">

                <div class="col-md-6">
                    <label for="room_create_title" class="form-label fw-semibold">Tiêu đề phòng</label>
                    <input type="text" class="form-control" id="room_create_title" name="title" value="{{ $adminContext === 'room-create' ? old('title') : '' }}" required>
                </div>

                <div class="col-md-6">
                    <label for="room_create_hotel" class="form-label fw-semibold">Tên khách sạn</label>
                    <input type="text" class="form-control" id="room_create_hotel" name="hotel_name" value="{{ $adminContext === 'room-create' ? old('hotel_name') : '' }}" required>
                </div>

                <div class="col-md-6">
                    <label for="room_create_owner" class="form-label fw-semibold">Chủ khách sạn</label>
                    <select id="room_create_owner" name="owner_id" class="form-select" required>
                        <option value="">Chọn tài khoản chủ khách sạn</option>
                        @foreach($hotelOwnerOptions as $owner)
                            <option value="{{ $owner->id }}" @selected($adminContext === 'room-create' && (string) old('owner_id') === (string) $owner->id)>{{ $owner->name }} - {{ $owner->roleLabel() }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="room_create_location" class="form-label fw-semibold">Vị trí</label>
                    <input type="text" class="form-control" id="room_create_location" name="location" value="{{ $adminContext === 'room-create' ? old('location') : '' }}" required>
                </div>

                <div class="col-12">
                    <label for="room_create_description" class="form-label fw-semibold">Mô tả</label>
                    <textarea class="form-control" id="room_create_description" name="description" rows="4">{{ $adminContext === 'room-create' ? old('description') : '' }}</textarea>
                </div>

                <div class="col-md-4">
                    <label for="room_create_price" class="form-label fw-semibold">Giá mỗi đêm (VND)</label>
                    <input type="number" class="form-control" id="room_create_price" name="price_per_night" value="{{ $adminContext === 'room-create' ? old('price_per_night') : '' }}" min="0" required>
                </div>

                <div class="col-md-4">
                    <label for="room_create_capacity" class="form-label fw-semibold">Sức chứa</label>
                    <input type="number" class="form-control" id="room_create_capacity" name="guest_capacity" value="{{ $adminContext === 'room-create' ? old('guest_capacity', 2) : 2 }}" min="1" required>
                </div>

                <div class="col-md-4">
                    <label for="room_create_available" class="form-label fw-semibold">Số phòng trống</label>
                    <input type="number" class="form-control" id="room_create_available" name="available_rooms" value="{{ $adminContext === 'room-create' ? old('available_rooms', 1) : 1 }}" min="0" required>
                </div>

                <div class="col-md-6">
                    <label for="room_create_status" class="form-label fw-semibold">Trạng thái</label>
                    <select id="room_create_status" name="status" class="form-select" required>
                        <option value="active" @selected($adminContext === 'room-create' ? old('status', 'active') === 'active' : true)>Hiển thị</option>
                        <option value="hidden" @selected($adminContext === 'room-create' && old('status') === 'hidden')>Ẩn tạm thời</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="room_create_image" class="form-label fw-semibold">Hình ảnh phòng</label>
                    <input type="file" class="form-control" id="room_create_image" name="image" accept="image/*" data-preview-target="room-create-preview">
                    <small class="text-muted d-block mt-2">Định dạng JPG, PNG, GIF, WEBP. Tối đa 4MB.</small>
                    <div class="mt-3">
                        <img id="room-create-preview" src="" alt="Xem trước ảnh phòng" class="img-fluid rounded-3 border d-none" style="max-width: 260px;">
                    </div>
                </div>

                <div class="col-12 d-flex justify-content-end">
                    <button type="submit" class="btn btn-brand px-4">Lưu phòng</button>
                </div>
            </form>
        </div>
    </section>

    <section class="embedded-card">
        <div class="panel-heading">
            <h2 class="h4 mb-0">Danh sách phòng</h2>
        </div>

        <div class="table-responsive">
            <table class="table table-clean mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ảnh</th>
                        <th>Tiêu đề</th>
                        <th>Chủ khách sạn</th>
                        <th>Khách sạn</th>
                        <th>Vị trí</th>
                        <th>Giá/đêm</th>
                        <th>Trạng thái</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($adminRooms as $room)
                        <tr>
                            <td>#{{ $room->id }}</td>
                            <td>
                                @if($room->image_url)
                                    <img src="{{ $room->image_url }}" alt="{{ $room->title }}" class="rounded-3 border" style="width: 72px; height: 52px; object-fit: cover;">
                                @else
                                    <div class="image-thumb-placeholder">
                                        <i class="fa-solid fa-bed"></i>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold">{{ Str::limit($room->title, 42) }}</div>
                                <small class="text-muted">{{ Str::limit($room->description, 56) }}</small>
                            </td>
                            <td>{{ $room->owner?->name ?? 'Hệ thống' }}</td>
                            <td>{{ $room->hotel_name }}</td>
                            <td>{{ $room->location }}</td>
                            <td class="fw-semibold text-danger">{{ number_format($room->price_per_night, 0, ',', '.') }} VND</td>
                            <td>
                                <span class="chip {{ $room->status === 'active' ? 'chip-confirmed' : 'chip-cancelled' }}">
                                    {{ $room->status === 'active' ? 'Đang hiển thị' : 'Đang ẩn' }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex justify-content-end gap-2">
                                    <button type="button" class="btn btn-soft btn-sm" data-bs-toggle="modal" data-bs-target="#roomEditModal{{ $room->id }}">
                                        <i class="fa-solid fa-pen-to-square me-1"></i>Sửa
                                    </button>
                                    <form method="POST" action="{{ route('admin.rooms.destroy', $room->id) }}" onsubmit="return confirm('Bạn có chắc muốn xóa phòng này?')">
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
                            <td colspan="9" class="text-center py-4 page-note">Chưa có phòng nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @foreach($adminRooms as $room)
        @php
            $roomUsesOldInput = $adminContext === 'room-edit' && (string) $adminItemId === (string) $room->id;
        @endphp
        <div class="modal fade" id="roomEditModal{{ $room->id }}" tabindex="-1" aria-labelledby="roomEditModalLabel{{ $room->id }}" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h2 class="modal-title h5 mb-1" id="roomEditModalLabel{{ $room->id }}">Chỉnh sửa phòng #{{ $room->id }}</h2>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" action="{{ route('admin.rooms.update', $room->id) }}" enctype="multipart/form-data">
                        <div class="modal-body">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="admin_active_tab" value="phong">
                            <input type="hidden" name="admin_context" value="room-edit">
                            <input type="hidden" name="admin_item_id" value="{{ $room->id }}">

                            @if($roomUsesOldInput && $errors->any())
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
                                    <label class="form-label fw-semibold">Tiêu đề phòng</label>
                                    <input type="text" class="form-control" name="title" value="{{ $roomUsesOldInput ? old('title') : $room->title }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Tên khách sạn</label>
                                    <input type="text" class="form-control" name="hotel_name" value="{{ $roomUsesOldInput ? old('hotel_name') : $room->hotel_name }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Chủ khách sạn</label>
                                    <select name="owner_id" class="form-select" required>
                                        <option value="">Chọn tài khoản chủ khách sạn</option>
                                        @foreach($hotelOwnerOptions as $owner)
                                            @php
                                                $selectedOwner = $roomUsesOldInput ? old('owner_id') : $room->owner_id;
                                            @endphp
                                            <option value="{{ $owner->id }}" @selected((string) $selectedOwner === (string) $owner->id)>{{ $owner->name }} - {{ $owner->roleLabel() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Vị trí</label>
                                    <input type="text" class="form-control" name="location" value="{{ $roomUsesOldInput ? old('location') : $room->location }}" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Mô tả</label>
                                    <textarea class="form-control" name="description" rows="4">{{ $roomUsesOldInput ? old('description') : $room->description }}</textarea>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Giá mỗi đêm (VND)</label>
                                    <input type="number" class="form-control" name="price_per_night" value="{{ $roomUsesOldInput ? old('price_per_night') : $room->price_per_night }}" min="0" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Sức chứa</label>
                                    <input type="number" class="form-control" name="guest_capacity" value="{{ $roomUsesOldInput ? old('guest_capacity') : $room->guest_capacity }}" min="1" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Số phòng trống</label>
                                    <input type="number" class="form-control" name="available_rooms" value="{{ $roomUsesOldInput ? old('available_rooms') : $room->available_rooms }}" min="0" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Trạng thái</label>
                                    <select name="status" class="form-select" required>
                                        @php
                                            $selectedStatus = $roomUsesOldInput ? old('status') : $room->status;
                                        @endphp
                                        <option value="active" @selected($selectedStatus === 'active')>Hiển thị</option>
                                        <option value="hidden" @selected($selectedStatus === 'hidden')>Ẩn tạm thời</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Hình ảnh phòng</label>
                                    <input type="file" class="form-control" name="image" accept="image/*" data-preview-target="room-preview-{{ $room->id }}">
                                    <small class="text-muted d-block mt-2">Có thể tải ảnh mới để thay thế ảnh cũ.</small>

                                    @if($room->image_url)
                                        <div class="form-check mt-3">
                                            <input class="form-check-input" type="checkbox" value="1" id="room_remove_image_{{ $room->id }}" name="remove_image">
                                            <label class="form-check-label" for="room_remove_image_{{ $room->id }}">Xóa ảnh hiện tại</label>
                                        </div>
                                    @endif

                                    <div class="mt-3">
                                        <img id="room-preview-{{ $room->id }}" src="{{ $room->image_url ?? '' }}" alt="{{ $room->title }}" class="img-fluid rounded-3 border {{ $room->image_url ? '' : 'd-none' }}" style="max-width: 260px;">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Đóng</button>
                            <button type="submit" class="btn btn-brand">Cập nhật phòng</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
</div>
