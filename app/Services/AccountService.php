<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\User;

class AccountService
{
    /** Cập nhật thông tin cá nhân (hiện chỉ có tên). Chỉ ghi lịch sử khi có thay đổi thật. */
    public function updateProfile(User $user, array $data): void
    {
        $user->fill(['name' => $data['name']]);

        if ($user->isDirty()) {
            $user->save();
            $this->logActivity('update_account', $user);
        }
    }

    /** Đổi mật khẩu (cast "hashed" của User tự băm mật khẩu). */
    public function changePassword(User $user, string $password): void
    {
        $user->update(['password' => $password]);

        $this->logActivity('change_password', $user);
    }

    /** Lịch sử hoạt động (message = null: không phải thông báo trên chuông). */
    private function logActivity(string $action, User $user): void
    {
        Activity::create([
            'action' => $action,
            'notify_user_id' => $user->id,
        ]);
    }
}
