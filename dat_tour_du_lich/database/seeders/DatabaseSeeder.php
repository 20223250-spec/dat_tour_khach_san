<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Notification;
use App\Models\Review;
use App\Models\Room;
use App\Models\RoomBooking;
use App\Models\Tour;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->clearExistingData();

        $users = $this->seedUsers();
        $tours = $this->seedTours($users['tour_owners']);
        $rooms = $this->seedRooms($users['hotel_owners']);

        $bookings = $this->seedBookings($users['customers'], $tours['items']);
        $roomBookings = $this->seedRoomBookings($users['customers'], $rooms['items']);

        $this->syncAvailableSeats($tours['items'], $tours['capacities'], $bookings);
        $this->syncAvailableRooms($rooms['items'], $rooms['capacities'], $roomBookings);
        $this->seedReviews($bookings);
        $this->seedNotifications($bookings, $tours['items'], $roomBookings, $rooms['items']);
    }

    private function clearExistingData(): void
    {
        $tables = [
            'notifications',
            'reviews',
            'bookings',
            'room_bookings',
            'rooms',
            'tours',
            'sessions',
            'password_reset_tokens',
            'users',
        ];

        Schema::disableForeignKeyConstraints();

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
            }
        }

        Schema::enableForeignKeyConstraints();
    }

    private function seedUsers(): array
    {
        $password = Hash::make('12345678');

        $definitions = [
            'admin' => ['name' => 'Quan tri he thong', 'email' => 'admin@demo.local', 'role' => User::ROLE_ADMIN, 'is_admin' => true],
            'tour_owner_1' => ['name' => 'Nguyen Minh Travel', 'email' => 'chutour1@demo.local', 'role' => User::ROLE_TOUR_OWNER, 'is_admin' => false],
            'tour_owner_2' => ['name' => 'Tran An Tour', 'email' => 'chutour2@demo.local', 'role' => User::ROLE_TOUR_OWNER, 'is_admin' => false],
            'tour_owner_3' => ['name' => 'Le Bao Adventure', 'email' => 'chutour3@demo.local', 'role' => User::ROLE_TOUR_OWNER, 'is_admin' => false],
            'hotel_owner_1' => ['name' => 'Blue Ocean Hotel', 'email' => 'chukhachsan1@demo.local', 'role' => User::ROLE_HOTEL_OWNER, 'is_admin' => false],
            'hotel_owner_2' => ['name' => 'Sapa Valley Lodge', 'email' => 'chukhachsan2@demo.local', 'role' => User::ROLE_HOTEL_OWNER, 'is_admin' => false],
            'hotel_owner_3' => ['name' => 'Hoi An Riverside', 'email' => 'chukhachsan3@demo.local', 'role' => User::ROLE_HOTEL_OWNER, 'is_admin' => false],
            'customer_1' => ['name' => 'Pham Thu Ha', 'email' => 'khach1@demo.local', 'role' => User::ROLE_CUSTOMER, 'is_admin' => false],
            'customer_2' => ['name' => 'Do Van Nam', 'email' => 'khach2@demo.local', 'role' => User::ROLE_CUSTOMER, 'is_admin' => false],
            'customer_3' => ['name' => 'Le Ngoc Anh', 'email' => 'khach3@demo.local', 'role' => User::ROLE_CUSTOMER, 'is_admin' => false],
            'customer_4' => ['name' => 'Tran Gia Linh', 'email' => 'khach4@demo.local', 'role' => User::ROLE_CUSTOMER, 'is_admin' => false],
            'customer_5' => ['name' => 'Vu Tuan Kiet', 'email' => 'khach5@demo.local', 'role' => User::ROLE_CUSTOMER, 'is_admin' => false],
        ];

        $users = collect($definitions)->map(
            fn (array $user) => User::query()->create([
                'name' => $user['name'],
                'email' => $user['email'],
                'password' => $password,
                'email_verified_at' => now(),
                'is_admin' => $user['is_admin'],
                'role' => $user['role'],
                'remember_token' => Str::random(10),
            ])
        );

        return [
            'all' => $users,
            'tour_owners' => $users->only(['tour_owner_1', 'tour_owner_2', 'tour_owner_3']),
            'hotel_owners' => $users->only(['hotel_owner_1', 'hotel_owner_2', 'hotel_owner_3']),
            'customers' => $users->only(['customer_1', 'customer_2', 'customer_3', 'customer_4', 'customer_5']),
        ];
    }

    private function seedTours(Collection $tourOwners): array
    {
        $tourImages = [
            'uploads/tours/demo-tour-01.svg',
            'uploads/tours/demo-tour-02.svg',
            'uploads/tours/demo-tour-03.svg',
        ];

        $definitions = [
            'di_san_mien_trung' => ['owner' => 'tour_owner_1', 'name' => 'Di san Da Nang - Hoi An 3N2D', 'destination' => 'Da Nang - Hoi An', 'description' => 'Lich trinh danh cho khach muon tham quan pho co Hoi An, ban dao Son Tra va thuong thuc am thuc mien Trung trong 3 ngay 2 dem.', 'price' => 3290000, 'duration_days' => 3, 'capacity' => 26, 'start_date' => now()->addDays(12)->toDateString()],
            'ha_noi_ha_long' => ['owner' => 'tour_owner_1', 'name' => 'Ha Noi - Ha Long cuoi tuan', 'destination' => 'Ha Noi - Ha Long', 'description' => 'Tour phu hop gia dinh, ket hop tham quan Ha Noi, nghi du thuyen va check-in cac diem noi bat tai Ha Long.', 'price' => 2890000, 'duration_days' => 2, 'capacity' => 22, 'start_date' => now()->addDays(18)->toDateString()],
            'sapa_san_may' => ['owner' => 'tour_owner_2', 'name' => 'Sapa san may va ban Cat Cat', 'destination' => 'Sa Pa', 'description' => 'Hanh trinh dua khach len cac diem ngam canh dep nhat Sapa, ket hop trekking nhe va giao luu van hoa dia phuong.', 'price' => 3590000, 'duration_days' => 3, 'capacity' => 18, 'start_date' => now()->addDays(15)->toDateString()],
            'phu_quoc_nghi_duong' => ['owner' => 'tour_owner_2', 'name' => 'Phu Quoc nghi duong 4N3D', 'destination' => 'Phu Quoc', 'description' => 'Goi nghi duong bien dao cao cap, tham quan Sunset Town, cap treo Hon Thom va kham pha am thuc Phu Quoc.', 'price' => 6490000, 'duration_days' => 4, 'capacity' => 30, 'start_date' => now()->addDays(28)->toDateString()],
            'da_lat_san_may' => ['owner' => 'tour_owner_3', 'name' => 'Da Lat san may va vuon hoa', 'destination' => 'Da Lat', 'description' => 'Tour chill cho nhom ban tre, ket hop san may, tham quan vuon hoa, cafe view doi va cac diem check-in noi bat cua Da Lat.', 'price' => 2790000, 'duration_days' => 3, 'capacity' => 24, 'start_date' => now()->addDays(22)->toDateString()],
            'quy_nhon_bien_xanh' => ['owner' => 'tour_owner_3', 'name' => 'Quy Nhon bien xanh ky niem', 'destination' => 'Quy Nhon', 'description' => 'Chuong trinh tham quan Ky Co, Eo Gio va cac bai bien dep nhat Quy Nhon voi lich trinh gon, de di va nhieu anh dep.', 'price' => 3190000, 'duration_days' => 3, 'capacity' => 20, 'start_date' => now()->addDays(35)->toDateString()],
            'hue_di_san' => ['owner' => 'tour_owner_1', 'name' => 'Hue di san co do', 'destination' => 'Hue', 'description' => 'Tour da tung mo ban cho mua du lich truoc, phu hop de test danh gia va don dat da hoan thanh trong he thong.', 'price' => 2490000, 'duration_days' => 2, 'capacity' => 16, 'start_date' => now()->subDays(21)->toDateString()],
            'can_tho_song_nuoc' => ['owner' => 'tour_owner_2', 'name' => 'Can Tho cho noi song nuoc', 'destination' => 'Can Tho', 'description' => 'Hanh trinh tham quan cho noi Cai Rang, nha co Binh Thuy va cac trai nghiem dac trung vung song nuoc mien Tay.', 'price' => 2390000, 'duration_days' => 2, 'capacity' => 18, 'start_date' => now()->subDays(10)->toDateString()],
            'ninh_binh_non_nuoc' => ['owner' => 'tour_owner_3', 'name' => 'Ninh Binh non nuoc huu tinh', 'destination' => 'Ninh Binh', 'description' => 'Tour ngam canh Trang An, chua Bai Dinh va Tam Coc. Du lieu nay giup test luong tour da ket thuc va thong ke doanh thu.', 'price' => 2190000, 'duration_days' => 2, 'capacity' => 14, 'start_date' => now()->subDays(6)->toDateString()],
            'nha_trang_lang_bien' => ['owner' => 'tour_owner_1', 'name' => 'Nha Trang lan bien va du thuyen', 'destination' => 'Nha Trang', 'description' => 'Tour ket hop tam bun, du thuyen, lang ngam san ho va nghi duong cho nhom gia dinh hoac cong ty.', 'price' => 4190000, 'duration_days' => 3, 'capacity' => 28, 'start_date' => now()->addDays(30)->toDateString()],
            'moc_chau_hoa_mua_he' => ['owner' => 'tour_owner_2', 'name' => 'Moc Chau hoa va doi che', 'destination' => 'Moc Chau', 'description' => 'Lich trinh ngam doi che, thac Dai Yem va cac diem song ao dep, phu hop cho khach muon di tour ngan ngay tu Ha Noi.', 'price' => 2690000, 'duration_days' => 2, 'capacity' => 19, 'start_date' => now()->addDays(40)->toDateString()],
            'buon_ma_thuot_ca_phe' => ['owner' => 'tour_owner_3', 'name' => 'Buon Ma Thuot mua ca phe', 'destination' => 'Buon Ma Thuot', 'description' => 'Tour kham pha van hoa Tay Nguyen, thuong thuc ca phe, tham quan bao tang va cac diem du lich sinh thai dac sac.', 'price' => 3090000, 'duration_days' => 3, 'capacity' => 21, 'start_date' => now()->addDays(26)->toDateString()],
        ];

        $capacities = [];
        $imageIndex = 0;

        $tours = collect($definitions)->map(function (array $tour, string $key) use ($tourOwners, $tourImages, &$capacities, &$imageIndex) {
            $capacities[$key] = $tour['capacity'];

            return Tour::query()->create([
                'owner_id' => $tourOwners[$tour['owner']]->id,
                'name' => $tour['name'],
                'description' => $tour['description'],
                'destination' => $tour['destination'],
                'price' => $tour['price'],
                'duration_days' => $tour['duration_days'],
                'available_seats' => $tour['capacity'],
                'start_date' => $tour['start_date'],
                'image' => $tourImages[$imageIndex++ % count($tourImages)],
            ]);
        });

        return ['items' => $tours, 'capacities' => $capacities];
    }

    private function seedRooms(Collection $hotelOwners): array
    {
        $roomImages = [
            'uploads/rooms/demo-room-01.svg',
            'uploads/rooms/demo-room-02.svg',
            'uploads/rooms/demo-room-03.svg',
        ];

        $definitions = [
            'blue_deluxe_bien' => ['owner' => 'hotel_owner_1', 'title' => 'Phong Deluxe huong bien', 'hotel_name' => 'Blue Ocean Hotel', 'location' => 'Da Nang', 'description' => 'Phong rong, co ban cong huong bien, phu hop cap doi hoac gia dinh nho muon nghi duong gan bai bien.', 'price_per_night' => 1250000, 'guest_capacity' => 2, 'available_rooms' => 6, 'status' => 'active'],
            'blue_suite_gia_dinh' => ['owner' => 'hotel_owner_1', 'title' => 'Suite gia dinh premium', 'hotel_name' => 'Blue Ocean Hotel', 'location' => 'Da Nang', 'description' => 'Phong suite co khu tiep khach rieng, phu hop gia dinh 4 nguoi va nhom khach muon khong gian rong rai.', 'price_per_night' => 1890000, 'guest_capacity' => 4, 'available_rooms' => 3, 'status' => 'active'],
            'sapa_view_nui' => ['owner' => 'hotel_owner_2', 'title' => 'Phong view nui co may sua', 'hotel_name' => 'Sapa Valley Lodge', 'location' => 'Sa Pa', 'description' => 'Phong huong nui, noi that go, thich hop khach muon nghi duong yen tinh va ngam canh thung lung.', 'price_per_night' => 980000, 'guest_capacity' => 2, 'available_rooms' => 5, 'status' => 'active'],
            'sapa_doi_tieu_chuan' => ['owner' => 'hotel_owner_2', 'title' => 'Phong doi tieu chuan', 'hotel_name' => 'Sapa Valley Lodge', 'location' => 'Sa Pa', 'description' => 'Phong gia hop ly de test luong an hien phong trong khu doi tac va quan tri he thong.', 'price_per_night' => 760000, 'guest_capacity' => 2, 'available_rooms' => 2, 'status' => 'hidden'],
            'hoian_riverside' => ['owner' => 'hotel_owner_3', 'title' => 'Phong Riverside balcony', 'hotel_name' => 'Hoi An Riverside', 'location' => 'Hoi An', 'description' => 'Phong co ban cong nhin ra song, de di bo vao pho co va thuan tien cho khach thich khong gian nhe nhang.', 'price_per_night' => 1120000, 'guest_capacity' => 2, 'available_rooms' => 4, 'status' => 'active'],
            'hoian_gia_dinh' => ['owner' => 'hotel_owner_3', 'title' => 'Phong gia dinh ket noi', 'hotel_name' => 'Hoi An Riverside', 'location' => 'Hoi An', 'description' => 'Hai gian phong lien thong, phu hop gia dinh 4-5 nguoi, duoc dang san de admin kiem tra vai tro chu khach san.', 'price_per_night' => 1740000, 'guest_capacity' => 5, 'available_rooms' => 2, 'status' => 'active'],
            'blue_thanh_pho' => ['owner' => 'hotel_owner_1', 'title' => 'Phong huong thanh pho', 'hotel_name' => 'Blue Ocean Hotel', 'location' => 'Da Nang', 'description' => 'Phong co cua so lon, gia mem, phu hop khach cong tac hoac khach di tour ngan ngay.', 'price_per_night' => 890000, 'guest_capacity' => 2, 'available_rooms' => 7, 'status' => 'active'],
            'sapa_premium' => ['owner' => 'hotel_owner_2', 'title' => 'Phong premium tam go', 'hotel_name' => 'Sapa Valley Lodge', 'location' => 'Sa Pa', 'description' => 'Phong tieu chuan cao, tam go ngam nui, thich hop cap doi muon trai nghiem ky nghi lanh va yen tinh.', 'price_per_night' => 1450000, 'guest_capacity' => 3, 'available_rooms' => 3, 'status' => 'active'],
        ];

        $imageIndex = 0;
        $capacities = [];
        $rooms = collect();

        foreach ($definitions as $key => $room) {
            $capacities[$key] = $room['available_rooms'];

            $rooms[$key] = Room::query()->create([
                'owner_id' => $hotelOwners[$room['owner']]->id,
                'title' => $room['title'],
                'hotel_name' => $room['hotel_name'],
                'location' => $room['location'],
                'description' => $room['description'],
                'price_per_night' => $room['price_per_night'],
                'guest_capacity' => $room['guest_capacity'],
                'available_rooms' => $room['available_rooms'],
                'status' => $room['status'],
                'image' => $roomImages[$imageIndex++ % count($roomImages)],
            ]);
        }

        return ['items' => $rooms, 'capacities' => $capacities];
    }

    private function seedBookings(Collection $customers, Collection $tours): Collection
    {
        $definitions = [
            ['customer' => 'customer_1', 'tour' => 'di_san_mien_trung', 'number_of_people' => 2, 'status' => 'confirmed', 'customer_name' => 'Pham Thu Ha', 'customer_phone' => '0901000001', 'created_at' => now()->subDays(14)],
            ['customer' => 'customer_1', 'tour' => 'hue_di_san', 'number_of_people' => 3, 'status' => 'completed', 'customer_name' => 'Pham Thu Ha', 'customer_phone' => '0901000001', 'created_at' => now()->subDays(38)],
            ['customer' => 'customer_1', 'tour' => 'nha_trang_lang_bien', 'number_of_people' => 2, 'status' => 'pending', 'customer_name' => 'Pham Thu Ha', 'customer_phone' => '0901000001', 'created_at' => now()->subDays(4)],
            ['customer' => 'customer_2', 'tour' => 'sapa_san_may', 'number_of_people' => 2, 'status' => 'confirmed', 'customer_name' => 'Do Van Nam', 'customer_phone' => '0901000002', 'created_at' => now()->subDays(9)],
            ['customer' => 'customer_2', 'tour' => 'can_tho_song_nuoc', 'number_of_people' => 1, 'status' => 'completed', 'customer_name' => 'Do Van Nam', 'customer_phone' => '0901000002', 'created_at' => now()->subDays(24)],
            ['customer' => 'customer_2', 'tour' => 'phu_quoc_nghi_duong', 'number_of_people' => 4, 'status' => 'pending', 'customer_name' => 'Do Van Nam', 'customer_phone' => '0901000002', 'created_at' => now()->subDays(2)],
            ['customer' => 'customer_3', 'tour' => 'da_lat_san_may', 'number_of_people' => 2, 'status' => 'confirmed', 'customer_name' => 'Le Ngoc Anh', 'customer_phone' => '0901000003', 'created_at' => now()->subDays(6)],
            ['customer' => 'customer_3', 'tour' => 'ninh_binh_non_nuoc', 'number_of_people' => 2, 'status' => 'completed', 'customer_name' => 'Le Ngoc Anh', 'customer_phone' => '0901000003', 'created_at' => now()->subDays(19)],
            ['customer' => 'customer_3', 'tour' => 'quy_nhon_bien_xanh', 'number_of_people' => 1, 'status' => 'cancelled', 'customer_name' => 'Le Ngoc Anh', 'customer_phone' => '0901000003', 'created_at' => now()->subDays(7)],
            ['customer' => 'customer_4', 'tour' => 'ha_noi_ha_long', 'number_of_people' => 3, 'status' => 'confirmed', 'customer_name' => 'Tran Gia Linh', 'customer_phone' => '0901000004', 'created_at' => now()->subDays(12)],
            ['customer' => 'customer_4', 'tour' => 'moc_chau_hoa_mua_he', 'number_of_people' => 2, 'status' => 'pending', 'customer_name' => 'Tran Gia Linh', 'customer_phone' => '0901000004', 'created_at' => now()->subDays(3)],
            ['customer' => 'customer_4', 'tour' => 'buon_ma_thuot_ca_phe', 'number_of_people' => 2, 'status' => 'confirmed', 'customer_name' => 'Tran Gia Linh', 'customer_phone' => '0901000004', 'created_at' => now()->subDays(5)],
            ['customer' => 'customer_5', 'tour' => 'di_san_mien_trung', 'number_of_people' => 4, 'status' => 'pending', 'customer_name' => 'Vu Tuan Kiet', 'customer_phone' => '0901000005', 'created_at' => now()->subDay()],
            ['customer' => 'customer_5', 'tour' => 'phu_quoc_nghi_duong', 'number_of_people' => 2, 'status' => 'confirmed', 'customer_name' => 'Vu Tuan Kiet', 'customer_phone' => '0901000005', 'created_at' => now()->subDays(8)],
            ['customer' => 'customer_5', 'tour' => 'hue_di_san', 'number_of_people' => 1, 'status' => 'completed', 'customer_name' => 'Vu Tuan Kiet', 'customer_phone' => '0901000005', 'created_at' => now()->subDays(31)],
        ];

        return collect($definitions)->map(function (array $booking) use ($customers, $tours) {
            $createdAt = Carbon::parse($booking['created_at']);
            $tour = $tours[$booking['tour']];

            return Booking::query()->create([
                'user_id' => $customers[$booking['customer']]->id,
                'tour_id' => $tour->id,
                'number_of_people' => $booking['number_of_people'],
                'total_price' => $tour->price * $booking['number_of_people'],
                'status' => $booking['status'],
                'customer_name' => $booking['customer_name'],
                'customer_phone' => $booking['customer_phone'],
                'created_at' => $createdAt,
                'updated_at' => $createdAt->copy()->addHours(6),
            ]);
        });
    }

    private function syncAvailableSeats(Collection $tours, array $capacities, Collection $bookings): void
    {
        foreach ($tours as $key => $tour) {
            $reservedSeats = $bookings
                ->where('tour_id', $tour->id)
                ->filter(fn ($booking) => $booking->status !== 'cancelled')
                ->sum('number_of_people');

            $tour->update([
                'available_seats' => max($capacities[$key] - $reservedSeats, 0),
            ]);
        }
    }

    private function seedRoomBookings(Collection $customers, Collection $rooms): Collection
    {
        $definitions = [
            ['customer' => 'customer_1', 'room' => 'blue_deluxe_bien', 'number_of_guests' => 2, 'number_of_rooms' => 1, 'check_in_date' => now()->addDays(8), 'check_out_date' => now()->addDays(10), 'status' => 'confirmed', 'customer_name' => 'Pham Thu Ha', 'customer_phone' => '0901000001', 'created_at' => now()->subDays(6)],
            ['customer' => 'customer_2', 'room' => 'sapa_view_nui', 'number_of_guests' => 2, 'number_of_rooms' => 1, 'check_in_date' => now()->addDays(14), 'check_out_date' => now()->addDays(16), 'status' => 'pending', 'customer_name' => 'Do Van Nam', 'customer_phone' => '0901000002', 'created_at' => now()->subDays(3)],
            ['customer' => 'customer_3', 'room' => 'hoian_riverside', 'number_of_guests' => 2, 'number_of_rooms' => 1, 'check_in_date' => now()->subDays(20), 'check_out_date' => now()->subDays(17), 'status' => 'completed', 'customer_name' => 'Le Ngoc Anh', 'customer_phone' => '0901000003', 'created_at' => now()->subDays(28)],
            ['customer' => 'customer_4', 'room' => 'blue_suite_gia_dinh', 'number_of_guests' => 4, 'number_of_rooms' => 1, 'check_in_date' => now()->addDays(18), 'check_out_date' => now()->addDays(21), 'status' => 'confirmed', 'customer_name' => 'Tran Gia Linh', 'customer_phone' => '0901000004', 'created_at' => now()->subDays(2)],
            ['customer' => 'customer_5', 'room' => 'sapa_premium', 'number_of_guests' => 3, 'number_of_rooms' => 1, 'check_in_date' => now()->addDays(11), 'check_out_date' => now()->addDays(13), 'status' => 'cancelled', 'customer_name' => 'Vu Tuan Kiet', 'customer_phone' => '0901000005', 'created_at' => now()->subDays(5)],
            ['customer' => 'customer_1', 'room' => 'hoian_gia_dinh', 'number_of_guests' => 5, 'number_of_rooms' => 1, 'check_in_date' => now()->addDays(24), 'check_out_date' => now()->addDays(27), 'status' => 'pending', 'customer_name' => 'Pham Thu Ha', 'customer_phone' => '0901000001', 'created_at' => now()->subDay()],
        ];

        return collect($definitions)->map(function (array $booking) use ($customers, $rooms) {
            $room = $rooms[$booking['room']];
            $checkInDate = Carbon::parse($booking['check_in_date']);
            $checkOutDate = Carbon::parse($booking['check_out_date']);
            $createdAt = Carbon::parse($booking['created_at']);
            $totalNights = max(1, $checkInDate->diffInDays($checkOutDate));

            return RoomBooking::query()->create([
                'user_id' => $customers[$booking['customer']]->id,
                'room_id' => $room->id,
                'customer_name' => $booking['customer_name'],
                'customer_phone' => $booking['customer_phone'],
                'number_of_guests' => $booking['number_of_guests'],
                'number_of_rooms' => $booking['number_of_rooms'],
                'check_in_date' => $checkInDate->toDateString(),
                'check_out_date' => $checkOutDate->toDateString(),
                'total_nights' => $totalNights,
                'total_price' => $room->price_per_night * $booking['number_of_rooms'] * $totalNights,
                'status' => $booking['status'],
                'created_at' => $createdAt,
                'updated_at' => $createdAt->copy()->addHours(4),
            ]);
        });
    }

    private function syncAvailableRooms(Collection $rooms, array $capacities, Collection $roomBookings): void
    {
        foreach ($rooms as $key => $room) {
            $reservedRooms = $roomBookings
                ->where('room_id', $room->id)
                ->filter(fn ($booking) => $booking->status !== 'cancelled')
                ->sum('number_of_rooms');

            $room->update([
                'available_rooms' => max($capacities[$key] - $reservedRooms, 0),
            ]);
        }
    }

    private function seedReviews(Collection $bookings): void
    {
        $comments = [
            'Tour di dung gio, huong dan vien nhiet tinh va lich trinh rat de theo.',
            'Phong cach phuc vu tot, can bang giua tham quan va nghi ngoi.',
            'Gia hop ly, diem den dep va thong tin huong dan ro rang.',
            'Gia dinh minh hai long, du lieu nay rat hop de test review va danh gia sao.',
            'Toi se dat lai lan sau vi trai nghiem kha on va de su dung he thong.',
        ];

        $index = 0;
        $reviewedPairs = [];

        foreach ($bookings->where('status', 'completed') as $booking) {
            $pair = $booking->user_id . '-' . $booking->tour_id;

            if (isset($reviewedPairs[$pair])) {
                continue;
            }

            Review::query()->create([
                'user_id' => $booking->user_id,
                'tour_id' => $booking->tour_id,
                'rating' => 4 + ($index % 2),
                'comment' => $comments[$index++ % count($comments)],
                'created_at' => $booking->created_at->copy()->addDays(4),
                'updated_at' => $booking->created_at->copy()->addDays(4),
            ]);

            $reviewedPairs[$pair] = true;
        }
    }

    private function seedNotifications(Collection $bookings, Collection $tours, Collection $roomBookings, Collection $rooms): void
    {
        foreach ($bookings as $booking) {
            $tour = $tours->firstWhere('id', $booking->tour_id);

            [$type, $title, $message] = match ($booking->status) {
                'confirmed' => ['booking_confirmed', 'Don dat da duoc xac nhan', "Don dat tour '{$tour->name}' cua ban da duoc xac nhan."],
                'cancelled' => ['booking_cancelled', 'Don dat da bi huy', "Don dat tour '{$tour->name}' cua ban da bi huy."],
                'completed' => ['booking_confirmed', 'Chuyen di da hoan thanh', "Tour '{$tour->name}' cua ban da hoan thanh. Ban co the gui danh gia ngay."],
                default => ['booking_received', 'Da nhan yeu cau dat tour', "He thong da ghi nhan yeu cau dat tour '{$tour->name}'."],
            };

            Notification::query()->create([
                'user_id' => $booking->user_id,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'data' => [
                    'booking_id' => $booking->id,
                    'tour_id' => $booking->tour_id,
                    'tour_name' => $tour->name,
                ],
                'is_read' => in_array($booking->status, ['completed', 'cancelled'], true),
                'read_at' => in_array($booking->status, ['completed', 'cancelled'], true) ? $booking->created_at->copy()->addDay() : null,
                'created_at' => $booking->created_at->copy()->addHour(),
                'updated_at' => $booking->created_at->copy()->addHour(),
            ]);
        }

        foreach ($tours->take(6) as $tour) {
            $bookingUserIds = $bookings->where('tour_id', $tour->id)->pluck('user_id')->unique();

            foreach ($bookingUserIds as $userId) {
                Notification::query()->create([
                    'user_id' => $userId,
                    'type' => 'tour_updated',
                    'title' => 'Tour co cap nhat moi',
                    'message' => "Tour '{$tour->name}' vua duoc cap nhat thong tin lich trinh hoac hinh anh.",
                    'data' => [
                        'tour_id' => $tour->id,
                        'tour_name' => $tour->name,
                    ],
                    'is_read' => false,
                    'read_at' => null,
                    'created_at' => now()->subHours(12),
                    'updated_at' => now()->subHours(12),
                ]);
            }
        }

        foreach ($roomBookings as $booking) {
            $room = $rooms->firstWhere('id', $booking->room_id);

            [$type, $title, $message] = match ($booking->status) {
                'confirmed' => ['room_booking_confirmed', 'Don dat phong da duoc xac nhan', "Don dat phong '{$room->title}' cua ban da duoc xac nhan."],
                'cancelled' => ['room_booking_cancelled', 'Don dat phong da bi huy', "Don dat phong '{$room->title}' cua ban da bi huy."],
                'completed' => ['room_booking_confirmed', 'Ky nghi da hoan thanh', "Phong '{$room->title}' cua ban da hoan thanh luot luu tru."],
                default => ['room_booking_received', 'Da nhan yeu cau dat phong', "He thong da ghi nhan yeu cau dat phong '{$room->title}'."],
            };

            Notification::query()->create([
                'user_id' => $booking->user_id,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'data' => [
                    'room_booking_id' => $booking->id,
                    'room_id' => $booking->room_id,
                    'room_title' => $room->title,
                ],
                'is_read' => in_array($booking->status, ['completed', 'cancelled'], true),
                'read_at' => in_array($booking->status, ['completed', 'cancelled'], true) ? $booking->created_at->copy()->addDay() : null,
                'created_at' => $booking->created_at->copy()->addHour(),
                'updated_at' => $booking->created_at->copy()->addHour(),
            ]);
        }
    }
}
