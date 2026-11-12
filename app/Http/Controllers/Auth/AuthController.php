<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthRequest;
use App\Models\Activity;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /** Số lần đăng nhập sai tối đa trước khi bị khóa tạm thời (theo email + IP). */
    private const MAX_ATTEMPTS = 5;
    private const DECAY_SECONDS = 60;

    // ---------- Đăng nhập ----------

    public function login(AuthRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $email = $data['email']; // đã được AuthRequest chuẩn hóa chữ thường

        $throttleKey = $email . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'email' => "Bạn đã đăng nhập sai quá nhiều lần. Vui lòng thử lại sau {$seconds} giây.",
            ]);
        }

        // User dùng SoftDeletes nên tài khoản đã xóa sẽ không tìm thấy ở đây.
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
            $reason = $user->blocked_reason ? ' Lý do: ' . $user->blocked_reason : '';

            throw ValidationException::withMessages([
                'email' => 'Tài khoản của bạn đã bị khóa.' . $reason,
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate(); // chống session fixation

        $this->logActivity('login', $user);

        return redirect()->intended(route('home'));
    }

    // ---------- Đăng ký ----------

    public function register(AuthRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Không nhận "role" từ form: tài khoản đăng ký luôn là customer (mặc định của cột role).
        $user = User::create([
            'name' => trim($data['name']),
            'email' => $data['email'],
            'password' => $data['password'], // cast 'hashed' trong User tự mã hóa
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        $this->logActivity('register', $user);

        return redirect()->route('home')->with('status', 'Đăng ký thành công. Chào mừng bạn!');
    }

    // ---------- Đăng xuất ----------

    public function logout(Request $request): RedirectResponse
    {
        if ($user = $request->user()) {
            $this->logActivity('logout', $user);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('status', 'Bạn đã đăng xuất.');
    }

    /**
     * Ghi lịch sử hoạt động (message = null nên không hiện ở chuông thông báo).
     * Bảng activities chưa có cột user_id nên dùng notify_user_id để biết của ai,
     * giống cách ActivitySeeder đang làm.
     */
    private function logActivity(string $action, User $user): void
    {
        Activity::create([
            'action' => $action,
            'notify_user_id' => $user->id,
        ]);
    }
}