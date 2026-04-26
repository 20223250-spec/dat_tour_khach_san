<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Tour;
use App\Models\User;
use Illuminate\Http\Request;

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
        return response()->json(Tour::findOrFail($id));
    }

    public function indexWeb(Request $request)
    {
        $query = Tour::query()
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
            ->when($request->filled('start_date_from'), function ($query) use ($request) {
                $query->whereDate('start_date', '>=', $request->input('start_date_from'));
            })
            ->when($request->filled('start_date_to'), function ($query) use ($request) {
                $query->whereDate('start_date', '<=', $request->input('start_date_to'));
            })
            ->when($request->filled('min_price'), function ($query) use ($request) {
                $query->where('price', '>=', $request->input('min_price'));
            })
            ->when($request->filled('max_price'), function ($query) use ($request) {
                $query->where('price', '<=', $request->input('max_price'));
            })
            ->when($request->filled('min_duration_days'), function ($query) use ($request) {
                $query->where('duration_days', '>=', $request->input('min_duration_days'));
            })
            ->when($request->filled('max_duration_days'), function ($query) use ($request) {
                $query->where('duration_days', '<=', $request->input('max_duration_days'));
            })
            ->when($request->filled('min_seats'), function ($query) use ($request) {
                $query->where('available_seats', '>=', $request->input('min_seats'));
            })
            ->orderBy('start_date');

        $tours = $query->paginate(12);

        return view('home', compact('tours'));
    }

    public function showWeb($id)
    {
        $tour = Tour::with(['reviews.user'])->findOrFail($id);

        return view('tours.show', compact('tour'));
    }

    public function adminDashboard()
    {
        $stats = [
            'total_tours' => Tour::count(),
            'total_bookings' => Booking::count(),
            'total_users' => User::count(),
            'total_revenue' => Booking::sum('total_price'),
            'pending_bookings' => Booking::where('status', 'pending')->count(),
            'confirmed_bookings' => Booking::where('status', 'confirmed')->count(),
        ];

        $recent_bookings = Booking::with(['user', 'tour'])->latest()->take(10)->get();

        return view('admin.dashboard', compact('stats', 'recent_bookings'));
    }

    public function adminIndex()
    {
        $query = Tour::query()
            ->when(request()->filled('id'), function ($query) {
                $query->where('id', request()->input('id'));
            })
            ->when(request()->filled('start_date'), function ($query) {
                $query->whereDate('start_date', request()->input('start_date'));
            })
            ->when(request()->filled('destination'), function ($query) {
                $destination = trim((string) request()->input('destination'));

                $query->where('destination', 'like', '%' . $destination . '%');
            })
            ->latest();

        $tours = $query->paginate(20)->appends(request()->query());

        return view('admin.tours.index', compact('tours'));
    }

    public function create()
    {
        return view('admin.tours.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'destination' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'available_seats' => 'required|integer|min:0',
            'start_date' => 'required|date|after:today',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->all();

        if ($request->hasFile('image')) {
            $imageName = time() . '.' . $request->image->extension();
            $request->image->move(public_path('images/tours'), $imageName);
            $data['image'] = 'images/tours/' . $imageName;
        }

        Tour::create($data);

        return redirect()->route('admin.tours.index')->with('success', 'Tạo tour thành công.');
    }

    public function edit($id)
    {
        $tour = Tour::findOrFail($id);

        return view('admin.tours.edit', compact('tour'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'destination' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'available_seats' => 'required|integer|min:0',
            'start_date' => 'required|date',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $tour = Tour::findOrFail($id);
        $data = $request->all();

        if ($request->hasFile('image')) {
            if ($tour->image && file_exists(public_path($tour->image))) {
                unlink(public_path($tour->image));
            }

            $imageName = time() . '.' . $request->image->extension();
            $request->image->move(public_path('images/tours'), $imageName);
            $data['image'] = 'images/tours/' . $imageName;
        }

        $tour->update($data);

        return redirect()->route('admin.tours.index')->with('success', 'Cập nhật tour thành công.');
    }

    public function destroy($id)
    {
        Tour::findOrFail($id)->delete();

        return redirect()->route('admin.tours.index')->with('success', 'Xóa tour thành công.');
    }
}
