<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\RoomBooking;
use App\Models\User;
use App\Support\ImageUploader;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RoomController extends Controller
{
    private const ADMIN_WORKSPACE_ANCHOR = '#quan-tri-noi-bo';

    public function showWeb($id)
    {
        $room = Room::with('owner')->findOrFail($id);

        if (
            $room->status !== 'active'
            && (! auth()->check()
                || (! auth()->user()->isAdmin() && (int) auth()->id() !== (int) $room->owner_id))
        ) {
            abort(404);
        }

        return view('phong.chi_tiet', compact('room'));
    }

    public function adminIndex(Request $request)
    {
        return redirect()->to($this->adminWorkspaceUrl('phong'));
    }

    public function partnerIndex(Request $request)
    {
        return $this->renderManageIndex($request, 'partner.rooms', false);
    }

    public function create(Request $request)
    {
        return redirect()->to($this->adminWorkspaceUrl('phong'));
    }

    public function partnerCreate(Request $request)
    {
        return $this->renderCreateForm($request, 'partner.rooms', false);
    }

    public function store(Request $request)
    {
        return $this->handleStore($request, 'admin.rooms');
    }

    public function partnerStore(Request $request)
    {
        return $this->handleStore($request, 'partner.rooms');
    }

    public function edit(Request $request, $id)
    {
        return redirect()->to($this->adminWorkspaceUrl('phong'));
    }

    public function partnerEdit(Request $request, $id)
    {
        return $this->renderEditForm($request, $id, 'partner.rooms', false);
    }

    public function update(Request $request, $id)
    {
        return $this->handleUpdate($request, $id, 'admin.rooms');
    }

    public function partnerUpdate(Request $request, $id)
    {
        return $this->handleUpdate($request, $id, 'partner.rooms');
    }

    public function destroy(Request $request, $id)
    {
        return $this->handleDestroy($request, $id, 'admin.rooms');
    }

    public function partnerDestroy(Request $request, $id)
    {
        return $this->handleDestroy($request, $id, 'partner.rooms');
    }

    private function renderManageIndex(Request $request, string $routePrefix, bool $isAdminArea)
    {
        $query = Room::query()
            ->with('owner')
            ->latest();

        if (! $request->user()->isAdmin()) {
            $query->where('owner_id', $request->user()->id);
        }

        $rooms = $query->paginate(20);

        $roomBookings = RoomBooking::query()
            ->with(['user', 'room'])
            ->latest()
            ->when(! $request->user()->isAdmin(), function ($bookingQuery) use ($request) {
                $bookingQuery->whereHas('room', function ($roomQuery) use ($request) {
                    $roomQuery->where('owner_id', $request->user()->id);
                });
            })
            ->paginate(15, ['*'], 'room_bookings_page');

        return view('quan_tri.phong.danh_sach', [
            'rooms' => $rooms,
            'roomBookings' => $roomBookings,
            'routePrefix' => $routePrefix,
            'isAdminArea' => $isAdminArea,
            'showOwnerColumn' => $request->user()->isAdmin(),
            'roomBookingStatusRoute' => $request->user()->isAdmin()
                ? 'admin.room-bookings.update-status'
                : 'partner.rooms.bookings.update-status',
        ]);
    }

    private function renderCreateForm(Request $request, string $routePrefix, bool $isAdminArea)
    {
        return view('quan_tri.phong.tao_moi', [
            'routePrefix' => $routePrefix,
            'isAdminArea' => $isAdminArea,
            'ownerOptions' => $this->hotelOwnerOptions($request->user()),
        ]);
    }

    private function handleStore(Request $request, string $routePrefix)
    {
        [$data] = $this->validatedRoomData($request);

        if ($request->hasFile('image')) {
            $data['image'] = ImageUploader::store($request->file('image'), 'uploads/rooms');
        }

        Room::create($data);

        return $this->redirectAfterManageAction($routePrefix, 'phong', 'Da dang phong thanh cong.');
    }

    private function renderEditForm(Request $request, $id, string $routePrefix, bool $isAdminArea)
    {
        $room = Room::with('owner')->findOrFail($id);
        $this->ensureRoomAccess($request->user(), $room);

        return view('quan_tri.phong.chinh_sua', [
            'room' => $room,
            'routePrefix' => $routePrefix,
            'isAdminArea' => $isAdminArea,
            'ownerOptions' => $this->hotelOwnerOptions($request->user()),
        ]);
    }

    private function handleUpdate(Request $request, $id, string $routePrefix)
    {
        $room = Room::findOrFail($id);
        $this->ensureRoomAccess($request->user(), $room);

        [$data, $removeImage] = $this->validatedRoomData($request);

        if ($removeImage) {
            ImageUploader::delete($room->image);
            $data['image'] = null;
        }

        if ($request->hasFile('image')) {
            ImageUploader::delete($room->image);
            $data['image'] = ImageUploader::store($request->file('image'), 'uploads/rooms');
        }

        $room->update($data);

        return $this->redirectAfterManageAction($routePrefix, 'phong', 'Da cap nhat phong thanh cong.');
    }

    private function handleDestroy(Request $request, $id, string $routePrefix)
    {
        $room = Room::findOrFail($id);
        $this->ensureRoomAccess($request->user(), $room);

        if ($room->bookings()->exists()) {
            return $this->redirectAfterManageAction($routePrefix, 'phong', null)
                ->withErrors(['message' => 'Khong the xoa phong da phat sinh don dat.']);
        }

        ImageUploader::delete($room->image);
        $room->delete();

        return $this->redirectAfterManageAction($routePrefix, 'phong', 'Da xoa phong thanh cong.');
    }

    private function validatedRoomData(Request $request): array
    {
        $ownerRules = ['nullable', 'integer', 'exists:users,id'];

        if ($request->user()->isAdmin()) {
            $ownerRules[0] = 'required';
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'hotel_name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price_per_night' => 'required|numeric|min:0',
            'guest_capacity' => 'required|integer|min:1',
            'available_rooms' => 'required|integer|min:0',
            'status' => 'required|string|in:active,hidden',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'remove_image' => 'nullable|boolean',
            'owner_id' => $ownerRules,
        ]);

        $removeImage = $request->boolean('remove_image');
        unset($validated['remove_image']);

        if ($request->user()->isAdmin()) {
            $validated['owner_id'] = $this->resolveRoomOwnerId($request);
        } else {
            unset($validated['owner_id']);
            $validated['owner_id'] = $request->user()->id;
        }

        return [$validated, $removeImage];
    }

    private function resolveRoomOwnerId(Request $request): int
    {
        $owner = User::query()
            ->whereKey($request->input('owner_id'))
            ->where('role', User::ROLE_HOTEL_OWNER)
            ->first();

        if (! $owner) {
            throw ValidationException::withMessages([
                'owner_id' => 'Chi tai khoan chu khach san moi duoc nhan phong nay.',
            ]);
        }

        return $owner->id;
    }

    private function hotelOwnerOptions(User $user)
    {
        if (! $user->isAdmin()) {
            return collect();
        }

        return User::query()
            ->where('role', User::ROLE_HOTEL_OWNER)
            ->orderBy('name')
            ->get();
    }

    private function ensureRoomAccess(User $user, Room $room): void
    {
        if ($user->isAdmin()) {
            return;
        }

        if ((int) $room->owner_id !== (int) $user->id) {
            abort(403, 'Access denied');
        }
    }

    private function redirectAfterManageAction(string $routePrefix, string $hash, ?string $message)
    {
        if ($routePrefix === 'admin.rooms') {
            $redirect = redirect()->to($this->adminWorkspaceUrl($hash));

            return $message ? $redirect->with('success', $message) : $redirect;
        }

        $redirect = redirect()->route($routePrefix . '.index');

        return $message ? $redirect->with('success', $message) : $redirect;
    }

    private function adminWorkspaceUrl(string $hash): string
    {
        return route('home', ['admin_active_tab' => $hash]) . self::ADMIN_WORKSPACE_ANCHOR;
    }
}
