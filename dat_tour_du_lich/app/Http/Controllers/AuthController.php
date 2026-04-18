<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'nullable|string|in:' . User::ROLE_CUSTOMER . ',' . User::ROLE_TOUR_OWNER . ',' . User::ROLE_HOTEL_OWNER,
        ]);

        $role = $request->input('role', User::ROLE_CUSTOMER);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'email_verified_at' => now(),
            'is_admin' => false,
            'role' => $role,
        ]);

        if (! method_exists($user, 'createToken')) {
            return response()->json([
                'message' => 'Tài khoản đã được tạo thành công.',
                'user' => $user,
            ], 201);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Thông tin đăng nhập không chính xác.'],
            ]);
        }

        if (! method_exists($user, 'createToken')) {
            return response()->json([
                'message' => 'Đăng nhập thành công nhưng xác thực API token chưa được cài đặt.',
                'user' => $user,
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }

    public function logout(Request $request)
    {
        if ($request->user()?->currentAccessToken()) {
            $request->user()->currentAccessToken()->delete();
        }

        return response()->json([
            'message' => 'Đăng xuất thành công.',
        ]);
    }

    public function registerWeb(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'nullable|string|in:' . User::ROLE_CUSTOMER . ',' . User::ROLE_TOUR_OWNER . ',' . User::ROLE_HOTEL_OWNER,
        ]);

        $role = $request->input('role', User::ROLE_CUSTOMER);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'email_verified_at' => now(),
            'is_admin' => false,
            'role' => $role,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()
            ->route('home')
            ->with('success', 'Tài khoản đã được tạo thành công.');
    }

    public function loginWeb(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (! Auth::attempt($request->only('email', 'password'))) {
            return back()
                ->withErrors(['email' => 'Thông tin đăng nhập không chính xác.'])
                ->withInput($request->only('email'));
        }

        $request->session()->regenerate();

        return redirect()
            ->route('home')
            ->with('success', 'Đăng nhập thành công.');
    }

    public function profile()
    {
        return view('ho_so');
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $request->user()->id,
        ]);

        $user = $request->user();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->email_verified_at = now();
        $user->save();

        return back()->with('success', 'Cập nhật thông tin thành công.');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('success', $this->passwordStatusMessage($status));
        }

        return back()
            ->withErrors(['email' => $this->passwordStatusMessage($status)])
            ->withInput($request->only('email'));
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('success', $this->passwordStatusMessage($status))
            : back()
                ->withErrors(['email' => [$this->passwordStatusMessage($status)]])
                ->withInput($request->only('email'));
    }

    protected function passwordStatusMessage(string $status): string
    {
        return match ($status) {
            Password::RESET_LINK_SENT => 'Chúng tôi đã gửi liên kết đặt lại mật khẩu tới email của bạn.',
            Password::PASSWORD_RESET => 'Mật khẩu đã được đặt lại thành công.',
            Password::INVALID_USER => 'Không tìm thấy tài khoản phù hợp với địa chỉ email này.',
            Password::INVALID_TOKEN => 'Liên kết đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.',
            Password::RESET_THROTTLED => 'Bạn đã gửi yêu cầu quá nhiều lần. Vui lòng thử lại sau ít phút.',
            default => __($status),
        };
    }
}
