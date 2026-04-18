<?php

namespace App\Http\Controllers;

use App\Mail\RoomBookingConfirmation;
use App\Models\Notification;
use App\Models\Room;
use App\Models\RoomBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class RoomBookingController extends Controller
{
    private const ADMIN_WORKSPACE_ANCHOR = '#quan-tri-noi-bo';

    public function storeWeb(Request $request, $roomId)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'number_of_guests' => 'required|integer|min:1',
            'number_of_rooms' => 'required|integer|min:1',
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
        ]);

        try {
            DB::beginTransaction();

            $room = Room::lockForUpdate()->findOrFail($roomId);
            $this->ensureRoomCanBeBooked($room, $validated);

            $checkInDate = Carbon::parse($validated['check_in_date'])->startOfDay();
            $checkOutDate = Carbon::parse($validated['check_out_date'])->startOfDay();
            $totalNights = max(1, $checkInDate->diffInDays($checkOutDate));

            $booking = RoomBooking::create([
                'user_id' => Auth::id(),
                'room_id' => $room->id,
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'number_of_guests' => $validated['number_of_guests'],
                'number_of_rooms' => $validated['number_of_rooms'],
                'check_in_date' => $validated['check_in_date'],
                'check_out_date' => $validated['check_out_date'],
                'total_nights' => $totalNights,
                'total_price' => $room->price_per_night * $validated['number_of_rooms'] * $totalNights,
                'status' => 'pending',
            ]);

            $room->decrement('available_rooms', $validated['number_of_rooms']);

            DB::commit();

            $booking->loadMissing('room');
            Notification::createRoomBookingReceived(Auth::id(), $booking);

            try {
                Mail::to(Auth::user()->email)->send(new RoomBookingConfirmation($booking));
            } catch (\Throwable $exception) {
                report($exception);
            }

            return redirect()
                ->route('bookings.index')
                ->with('success', 'Dat phong thanh cong. Email xac nhan da duoc gui.');
        } catch (ValidationException $exception) {
            DB::rollBack();

            throw $exception;
        } catch (\Throwable $exception) {
            DB::rollBack();

            return back()->withErrors(['message' => 'Co loi xay ra trong qua trinh dat phong.']);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled,completed',
        ]);

        try {
            $booking = RoomBooking::with('room')->findOrFail($id);
            $this->ensureRoomBookingManageAccess($request, $booking);
            $oldStatus = $booking->status;

            $this->syncRoomBookingStatus($booking, $request->string('status')->toString());

            $booking->refresh();

            if ($booking->status === 'confirmed' && $oldStatus !== 'confirmed') {
                Notification::createRoomBookingConfirmed($booking->user_id, $booking);
            } elseif ($booking->status === 'cancelled' && $oldStatus !== 'cancelled') {
                Notification::createRoomBookingCancelled($booking->user_id, $booking);
            }

            return redirect()
                ->to($this->manageRedirectUrl($request))
                ->with('success', 'Cap nhat trang thai dat phong thanh cong.');
        } catch (ValidationException $exception) {
            return redirect()
                ->to($this->manageRedirectUrl($request))
                ->withErrors($exception->errors());
        }
    }

    private function ensureRoomCanBeBooked(Room $room, array $validated): void
    {
        if ($room->status !== 'active') {
            throw ValidationException::withMessages([
                'message' => 'Phong nay tam thoi khong nhan dat.',
            ]);
        }

        if ($room->available_rooms < (int) $validated['number_of_rooms']) {
            throw ValidationException::withMessages([
                'message' => 'So phong trong hien khong du cho yeu cau nay.',
            ]);
        }

        if ((int) $validated['number_of_guests'] > ((int) $validated['number_of_rooms'] * (int) $room->guest_capacity)) {
            throw ValidationException::withMessages([
                'message' => 'So khach vuot qua suc chua toi da cua phong da chon.',
            ]);
        }
    }

    private function syncRoomBookingStatus(RoomBooking $booking, string $nextStatus): void
    {
        DB::transaction(function () use ($booking, $nextStatus) {
            $room = Room::lockForUpdate()->findOrFail($booking->room_id);
            $currentStatus = $booking->status;

            if ($currentStatus === $nextStatus) {
                return;
            }

            $currentlyReservesRooms = $this->statusReservesInventory($currentStatus);
            $nextReservesRooms = $this->statusReservesInventory($nextStatus);

            if ($currentlyReservesRooms && ! $nextReservesRooms) {
                $room->increment('available_rooms', $booking->number_of_rooms);
            }

            if (! $currentlyReservesRooms && $nextReservesRooms) {
                if ($room->status !== 'active') {
                    throw ValidationException::withMessages([
                        'message' => 'Phong nay da an hoac khong con mo de nhan dat.',
                    ]);
                }

                if ($room->available_rooms < $booking->number_of_rooms) {
                    throw ValidationException::withMessages([
                        'message' => 'Khong con du phong trong de cap nhat don dat nay.',
                    ]);
                }

                $room->decrement('available_rooms', $booking->number_of_rooms);
            }

            $booking->status = $nextStatus;
            $booking->save();
        });
    }

    private function ensureRoomBookingManageAccess(Request $request, RoomBooking $booking): void
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return;
        }

        if (! $user->isHotelOwner() || (int) $booking->room->owner_id !== (int) $user->id) {
            abort(403, 'Access denied');
        }
    }

    private function manageRedirectUrl(Request $request): string
    {
        if ($request->user()->isAdmin()) {
            return route('home', ['admin_active_tab' => 'don-dat']) . self::ADMIN_WORKSPACE_ANCHOR;
        }

        return route('partner.rooms.index');
    }

    private function statusReservesInventory(string $status): bool
    {
        return in_array($status, ['pending', 'confirmed', 'completed'], true);
    }
}
