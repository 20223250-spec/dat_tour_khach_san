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
use Illuminate\Validation\Rule;
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
        $validated = $request->validate([
            'number_of_people' => 'required|integer|min:1',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
        ]);

        try {
            $booking = $this->createBooking($tourId, $validated);

            return response()->json([
                'message' => 'Dat tour thanh cong.',
                'booking' => $booking,
            ], 201);
        } catch (ValidationException $exception) {
            return response()->json([
                'message' => $exception->errors()['message'][0] ?? 'Du lieu dat tour khong hop le.',
                'errors' => $exception->errors(),
            ], 422);
        } catch (\Throwable $exception) {
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
        $validated = $request->validate([
            'number_of_people' => 'required|integer|min:1',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
        ]);

        try {
            $this->createBooking($tourId, $validated);

            return redirect()
                ->route('bookings.index')
                ->with('success', 'Dat tour thanh cong. Email xac nhan se duoc gui ngay sau khi he thong xu ly.');
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            return back()->withErrors(['message' => 'Co loi xay ra trong qua trinh dat tour.']);
        }
    }

    public function adminIndex()
    {
        return redirect()->to($this->adminWorkspaceUrl('don-dat'));
    }

    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys(Booking::statusOptions()))],
            'payment_status' => ['nullable', Rule::in(array_keys(Booking::paymentStatusOptions()))],
            'payment_method' => 'nullable|string|max:40',
            'paid_amount' => 'nullable|numeric|min:0',
        ]);

        try {
            $booking = Booking::with('tour')->findOrFail($id);
            $this->ensureBookingManageAccess($request, $booking);
            $oldStatus = $booking->status;

            $this->syncTourBookingState($booking, $validated);

            $booking->refresh();

            if ($booking->status === Booking::STATUS_CONFIRMED && $oldStatus !== Booking::STATUS_CONFIRMED) {
                Notification::createBookingConfirmed($booking->user_id, $booking);
            } elseif ($booking->status === Booking::STATUS_CANCELLED && $oldStatus !== Booking::STATUS_CANCELLED) {
                Notification::createBookingCancelled($booking->user_id, $booking);
            }

            return redirect()
                ->to($this->manageRedirectUrl($request))
                ->with('success', 'Cap nhat don tour thanh cong.');
        } catch (ValidationException $exception) {
            return redirect()
                ->to($this->manageRedirectUrl($request))
                ->withErrors($exception->errors());
        }
    }

    private function createBooking(int|string $tourId, array $validated): Booking
    {
        $booking = DB::transaction(function () use ($tourId, $validated) {
            $tour = Tour::query()
                ->withAvailabilityMetrics()
                ->lockForUpdate()
                ->findOrFail($tourId);

            $this->ensureTourCanBeBooked($tour);

            if ($tour->availableSeats() < (int) $validated['number_of_people']) {
                throw ValidationException::withMessages([
                    'message' => 'Tour nay khong con du cho trong.',
                ]);
            }

            $booking = Booking::create([
                'user_id' => Auth::id(),
                'tour_id' => $tour->id,
                'number_of_people' => (int) $validated['number_of_people'],
                'total_price' => $tour->price * (int) $validated['number_of_people'],
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'status' => Booking::STATUS_PENDING,
                'payment_status' => Booking::PAYMENT_STATUS_UNPAID,
                'paid_amount' => 0,
            ]);

            $tour->syncAvailabilityCache();

            return $booking;
        });

        $booking->loadMissing('user', 'tour');
        Notification::createBookingReceived(Auth::id(), $booking);
        $this->sendConfirmationAfterResponse($booking->id);

        return $booking;
    }

    private function ensureTourCanBeBooked(Tour $tour): void
    {
        $startDate = $tour->start_date instanceof Carbon
            ? $tour->start_date->copy()->startOfDay()
            : Carbon::parse($tour->start_date)->startOfDay();

        if (! $startDate->isAfter(now()->startOfDay())) {
            throw ValidationException::withMessages([
                'message' => 'Tour nay da dong nhan dat cho.',
            ]);
        }
    }

    private function syncTourBookingState(Booking $booking, array $validated): void
    {
        DB::transaction(function () use ($booking, $validated) {
            $booking = Booking::lockForUpdate()->findOrFail($booking->id);
            $tour = Tour::query()
                ->withAvailabilityMetrics()
                ->lockForUpdate()
                ->findOrFail($booking->tour_id);

            $currentStatus = $booking->status;
            $nextStatus = $validated['status'];

            if (
                ! Booking::reservesInventoryForStatus($currentStatus)
                && Booking::reservesInventoryForStatus($nextStatus)
            ) {
                $this->ensureTourCanBeBooked($tour);

                if ($tour->availableSeats() < $booking->number_of_people) {
                    throw ValidationException::withMessages([
                        'message' => 'Khong con du cho trong de cap nhat don dat nay.',
                    ]);
                }
            }

            $booking->status = $nextStatus;
            $this->applyPaymentData($booking, $validated);
            $booking->save();

            $tour->syncAvailabilityCache();
        });
    }

    private function applyPaymentData(Booking $booking, array $validated): void
    {
        $nextPaymentStatus = $validated['payment_status'] ?? $booking->payment_status ?? Booking::PAYMENT_STATUS_UNPAID;
        $paymentMethod = trim((string) ($validated['payment_method'] ?? $booking->payment_method ?? ''));
        $paidAmount = array_key_exists('paid_amount', $validated)
            ? (float) $validated['paid_amount']
            : (float) ($booking->paid_amount ?? 0);

        switch ($nextPaymentStatus) {
            case Booking::PAYMENT_STATUS_UNPAID:
                $booking->payment_status = $nextPaymentStatus;
                $booking->payment_method = null;
                $booking->paid_amount = 0;
                $booking->paid_at = null;
                return;

            case Booking::PAYMENT_STATUS_REFUNDED:
                $booking->payment_status = $nextPaymentStatus;
                $booking->payment_method = $paymentMethod ?: $booking->payment_method;
                $booking->paid_amount = 0;
                $booking->paid_at = null;
                return;

            case Booking::PAYMENT_STATUS_PARTIAL:
                if ($paidAmount <= 0 || $paidAmount >= (float) $booking->total_price) {
                    throw ValidationException::withMessages([
                        'paid_amount' => 'Tien dat coc phai lon hon 0 va nho hon tong tien don tour.',
                    ]);
                }

                $booking->payment_status = $nextPaymentStatus;
                $booking->payment_method = $paymentMethod ?: null;
                $booking->paid_amount = $paidAmount;
                $booking->paid_at ??= now();
                return;

            case Booking::PAYMENT_STATUS_PAID:
                $booking->payment_status = $nextPaymentStatus;
                $booking->payment_method = $paymentMethod ?: null;
                $booking->paid_amount = $booking->total_price;
                $booking->paid_at ??= now();
                return;
        }
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

    private function sendConfirmationAfterResponse(int $bookingId): void
    {
        dispatch(function () use ($bookingId) {
            try {
                $booking = Booking::with(['user', 'tour'])->find($bookingId);

                if ($booking?->user?->email) {
                    Mail::to($booking->user->email)->send(new BookingConfirmation($booking));
                }
            } catch (\Throwable $exception) {
                report($exception);
            }
        })->afterResponse();
    }

    private function adminWorkspaceUrl(string $tab): string
    {
        return route('home', ['admin_active_tab' => $tab]) . self::ADMIN_WORKSPACE_ANCHOR;
    }
}
