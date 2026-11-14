<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthRequest;
use App\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    // ---------- Đăng nhập ----------

    public function login(AuthRequest $request): RedirectResponse
    {
        $this->authService->login(
            $request->validated(),
            $request
        );

        return redirect()->intended(route('home'));
    }

    // ---------- Đăng ký ----------

    public function register(AuthRequest $request): RedirectResponse
    {
        $this->authService->register(
            $request->validated(),
            $request
        );

        return redirect()->route('home')->with('status', 'Đăng ký thành công. Chào mừng bạn!');
    }

    // ---------- Đăng xuất ----------

    public function logout(Request $request): RedirectResponse
    {
        $this->authService->logout($request);

        return redirect()->route('home')->with('status', 'Bạn đã đăng xuất.');
    }
}