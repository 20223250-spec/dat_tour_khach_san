@extends('layouts.site')

@section('title', $tour->name . ' - TourBooking')

@php
    $averageRating = $tour->reviews->count() > 0 ? number_format((float) $tour->reviews->avg('rating'), 1) : null;
    $hasDeparted = \Carbon\Carbon::parse($tour->start_date)->startOfDay()->lte(now()->startOfDay());
@endphp

@push('styles')
    <style>
        .tour-cover {
            width: 100%;
            height: 340px;
            object-fit: cover;
            border-radius: 24px;
            border: 1px solid rgba(18, 34, 64, 0.1);
        }

        .tour-cover-placeholder {
            width: 100%;
            height: 340px;
            border-radius: 24px;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, rgba(15, 74, 214, 0.16), rgba(20, 164, 199, 0.16));
            color: #0f4ad6;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 0.9rem;
        }

        .detail-item {
            border-radius: 16px;
            padding: 0.95rem 1rem;
            background: rgba(15, 74, 214, 0.07);
        }

        .rating-input {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
            gap: 0.3rem;
        }

        .rating-input input {
            display: none;
        }

        .rating-input label {
            color: #cbd5e1;
            cursor: pointer;
            font-size: 1.45rem;
        }

        .rating-input input:checked ~ label,
        .rating-input label:hover,
        .rating-input label:hover ~ label {
            color: #f59e0b;
        }

        .review-item + .review-item {
            margin-top: 0.9rem;
        }

        .booking-summary {
            border-radius: 14px;
            border: 1px solid rgba(15, 74, 214, 0.2);
            background: rgba(15, 74, 214, 0.08);
            padding: 0.85rem 0.9rem;
        }
    </style>
@endpush

