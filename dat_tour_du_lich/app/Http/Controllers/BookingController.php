<?php

namespace App\Http\Controllers;

use App\Mail\BookingConfirmation;
use App\Models\Booking;
use App\Models\Notification;
use App\Models\RoomBooking;
use App\Models\Tour;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    private const ADMIN_WORKSPACE_ANCHOR = '#quan-tri-noi-bo';

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
        $request->validate([
            'number_of_people' => 'required|integer|min:1',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
        ]);

        try {
            DB::beginTransaction();

            $tour = Tour::lockForUpdate()->findOrFail($tourId);
            $this->ensureTourCanBeBooked($tour);

            if ($tour->available_seats < $request->number_of_people) {
                return response()->json(['message' => 'Tour nay khong con du cho trong.'], 400);
            }

            $booking = Booking::create([
                'user_id' => Auth::id(),
                'tour_id' => $tour->id,
                'number_of_people' => $request->number_of_people,
                'total_price' => $tour->price * $request->number_of_people,
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'status' => 'pending',
            ]);

            $tour->decrement('available_seats', $request->number_of_people);

            DB::commit();

            $booking->loadMissing('user', 'tour');
            Notification::createBookingReceived(Auth::id(), $booking);

            try {
                Mail::to($booking->user->email)->send(new BookingConfirmation($booking));
            } catch (\Throwable $exception) {
                report($exception);
            }

            return response()->json([
                'message' => 'Dat tour thanh cong.',
                'booking' => $booking,
            ], 201);
        } catch (ValidationException $exception) {
            DB::rollBack();

            return response()->json([
                'message' => $exception->errors()['message'][0] ?? 'Du lieu dat tour khong hop le.',
                'errors' => $exception->errors(),
            ], 422);
        } catch (\Throwable $exception) {
            DB::rollBack();

            return response()->json([
                'message' => 'Co loi xay ra trong qua trinh dat tour.',
                'error' => $exception->getMessage(),
            ], 500);
        }
    }

    public function indexWeb()
    {
        $tourBookings = Booking::with('tour')
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        $roomBookings = RoomBooking::with('room')
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('don_dat.danh_sach', compact('tourBookings', 'roomBookings'));
    }

    public function storeWeb(Request $request, $tourId)
    {
        $request->validate([
            'number_of_people' => 'required|integer|min:1',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
        ]);

        try {
            DB::beginTransaction();

            $tour = Tour::lockForUpdate()->findOrFail($tourId);
            $this->ensureTourCanBeBooked($tour);

            if ($tour->available_seats < $request->number_of_people) {
                return back()->withErrors(['message' => 'Tour nay khong con du cho trong.']);
            }

            $booking = Booking::create([
                'user_id' => Auth::id(),
                'tour_id' => $tour->id,
                'number_of_people' => $request->number_of_people,
                'total_price' => $tour->price * $request->number_of_people,
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'status' => 'pending',
            ]);

            $tour->decrement('available_seats', $request->number_of_people);

            DB::commit();

            $booking->loadMissing('tour');
            Notification::createBookingReceived(Auth::id(), $booking);

            try {
                Mail::to(Auth::user()->email)->send(new BookingConfirmation($booking));
            } catch (\Throwable $exception) {
                report($exception);
            }

            return redirect()
                ->route('bookings.index')
                ->with('success', 'Dat tour thanh cong. Email xac nhan da duoc gui.');
        } catch (ValidationException $exception) {
            DB::rollBack();

            throw $exception;
        } catch (\Throwable $exception) {
            DB::rollBack();

            return back()->withErrors(['message' => 'Co loi xay ra trong qua trinh dat tour.']);
        }
    }

    public function adminIndex()
    {
        return redirect()->to($this->adminWorkspaceUrl('don-dat'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled,completed',
        ]);

        try {
            $booking = Booking::with('tour')->findOrFail($id);
            $this->ensureBookingManageAccess($request, $booking);
            $oldStatus = $booking->status;

            $this->syncTourBookingStatus($booking, $request->string('status')->toString());

            $booking->refresh();

            if ($booking->status === 'confirmed' && $oldStatus !== 'confirmed') {
                Notification::createBookingConfirmed($booking->user_id, $booking);
            } elseif ($booking->status === 'cancelled' && $oldStatus !== 'cancelled') {
                Notification::createBookingCancelled($booking->user_id, $booking);
            }

            return redirect()
                ->to($this->manageRedirectUrl($request))
                ->with('success', 'Cap nhat trang thai thanh cong.');
        } catch (ValidationException $exception) {
            return redirect()
                ->to($this->manageRedirectUrl($request))
                ->withErrors($exception->errors());
        }
    }

    private function ensureTourCanBeBooked(Tour $tour): void
    {
        if (! Carbon::parse($tour->start_date)->startOfDay()->isAfter(now()->startOfDay())) {
            throw ValidationException::withMessages([
                'message' => 'Tour nay da dong nhan dat cho.',
            ]);
        }
    }

    private function syncTourBookingStatus(Booking $booking, string $nextStatus): void
    {
        DB::transaction(function () use ($booking, $nextStatus) {
            $tour = Tour::lockForUpdate()->findOrFail($booking->tour_id);
            $currentStatus = $booking->status;

            if ($currentStatus === $nextStatus) {
                return;
            }

            $currentlyReservesSeats = $this->statusReservesInventory($currentStatus);
            $nextReservesSeats = $this->statusReservesInventory($nextStatus);

            if ($currentlyReservesSeats && ! $nextReservesSeats) {
                $tour->increment('available_seats', $booking->number_of_people);
            }

            if (! $currentlyReservesSeats && $nextReservesSeats) {
                $this->ensureTourCanBeBooked($tour);

                if ($tour->available_seats < $booking->number_of_people) {
                    throw ValidationException::withMessages([
                        'message' => 'Khong con du cho trong de cap nhat don dat nay.',
                    ]);
                }

                $tour->decrement('available_seats', $booking->number_of_people);
            }

            $booking->status = $nextStatus;
            $booking->save();
        });
    }

    private function ensureBookingManageAccess(Request $request, Booking $booking): void
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return;
        }

        if (! $user->isTourOwner() || (int) $booking->tour->owner_id !== (int) $user->id) {
            abort(403, 'Access denied');
        }
    }

    private function manageRedirectUrl(Request $request): string
    {
        if ($request->user()->isAdmin()) {
            return $this->adminWorkspaceUrl('don-dat');
        }

        return route('partner.tours.index');
    }

    private function statusReservesInventory(string $status): bool
    {
        return in_array($status, ['pending', 'confirmed', 'completed'], true);
    }

    private function adminWorkspaceUrl(string $tab): string
    {
        return route('home', ['admin_active_tab' => $tab]) . self::ADMIN_WORKSPACE_ANCHOR;
    }
}
