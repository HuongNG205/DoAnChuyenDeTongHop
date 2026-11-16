<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/** Quản lý tài khoản của admin: cập nhật (thông tin, vai trò, chặn / bỏ chặn) và xóa. */
class UserService
{
    public function __construct(private ActivityService $activities)
    {
    }

    /**
     * @param  array{name?: string, email?: string, role?: string, is_blocked?: bool|int|string, blocked_reason?: ?string}  $data
     */
    public function update(User $user, array $data): User
    {
        $user->fill(Arr::only($data, ['name', 'email', 'role', 'is_blocked']));

        if ($user->is_blocked) {
            if (array_key_exists('blocked_reason', $data)) {
                $user->blocked_reason = $data['blocked_reason'];
            }
        } else {
            $user->blocked_reason = null; // bỏ chặn thì xóa lý do
        }

        if (! $user->isDirty()) {
            return $user;
        }

        return DB::transaction(function () use ($user) {
            $blockChanged = $user->isDirty('is_blocked');
            $profileChanged = $user->isDirty(['name', 'email', 'role']);

            $user->save();

            if ($blockChanged) {
                $this->activities->record($user->is_blocked ? 'block_user' : 'unblock_user', null, $user);

                if ($user->is_blocked) {
                    $this->revokeSessions($user); // đang đăng nhập thì bị đăng xuất ngay
                }
            }

            if ($profileChanged) {
                $this->activities->record('update_account', null, $user);
            }

            return $user;
        });
    }

    /** Xóa mềm tài khoản (ticket, phản hồi, đánh giá của user được giữ lại). */
    public function delete(User $user): void
    {
        DB::transaction(function () use ($user) {
            $this->activities->record('delete_user', null, $user);

            $user->delete();
            $this->revokeSessions($user);
        });
    }

    /** Hủy mọi phiên đăng nhập của user (chỉ có tác dụng khi SESSION_DRIVER=database). */
    private function revokeSessions(User $user): void
    {
        if (config('session.driver') === 'database') {
            DB::table(config('session.table', 'sessions'))->where('user_id', $user->id)->delete();
        }
    }
}