@section('hero')
    <div class="hero-panel p-4 p-lg-5">
        <div class="row g-4 align-items-center">
            <div class="col-lg-6">
                @if ($tour->image)
                    <img src="{{ \Illuminate\Support\Str::startsWith($tour->image, ['http://', 'https://']) ? $tour->image : asset($tour->image) }}" alt="{{ $tour->name }}" class="tour-cover">
                @else
                    <div class="tour-cover-placeholder">
                        <i class="fa-regular fa-image fa-4x"></i>
                    </div>
                @endif
            </div>
            <div class="col-lg-6">
                <span class="badge badge-soft mb-3 px-3 py-2">{{ $tour->destination }}</span>
                <h1 class="display-5 fw-bold mb-3">{{ $tour->name }}</h1>
                <p class="lead text-white-50 mb-4">{{ Str::limit($tour->description, 180) }}</p>

                <div class="d-flex flex-wrap gap-4 text-white-50 mb-4">
                    <span><i class="fa-regular fa-calendar me-2"></i>{{ \Carbon\Carbon::parse($tour->start_date)->format('d/m/Y') }}</span>
                    <span><i class="fa-solid fa-users me-2"></i>{{ $tour->available_seats }} chỗ</span>
                    <span><i class="fa-regular fa-clock me-2"></i>{{ $tour->duration_days }} ngày</span>
                </div>

                <div class="d-flex flex-wrap align-items-end gap-3">
                    <div>
                        <div class="display-6 fw-bold mb-0">{{ number_format($tour->price, 0, ',', '.') }} VND</div>
                        <small class="text-white-50">giá mỗi người</small>
                    </div>
                    @if($averageRating)
                        <span class="badge bg-light text-primary px-3 py-2">
                            <i class="fa-solid fa-star text-warning me-1"></i>{{ $averageRating }}/5
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="row g-4">
        <div class="col-lg-8">
            <section class="surface-card p-4 p-lg-5 mb-4">
                <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
                    <div>
                        <h2 class="section-title h3 mb-1">Thông tin tour</h2>
                        <p class="muted-copy mb-0">Thông tin chi tiết giúp bạn cân nhắc trước khi đặt chỗ.</p>
                    </div>
                    <a href="{{ route('home') }}" class="btn btn-soft">Quay lại danh sách</a>
                </div>

                <p class="mb-4">{{ $tour->description }}</p>

                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="text-primary fw-semibold mb-1">Điểm đến</div>
                        <div>{{ $tour->destination }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="text-primary fw-semibold mb-1">Khởi hành</div>
                        <div>{{ \Carbon\Carbon::parse($tour->start_date)->format('d/m/Y') }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="text-primary fw-semibold mb-1">Thời lượng</div>
                        <div>{{ $tour->duration_days }} ngày</div>
                    </div>
                    <div class="detail-item">
                        <div class="text-primary fw-semibold mb-1">Chỗ trống</div>
                        <div>{{ $tour->available_seats }} khách</div>
                    </div>
                </div>
            </section>

            <section class="surface-card p-4 p-lg-5">
                <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
                    <div>
                        <h2 class="section-title h3 mb-1">Đánh giá khách hàng</h2>
                        <p class="muted-copy mb-0">{{ $tour->reviews->count() }} đánh giá đã được ghi nhận.</p>
                    </div>
                    @if($averageRating)
                        <span class="badge badge-soft px-3 py-2">
                            <i class="fa-solid fa-star text-warning me-1"></i>{{ $averageRating }}/5 trung bình
                        </span>
                    @endif
                </div>

                @forelse ($tour->reviews as $review)
                    <article class="review-item border rounded-4 p-4">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div>
                                <div class="fw-semibold">{{ $review->user->name }}</div>
                                <div class="text-warning small mb-2">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <i class="fa-solid fa-star {{ $i <= $review->rating ? '' : 'text-secondary opacity-25' }}"></i>
                                    @endfor
                                </div>
                            </div>
                            <small class="muted-copy">{{ $review->created_at->diffForHumans() }}</small>
                        </div>
                        @if ($review->comment)
                            <p class="mb-0">{{ $review->comment }}</p>
                        @endif
                    </article>
                @empty
                    <div class="text-center py-4">
                        <div class="icon-pill mx-auto mb-3 text-primary">
                            <i class="fa-regular fa-comments"></i>
                        </div>
                        <h3 class="h5 mb-2">Chưa có đánh giá nào</h3>
                        <p class="muted-copy mb-0">Đánh giá sẽ hiển thị sau khi khách hoàn thành tour.</p>
                    </div>
                @endforelse
            </section>
        </div>

        <div class="col-lg-4">
            @auth
                
                    <section class="surface-card p-4 mb-4">
                        <h2 class="section-title h4 mb-3">Đặt tour ngay</h2>
                        @if($hasDeparted)
                            <div class="alert alert-warning rounded-4 border-0 mb-0">
                                Tour đã khởi hành, không thể đặt thêm.
                            </div>
                        @else
                            <p class="muted-copy mb-4">Thông tin đặt chỗ sẽ được lưu vào lịch sử và gửi email xác nhận.</p>

                            @if ($errors->any())
                                <div class="alert alert-danger rounded-4 border-0">
                                    <ul class="mb-0 ps-3">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('bookings.store', $tour->id) }}" class="d-grid gap-3">
                                @csrf
                                <div>
                                    <label for="customer_name" class="form-label fw-semibold">Họ tên người đi</label>
                                    <input id="customer_name" type="text" class="form-control" name="customer_name" value="{{ old('customer_name', auth()->user()->name) }}" required>
                                </div>
                                <div>
                                    <label for="customer_phone" class="form-label fw-semibold">Số điện thoại</label>
                                    <input id="customer_phone" type="text" class="form-control" name="customer_phone" value="{{ old('customer_phone') }}" inputmode="numeric" pattern="[0-9]{9,15}" maxlength="15" required>
                                </div>
                                <div>
                                    <label for="number_of_people" class="form-label fw-semibold">Số lượng người</label>
                                    <input id="number_of_people" type="number" class="form-control" name="number_of_people" min="1" max="{{ $tour->available_seats }}" value="{{ old('number_of_people', 1) }}" data-unit-price="{{ (float) $tour->price }}" data-max-seats="{{ (int) $tour->available_seats }}" required>
                                </div>
                                <div class="booking-summary">
                                    <div class="small text-muted">Đơn giá: {{ number_format($tour->price, 0, ',', '.') }} VND/người</div>
                                    <div class="fw-semibold mt-1">Tổng tiền tạm tính: <span id="booking_total_price">{{ number_format($tour->price * old('number_of_people', 1), 0, ',', '.') }} VND</span></div>
                                </div>
                                <button type="submit" class="btn btn-brand py-2">Xác nhận đặt tour</button>
                            </form>
                        @endif
                    </section>

                    <section class="surface-card p-4">
                        <h2 class="section-title h4 mb-3">Gửi đánh giá</h2>
                        @if ($tour->reviews->where('user_id', auth()->id())->isNotEmpty())
                            <p class="muted-copy mb-0">Bạn đã gửi đánh giá cho tour này.</p>
                        @else
                            <form method="POST" action="{{ route('reviews.store', $tour->id) }}" class="d-grid gap-3">
                                @csrf
                                <div>
                                    <label class="form-label fw-semibold">Số sao</label>
                                    <div class="rating-input">
                                        @for ($i = 5; $i >= 1; $i--)
                                            <input id="star{{ $i }}" type="radio" name="rating" value="{{ $i }}" {{ old('rating') == $i ? 'checked' : '' }} required>
                                            <label for="star{{ $i }}"><i class="fa-solid fa-star"></i></label>
                                        @endfor
                                    </div>
                                </div>
                                <div>
                                    <label for="comment" class="form-label fw-semibold">Nhận xét</label>
                                    <textarea id="comment" class="form-control" name="comment" rows="4" placeholder="Chia sẻ trải nghiệm của bạn...">{{ old('comment') }}</textarea>
                                </div>
                                <button type="submit" class="btn btn-soft py-2">Gửi đánh giá</button>
                            </form>
                        @endif
                    </section>
            @else
                <section class="surface-card p-4 text-center">
                    <div class="icon-pill mx-auto mb-3 text-primary">
                        <i class="fa-solid fa-right-to-bracket"></i>
                    </div>
                    <h2 class="h4 mb-2">Đăng nhập để tiếp tục</h2>
                    <p class="muted-copy mb-4">Bạn cần đăng nhập để đặt tour và quản lý lịch sử đặt.</p>
                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                        <a href="{{ route('login') }}" class="btn btn-brand px-4">Đăng nhập</a>
                        <a href="{{ route('register') }}" class="btn btn-soft px-4">Tạo tài khoản</a>
                    </div>
                </section>
            @endauth
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const numberOfPeopleInput = document.getElementById('number_of_people');
            const totalPriceElement = document.getElementById('booking_total_price');

            if (!numberOfPeopleInput || !totalPriceElement) {
                return;
            }

            const unitPrice = parseFloat(numberOfPeopleInput.dataset.unitPrice || '0');
            const maxSeats = parseInt(numberOfPeopleInput.dataset.maxSeats || '0', 10);
            const formatter = new Intl.NumberFormat('vi-VN');

            const updateTotalPrice = () => {
                let numberOfPeople = parseInt(numberOfPeopleInput.value, 10);

                if (Number.isNaN(numberOfPeople) || numberOfPeople < 1) {
                    numberOfPeople = 1;
                }

                if (numberOfPeople > maxSeats) {
                    numberOfPeople = maxSeats;
                }

                numberOfPeopleInput.value = numberOfPeople;
                const totalPrice = unitPrice * numberOfPeople;
                totalPriceElement.textContent = `${formatter.format(totalPrice)} VND`;
            };

            numberOfPeopleInput.addEventListener('input', updateTotalPrice);
            numberOfPeopleInput.addEventListener('change', updateTotalPrice);

            updateTotalPrice();
        })();
    </script>
@endpush



