<?php

namespace App\Http\Controllers;

use App\Mail\BookingConfirmation;
use App\Models\Booking;
use App\Models\Notification;
use App\Models\Tour;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class BookingController extends Controller
{
    public function index()
    {
        $bookings = Booking::with('tour')
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return response()->json($bookings);
    }

    public function store(Request $request, $tourId)
    {
        $request->merge([
            'customer_phone' => preg_replace('/\s+/', '', (string) $request->input('customer_phone')),
        ]);

        $validated = $request->validate([
            'number_of_people' => 'required|integer|min:1',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => ['required', 'digits_between:9,15'],
        ]);

        $numberOfPeople = (int) $validated['number_of_people'];

        try {
            DB::beginTransaction();

            $tour = Tour::lockForUpdate()->findOrFail($tourId);

            if (now()->toDateString() >= $tour->start_date) {
                return response()->json(['message' => 'Tour đã khởi hành, không thể đặt thêm.'], 400);
            }

            if ($tour->available_seats < $numberOfPeople) {
                return response()->json(['message' => 'Xin lỗi, tour này không còn đủ chỗ trống.'], 400);
            }

            $totalPrice = round((float) $tour->price * $numberOfPeople, 2);

            $booking = Booking::create([
                'user_id' => Auth::id(),
                'tour_id' => $tour->id,
                'number_of_people' => $numberOfPeople,
                'total_price' => $totalPrice,
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'status' => 'pending',
            ]);

            $tour->decrement('available_seats', $numberOfPeople);

            DB::commit();

            Notification::createBookingReceived(Auth::id(), $booking);

            try {
                Mail::to($booking->user->email)->send(new BookingConfirmation($booking));
            } catch (\Throwable $exception) {
                report($exception);
            }

            return response()->json([
                'message' => 'Đặt tour thành công.',
                'booking' => $booking,
            ], 201);
        } catch (\Throwable $exception) {
            DB::rollBack();

            return response()->json([
                'message' => 'Có lỗi xảy ra trong quá trình đặt tour.',
                'error' => $exception->getMessage(),
            ], 500);
        }
    }

    public function indexWeb()
    {
        $bookings = Booking::with('tour')
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('bookings.index', compact('bookings'));
    }

    public function storeWeb(Request $request, $tourId)
    {
        $request->merge([
            'customer_phone' => preg_replace('/\s+/', '', (string) $request->input('customer_phone')),
        ]);

        $validated = $request->validate([
            'number_of_people' => 'required|integer|min:1',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => ['required', 'digits_between:9,15'],
        ]);

        $numberOfPeople = (int) $validated['number_of_people'];

        try {
            DB::beginTransaction();

            $tour = Tour::lockForUpdate()->findOrFail($tourId);

            if (now()->toDateString() >= $tour->start_date) {
                return back()->withErrors(['message' => 'Tour đã khởi hành, không thể đặt thêm.']);
            }

            if ($tour->available_seats < $numberOfPeople) {
                return back()->withErrors(['message' => 'Xin lỗi, tour này không còn đủ chỗ trống.']);
            }

            $totalPrice = round((float) $tour->price * $numberOfPeople, 2);

            $booking = Booking::create([
                'user_id' => Auth::id(),
                'tour_id' => $tour->id,
                'number_of_people' => $numberOfPeople,
                'total_price' => $totalPrice,
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'status' => 'pending',
            ]);

            $tour->decrement('available_seats', $numberOfPeople);

            DB::commit();

            Notification::createBookingReceived(Auth::id(), $booking);

            try {
                Mail::to(Auth::user()->email)->send(new BookingConfirmation($booking));
            } catch (\Throwable $exception) {
                report($exception);
            }

            return redirect()
                ->route('bookings.index')
                ->with('success', 'Đặt tour thành công. Email xác nhận đã được gửi.');
        } catch (\Throwable $exception) {
            DB::rollBack();

            return back()->withErrors(['message' => 'Có lỗi xảy ra trong quá trình đặt tour.']);
        }
    }

    public function adminIndex()
    {
        $bookingsQuery = Booking::with(['user', 'tour'])
            ->when(request()->filled('id'), function ($query) {
                $query->where('id', request()->input('id'));
            })
            ->when(request()->filled('status'), function ($query) {
                $query->where('status', request()->input('status'));
            })
            ->when(request()->filled('destination'), function ($query) {
                $destination = trim((string) request()->input('destination'));

                $query->whereHas('tour', function ($tourQuery) use ($destination) {
                    $tourQuery->where('destination', 'like', '%' . $destination . '%');
                });
            })
            ->latest();

        $bookings = $bookingsQuery->paginate(20)->appends(request()->query());

        return view('admin.bookings.index', compact('bookings'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled,completed',
        ]);

        $booking = Booking::findOrFail($id);
        $oldStatus = $booking->status;
        $booking->update(['status' => $request->status]);

        if ($request->status === 'confirmed' && $oldStatus !== 'confirmed') {
            Notification::createBookingConfirmed($booking->user_id, $booking);
        } elseif ($request->status === 'cancelled' && $oldStatus !== 'cancelled') {
            Notification::createBookingCancelled($booking->user_id, $booking);
        }

        return redirect()->back()->with('success', 'Cập nhật trạng thái thành công.');
    }
}
