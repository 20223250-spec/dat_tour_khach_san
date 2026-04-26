<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function userIndex(): View|RedirectResponse
    {
        $user = Auth::user();
        $adminIds = User::query()->where('is_admin', true)->orderBy('id')->pluck('id');
        $admin = User::query()->whereIn('id', $adminIds)->orderBy('id')->first();

        if ($adminIds->isEmpty() || ! $admin) {
            return redirect()->back()->withErrors(['message' => 'Hiện chưa có quản trị viên để liên hệ.']);
        }

        $messages = ChatMessage::query()
            ->with('sender')
            ->where(function ($query) use ($user, $adminIds) {
                $query->where('sender_id', $user->id)
                    ->whereIn('receiver_id', $adminIds);
            })
            ->orWhere(function ($query) use ($user, $adminIds) {
                $query->whereIn('sender_id', $adminIds)
                    ->where('receiver_id', $user->id);
            })
            ->orderBy('created_at')
            ->get();

        ChatMessage::query()
            ->whereIn('sender_id', $adminIds)
            ->where('receiver_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('chat.index', compact('messages', 'admin'));
    }

    public function userSend(Request $request): RedirectResponse
    {
        $request->validate([
            'message' => 'required|string|max:1500',
        ]);

        $user = Auth::user();
        $admin = User::query()->where('is_admin', true)->orderBy('id')->first();

        if (! $admin) {
            return back()->withErrors(['message' => 'Hiện chưa có quản trị viên để liên hệ.']);
        }

        ChatMessage::create([
            'sender_id' => $user->id,
            'receiver_id' => $admin->id,
            'message' => trim((string) $request->input('message')),
        ]);

        return back()->with('success', 'Đã gửi tin nhắn cho quản trị viên.');
    }

    public function adminIndex(): View
    {
        $adminIds = User::query()->where('is_admin', true)->pluck('id');

        $users = User::query()
            ->where('is_admin', false)
            ->where(function ($query) use ($adminIds) {
                $query->whereHas('sentMessages', function ($inner) use ($adminIds) {
                    $inner->whereIn('receiver_id', $adminIds);
                })->orWhereHas('receivedMessages', function ($inner) use ($adminIds) {
                    $inner->whereIn('sender_id', $adminIds);
                });
            })
            ->withCount([
                'sentMessages as unread_chat_count' => function ($query) use ($adminIds) {
                    $query->whereIn('receiver_id', $adminIds)->whereNull('read_at');
                },
            ])
            ->orderBy('id', 'desc')
            ->get();

        return view('admin.chat.index', compact('users'));
    }

    public function adminShow(int $userId): View
    {
        $adminIds = User::query()->where('is_admin', true)->pluck('id');
        $user = User::query()->where('is_admin', false)->findOrFail($userId);

        $messages = ChatMessage::query()
            ->with('sender')
            ->where(function ($query) use ($adminIds, $user) {
                $query->whereIn('sender_id', $adminIds)
                    ->where('receiver_id', $user->id);
            })
            ->orWhere(function ($query) use ($adminIds, $user) {
                $query->where('sender_id', $user->id)
                    ->whereIn('receiver_id', $adminIds);
            })
            ->orderBy('created_at')
            ->get();

        ChatMessage::query()
            ->where('sender_id', $user->id)
            ->whereIn('receiver_id', $adminIds)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('admin.chat.show', compact('messages', 'user'));
    }

    public function adminSend(Request $request, int $userId): RedirectResponse
    {
        $request->validate([
            'message' => 'required|string|max:1500',
        ]);

        $admin = Auth::user();
        $user = User::query()->where('is_admin', false)->findOrFail($userId);

        ChatMessage::create([
            'sender_id' => $admin->id,
            'receiver_id' => $user->id,
            'message' => trim((string) $request->input('message')),
        ]);

        return back()->with('success', 'Đã gửi phản hồi cho khách hàng.');
    }
}
