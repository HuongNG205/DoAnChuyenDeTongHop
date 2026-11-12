<?php

namespace App\View\Composers;

use App\Models\Activity;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

/** Cấp dữ liệu chuông thông báo cho partial.header (bảng activities, notify_user_id = user hiện tại). */
class HeaderComposer
{
    public function compose(View $view): void
    {
        if (! Auth::check()) {
            return;
        }

        $notifications = Activity::where('notify_user_id', Auth::id())->whereNotNull('message');

        $view->with([
            'unreadCount' => (clone $notifications)->where('is_read', false)->count(),
            'headerNotifications' => $notifications->latest('created_at')->latest('id')->limit(10)->get(),
        ]);
    }
}
