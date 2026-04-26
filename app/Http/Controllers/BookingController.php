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
                'payment_status' => 'unpaid',
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
                'payment_status' => 'unpaid',
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
            ->when(request()->filled('payment_status'), function ($query) {
                $query->where('payment_status', request()->input('payment_status'));
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

    public function updatePaymentStatus(Request $request, $id)
    {
        $request->validate([
            'payment_status' => 'required|in:unpaid,paid,failed',
        ]);

        $booking = Booking::with('tour')->findOrFail($id);
        $oldPaymentStatus = $booking->payment_status ?? 'unpaid';
        $newPaymentStatus = $request->input('payment_status');

        $booking->update([
            'payment_status' => $newPaymentStatus,
            'paid_at' => $newPaymentStatus === 'paid' ? now() : null,
        ]);

        if ($newPaymentStatus === 'paid' && $oldPaymentStatus !== 'paid') {
            Notification::create([
                'user_id' => $booking->user_id,
                'type' => 'payment_received',
                'title' => 'Thanh toán thành công',
                'message' => "Thanh toán cho tour '{$booking->tour->name}' đã được xác nhận.",
                'data' => [
                    'booking_id' => $booking->id,
                    'tour_id' => $booking->tour_id,
                    'tour_name' => $booking->tour->name,
                    'paid_at' => now()->toDateTimeString(),
                ],
            ]);
        }

        if ($newPaymentStatus === 'failed' && $oldPaymentStatus !== 'failed') {
            Notification::create([
                'user_id' => $booking->user_id,
                'type' => 'payment_failed',
                'title' => 'Thanh toán thất bại',
                'message' => "Thanh toán cho tour '{$booking->tour->name}' chưa thành công.",
                'data' => [
                    'booking_id' => $booking->id,
                    'tour_id' => $booking->tour_id,
                    'tour_name' => $booking->tour->name,
                ],
            ]);
        }

        return redirect()->back()->with('success', 'Cập nhật trạng thái thanh toán thành công.');
    }

    public function simulatePaymentWeb($id)
    {
        $booking = Booking::with('tour')
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if ($booking->status === 'cancelled') {
            return back()->withErrors(['message' => 'Đơn đã hủy, không thể thanh toán.']);
        }

        if ($booking->status === 'completed') {
            return back()->withErrors(['message' => 'Đơn đã hoàn tất, không cần thanh toán thêm.']);
        }

        if ($booking->payment_status === 'paid') {
            return back()->with('success', 'Đơn này đã thanh toán trước đó.');
        }

        $booking->update([
            'payment_status' => 'paid',
            'paid_at' => now(),
        ]);

        Notification::create([
            'user_id' => $booking->user_id,
            'type' => 'payment_received',
            'title' => 'Thanh toán thành công',
            'message' => "Bạn đã thanh toán thành công cho tour '{$booking->tour->name}'.",
            'data' => [
                'booking_id' => $booking->id,
                'tour_id' => $booking->tour_id,
                'tour_name' => $booking->tour->name,
                'paid_at' => now()->toDateTimeString(),
            ],
        ]);

        return back()->with('success', 'Thanh toán thành công.');
    }

    public function simulatePaymentFailedWeb($id)
    {
        $booking = Booking::with('tour')
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if ($booking->status === 'cancelled') {
            return back()->withErrors(['message' => 'Đơn đã hủy, không thể thực hiện thanh toán.']);
        }

        if ($booking->status === 'completed') {
            return back()->withErrors(['message' => 'Đơn đã hoàn tất, không cần thanh toán thêm.']);
        }

        if ($booking->payment_status === 'paid') {
            return back()->withErrors(['message' => 'Đơn đã thanh toán thành công, không thể chuyển sang trạng thái lỗi.']);
        }

        $booking->update([
            'payment_status' => 'failed',
            'paid_at' => null,
        ]);

        Notification::create([
            'user_id' => $booking->user_id,
            'type' => 'payment_failed',
            'title' => 'Thanh toán thất bại',
            'message' => "Giao dịch thanh toán cho tour '{$booking->tour->name}' chưa thành công. Vui lòng thử lại.",
            'data' => [
                'booking_id' => $booking->id,
                'tour_id' => $booking->tour_id,
                'tour_name' => $booking->tour->name,
            ],
        ]);

        return back()->with('success', 'Đã cập nhật trạng thái thanh toán lỗi cho đơn này.');
    }
}
