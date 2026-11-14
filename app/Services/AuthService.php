<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthService
{
    private const MAX_ATTEMPTS = 5;
    private const DECAY_SECONDS = 60;

    // ---------- Đăng nhập ----------

    public function login(array $data, Request $request): User
    {
        $email = $data['email'];

        $throttleKey = $email . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts(
            $throttleKey,
            self::MAX_ATTEMPTS
        )) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'email' => "Bạn đã đăng nhập sai quá nhiều lần. Vui lòng thử lại sau {$seconds} giây.",
            ]);
        }

        // Tìm tài khoản chưa bị Soft Delete.
        $user = User::where('email', $email)->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            RateLimiter::hit($throttleKey, self::DECAY_SECONDS);

            throw ValidationException::withMessages([
                'email' => 'Email hoặc mật khẩu không đúng.',
            ]);
        }

        RateLimiter::clear($throttleKey);

        // Mật khẩu đúng nhưng tài khoản bị khóa: báo lý do, không đăng nhập.
        if ($user->is_blocked) {
            $reason = $user->blocked_reason
                ? ' Lý do: ' . $user->blocked_reason
                : '';

            throw ValidationException::withMessages([
                'email' => 'Tài khoản của bạn đã bị khóa.' . $reason,
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate(); // chống session fixation

        $this->logActivity('login', $user);

        return $user;
    }

    // ---------- Đăng ký ----------

    public function register(array $data, Request $request): User
    {
        // Không nhận "role" từ form: tài khoản đăng ký luôn là customer (mặc định của cột role).
        $user = User::create([
            'name' => trim($data['name']),
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        $this->logActivity('register', $user);

        return $user;
    }

    // ---------- Đăng xuất ----------

    public function logout(Request $request): void
    {
        if ($user = $request->user()) {
            $this->logActivity('logout', $user);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    // ---------- Ghi lịch sử hoạt động ----------

    private function logActivity(string $action, User $user): void
    {
        Activity::create([
            'action' => $action,
            'notify_user_id' => $user->id,
        ]);
    }
}
