<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Room;
use App\Models\RoomBooking;
use App\Models\Tour;
use App\Models\User;
use App\Support\ImageUploader;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TourController extends Controller
{
    public function index()
    {
        $tours = Tour::where('available_seats', '>', 0)
            ->whereDate('start_date', '>', now())
            ->get();

        return response()->json($tours);
    }

    public function show($id)
    {
        return response()->json(Tour::with('owner')->findOrFail($id));
    }

    public function indexWeb(Request $request)
    {
        $query = Tour::query()
            ->with('owner')
            ->where('available_seats', '>', 0)
            ->whereDate('start_date', '>', now())
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->input('search'));

                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('destination', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('destination'), function ($query) use ($request) {
                $query->where('destination', 'like', '%' . trim((string) $request->input('destination')) . '%');
            })
            ->when($request->filled('min_price'), function ($query) use ($request) {
                $query->where('price', '>=', $request->input('min_price'));
            })
            ->when($request->filled('max_price'), function ($query) use ($request) {
                $query->where('price', '<=', $request->input('max_price'));
            })
            ->orderBy('start_date');

        $tours = $query->paginate(12);
        $featuredRooms = Room::query()
            ->with('owner')
            ->active()
            ->latest()
            ->take(6)
            ->get();

        $viewData = [
            'tours' => $tours,
            'featuredRooms' => $featuredRooms,
        ];

        if ($request->user()?->isAdmin()) {
            $viewData = array_merge($viewData, $this->adminWorkspaceData($request));
        }

        return view('trang_chu', $viewData);
    }

    public function showWeb($id)
    {
        $tour = Tour::with(['reviews.user', 'owner'])->findOrFail($id);

        return view('tour_du_lich.chi_tiet', compact('tour'));
    }

    public function adminDashboard(Request $request)
    {
        return redirect()->to($this->adminWorkspaceUrl(
            $request->input('admin_active_tab', 'tong-quan'),
            array_filter([
                'report_status' => $request->input('report_status'),
                'report_start_date' => $request->input('report_start_date'),
                'report_end_date' => $request->input('report_end_date'),
            ])
        ));
    }

    private function adminWorkspaceData(Request $request): array
    {
        $reportStatus = $request->input('report_status');
        $reportStartDate = $request->input('report_start_date');
        $reportEndDate = $request->input('report_end_date');
        $totalTourRevenue = Booking::where('status', 'completed')->sum('total_price');
        $totalRoomRevenue = RoomBooking::where('status', 'completed')->sum('total_price');
        $totalTourBookings = Booking::count();
        $totalRoomBookings = RoomBooking::count();

        $stats = [
            'total_tours' => Tour::count(),
            'total_rooms' => Room::count(),
            'total_bookings' => $totalTourBookings,
            'total_room_bookings' => $totalRoomBookings,
            'total_all_bookings' => $totalTourBookings + $totalRoomBookings,
            'total_users' => User::count(),
            'total_tour_owners' => User::where('role', User::ROLE_TOUR_OWNER)->count(),
            'total_hotel_owners' => User::where('role', User::ROLE_HOTEL_OWNER)->count(),
            'total_revenue' => $totalTourRevenue,
            'total_room_revenue' => $totalRoomRevenue,
            'total_all_revenue' => $totalTourRevenue + $totalRoomRevenue,
            'pending_bookings' => Booking::where('status', 'pending')->count(),
            'confirmed_bookings' => Booking::where('status', 'confirmed')->count(),
            'cancelled_bookings' => Booking::where('status', 'cancelled')->count(),
            'completed_bookings' => Booking::where('status', 'completed')->count(),
            'pending_room_bookings' => RoomBooking::where('status', 'pending')->count(),
            'confirmed_room_bookings' => RoomBooking::where('status', 'confirmed')->count(),
            'cancelled_room_bookings' => RoomBooking::where('status', 'cancelled')->count(),
            'completed_room_bookings' => RoomBooking::where('status', 'completed')->count(),
        ];

        $recent_bookings = Booking::with(['user', 'tour'])->latest()->take(8)->get();
        $recentRoomBookings = RoomBooking::with(['user', 'room'])->latest()->take(8)->get();
        $adminBookings = Booking::with(['user', 'tour'])->latest()->get();
        $adminRoomBookings = RoomBooking::with(['user', 'room'])->latest()->get();
        $adminTours = Tour::with('owner')->latest()->get();
        $adminRooms = Room::with('owner')->latest()->get();
        $tourOwnerOptions = User::query()
            ->where('role', User::ROLE_TOUR_OWNER)
            ->orderBy('name')
            ->get();
        $hotelOwnerOptions = User::query()
            ->where('role', User::ROLE_HOTEL_OWNER)
            ->orderBy('name')
            ->get();

        $reportBookingsQuery = Booking::with(['user', 'tour']);

        if ($reportStatus) {
            $reportBookingsQuery->where('status', $reportStatus);
        }

        if ($reportStartDate && $reportEndDate) {
            $reportBookingsQuery->whereBetween('created_at', [$reportStartDate, $reportEndDate]);
        }

        $reportBookings = $reportBookingsQuery->latest()->get();
        $statusStats = Booking::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');
        $revenueByMonth = Booking::where('status', 'completed')
            ->where('created_at', '>=', now()->subMonths(6))
            ->orderBy('created_at')
            ->get()
            ->groupBy(function ($booking) {
                return $booking->created_at->format('Y-m');
            })
            ->map(function ($group, $key) {
                [$year, $month] = explode('-', $key);

                return (object) [
                    'year' => (int) $year,
                    'month' => (int) $month,
                    'revenue' => $group->sum('total_price'),
                ];
            })
            ->values();
        $topTours = Tour::withCount('bookings')
            ->orderBy('bookings_count', 'desc')
            ->limit(5)
            ->get();
        $reportTours = Tour::withCount('bookings')
            ->with(['bookings' => function ($query) {
                $query->select('id', 'tour_id', 'total_price', 'status');
            }])
            ->orderBy('bookings_count', 'desc')
            ->get();
        $tourRevenue = [];

        foreach ($reportTours as $tour) {
            $tourRevenue[$tour->id] = $tour->bookings
                ->where('status', 'completed')
                ->sum('total_price');
        }

        return compact(
            'stats',
            'recent_bookings',
            'recentRoomBookings',
            'adminBookings',
            'adminRoomBookings',
            'adminTours',
            'adminRooms',
            'tourOwnerOptions',
            'hotelOwnerOptions',
            'reportBookings',
            'statusStats',
            'revenueByMonth',
            'topTours',
            'reportTours',
            'tourRevenue',
            'reportStatus',
            'reportStartDate',
            'reportEndDate',
        );
    }

    public function adminIndex(Request $request)
    {
        return redirect()->to($this->adminWorkspaceUrl('tour-du-lich'));
    }

    public function partnerIndex(Request $request)
    {
        return $this->renderManageIndex($request, 'partner.tours', false);
    }

    public function create(Request $request)
    {
        return redirect()->to($this->adminWorkspaceUrl('tour-du-lich'));
    }

    public function partnerCreate(Request $request)
    {
        return $this->renderCreateForm($request, 'partner.tours', false);
    }

    public function store(Request $request)
    {
        return $this->handleStore($request, 'admin.tours');
    }

    public function partnerStore(Request $request)
    {
        return $this->handleStore($request, 'partner.tours');
    }

    public function edit(Request $request, $id)
    {
        return redirect()->to($this->adminWorkspaceUrl('tour-du-lich'));
    }

    public function partnerEdit(Request $request, $id)
    {
        return $this->renderEditForm($request, $id, 'partner.tours', false);
    }

    public function update(Request $request, $id)
    {
        return $this->handleUpdate($request, $id, 'admin.tours');
    }

    public function partnerUpdate(Request $request, $id)
    {
        return $this->handleUpdate($request, $id, 'partner.tours');
    }

    public function destroy(Request $request, $id)
    {
        return $this->handleDestroy($request, $id, 'admin.tours');
    }

    public function partnerDestroy(Request $request, $id)
    {
        return $this->handleDestroy($request, $id, 'partner.tours');
    }

    private function renderManageIndex(Request $request, string $routePrefix, bool $isAdminArea)
    {
        $query = Tour::query()
            ->with('owner')
            ->latest();

        if (! $request->user()->isAdmin()) {
            $query->where('owner_id', $request->user()->id);
        }

        $tours = $query->paginate(20);
        $tourBookings = Booking::query()
            ->with(['user', 'tour'])
            ->latest()
            ->when(! $request->user()->isAdmin(), function ($bookingQuery) use ($request) {
                $bookingQuery->whereHas('tour', function ($tourQuery) use ($request) {
                    $tourQuery->where('owner_id', $request->user()->id);
                });
            })
            ->paginate(15, ['*'], 'tour_bookings_page');

        return view('quan_tri.tour_du_lich.danh_sach', [
            'tours' => $tours,
            'tourBookings' => $tourBookings,
            'routePrefix' => $routePrefix,
            'isAdminArea' => $isAdminArea,
            'showOwnerColumn' => $request->user()->isAdmin(),
            'bookingStatusRoute' => $request->user()->isAdmin()
                ? 'admin.bookings.update-status'
                : 'partner.tours.bookings.update-status',
        ]);
    }

    private function renderCreateForm(Request $request, string $routePrefix, bool $isAdminArea)
    {
        return view('quan_tri.tour_du_lich.tao_moi', [
            'routePrefix' => $routePrefix,
            'isAdminArea' => $isAdminArea,
            'ownerOptions' => $this->tourOwnerOptions($request->user()),
        ]);
    }

    private function handleStore(Request $request, string $routePrefix)
    {
        [$data] = $this->validatedTourData($request);

        if ($request->hasFile('image')) {
            $data['image'] = ImageUploader::store($request->file('image'), 'uploads/tours');
        }

        Tour::create($data);

        return $this->redirectAfterManageAction($routePrefix, 'tour-du-lich', 'Đã tạo tour thành công.');
    }

    private function renderEditForm(Request $request, $id, string $routePrefix, bool $isAdminArea)
    {
        $tour = Tour::with('owner')->findOrFail($id);
        $this->ensureTourAccess($request->user(), $tour);

        return view('quan_tri.tour_du_lich.chinh_sua', [
            'tour' => $tour,
            'routePrefix' => $routePrefix,
            'isAdminArea' => $isAdminArea,
            'ownerOptions' => $this->tourOwnerOptions($request->user()),
        ]);
    }

    private function handleUpdate(Request $request, $id, string $routePrefix)
    {
        $tour = Tour::findOrFail($id);
        $this->ensureTourAccess($request->user(), $tour);

        [$data, $removeImage] = $this->validatedTourData($request);

        if ($removeImage) {
            ImageUploader::delete($tour->image);
            $data['image'] = null;
        }

        if ($request->hasFile('image')) {
            ImageUploader::delete($tour->image);
            $data['image'] = ImageUploader::store($request->file('image'), 'uploads/tours');
        }

        $tour->update($data);

        return $this->redirectAfterManageAction($routePrefix, 'tour-du-lich', 'Đã cập nhật tour thành công.');
    }

    private function handleDestroy(Request $request, $id, string $routePrefix)
    {
        $tour = Tour::findOrFail($id);
        $this->ensureTourAccess($request->user(), $tour);
        if ($tour->bookings()->exists()) {
            return $this->redirectAfterManageAction($routePrefix, 'tour-du-lich', null)
                ->withErrors(['message' => 'Khong the xoa tour da phat sinh don dat.']);
        }

        ImageUploader::delete($tour->image);
        $tour->delete();

        return $this->redirectAfterManageAction($routePrefix, 'tour-du-lich', 'Đã xóa tour thành công.');
    }

    private function validatedTourData(Request $request): array
    {
        $startDateRules = ['required', 'date'];
        $ownerRules = ['nullable', 'integer', 'exists:users,id'];

        if ($request->isMethod('post')) {
            $startDateRules[] = 'after:today';
        }

        if ($request->user()->isAdmin()) {
            $ownerRules[0] = 'required';
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'destination' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'available_seats' => 'required|integer|min:0',
            'start_date' => $startDateRules,
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'remove_image' => 'nullable|boolean',
            'owner_id' => $ownerRules,
        ]);

        $removeImage = $request->boolean('remove_image');
        unset($validated['remove_image']);

        if ($request->user()->isAdmin()) {
            $validated['owner_id'] = $this->resolveTourOwnerId($request);
        } else {
            unset($validated['owner_id']);
            $validated['owner_id'] = $request->user()->id;
        }

        return [$validated, $removeImage];
    }

    private function resolveTourOwnerId(Request $request): int
    {
        $owner = User::query()
            ->whereKey($request->input('owner_id'))
            ->where('role', User::ROLE_TOUR_OWNER)
            ->first();

        if (! $owner) {
            throw ValidationException::withMessages([
                'owner_id' => 'Chỉ tài khoản chủ tour mới được nhận tour này.',
            ]);
        }

        return $owner->id;
    }

    private function tourOwnerOptions(User $user)
    {
        if (! $user->isAdmin()) {
            return collect();
        }

        return User::query()
            ->where('role', User::ROLE_TOUR_OWNER)
            ->orderBy('name')
            ->get();
    }

    private function ensureTourAccess(User $user, Tour $tour): void
    {
        if ($user->isAdmin()) {
            return;
        }

        if ((int) $tour->owner_id !== (int) $user->id) {
            abort(403, 'Access denied');
        }
    }

    private function redirectAfterManageAction(string $routePrefix, string $hash, ?string $message)
    {
        if ($routePrefix === 'admin.tours') {
            $redirect = redirect()->to($this->adminWorkspaceUrl($hash));

            return $message ? $redirect->with('success', $message) : $redirect;
        }

        $redirect = redirect()->route($routePrefix . '.index');

        return $message ? $redirect->with('success', $message) : $redirect;
    }

    private function adminWorkspaceUrl(string $tab = 'tong-quan', array $query = []): string
    {
        return route('home', array_merge($query, ['admin_active_tab' => $tab])) . '#quan-tri-noi-bo';
    }
}
