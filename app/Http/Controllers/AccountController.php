<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAccountRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Services\AccountService;
use Illuminate\Http\RedirectResponse;

class AccountController extends Controller
{
    protected AccountService $accountService;

    public function __construct(AccountService $accountService)
    {
        $this->accountService = $accountService;
    }

    public function edit()
    {
        return view('account.edit');
    }

    public function update(UpdateAccountRequest $request): RedirectResponse
    {
        $this->accountService->updateProfile($request->user(), $request->validated());

        return redirect()
            ->route('account.edit')
            ->with('status', 'Đã cập nhật tài khoản.');
    }

    public function password(UpdatePasswordRequest $request): RedirectResponse
    {
        $this->accountService->changePassword($request->user(), $request->validated('password'));

        return redirect()
            ->route('account.edit')
            ->with('status', 'Đã đổi mật khẩu.');
    }
}
