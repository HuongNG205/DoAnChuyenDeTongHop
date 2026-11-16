<?php

namespace App\Http\Controllers;

use App\Services\ActivityService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class NotificationController extends Controller
{
    /**
     * Người dùng bấm vào biểu tượng chuông => mọi thông báo chưa đọc của họ chuyển is_read = true.
     * Được gọi bằng fetch() từ partial.header.
     */
    public function markAllRead(Request $request, ActivityService $activities): Response
    {
        $activities->markAllRead($request->user());

        return response()->noContent();
    }
}
