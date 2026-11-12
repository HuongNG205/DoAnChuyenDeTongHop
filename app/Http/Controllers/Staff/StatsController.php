<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Rating;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    /**
     * Biểu đồ (đường, rating, ưu tiên) tính theo tháng được chọn của năm hiện tại.
     * Các con số tổng (user, ticket, điểm trung bình) tính trên toàn hệ thống.
     */
    public function index(Request $request)
    {
        $month = min(max((int) $request->query('month', now()->month), 1), 12);
        $start = Carbon::create(now()->year, $month, 1)->startOfDay();
        $end = $start->copy()->endOfMonth();
        $days = range(1, $start->daysInMonth);

        // Ticket tạo trong tháng, gom theo ngày tạo, đếm theo trạng thái hiện tại
        $tickets = Ticket::whereBetween('created_at', [$start, $end])->get(['id', 'status', 'priority', 'created_at']);
        $byDay = $tickets->groupBy(fn ($t) => $t->created_at->day);

        $series = fn (?string $status = null) => array_map(
            fn ($d) => $byDay->get($d, collect())
                ->when($status, fn ($c) => $c->where('status', $status))
                ->count(),
            $days
        );

        $ratingCounts = Rating::whereBetween('created_at', [$start, $end])
            ->selectRaw('stars, count(*) as total')->groupBy('stars')->pluck('total', 'stars');

        return view('staff.stats', [
            'month' => $month,
            'labels' => array_map(fn ($d) => sprintf('%02d/%02d', $d, $month), $days),
            'created' => $series(),
            'waiting' => $series('new'),
            'replied' => $series('replied'),
            'inProgress' => $series('in_progress'),
            'closed' => $series('closed'),
            // 5 phần tử: số ticket theo 1..5 sao
            'ratingCounts' => array_map(fn ($s) => (int) ($ratingCounts[$s] ?? 0), range(1, 5)),
            // Thứ tự: low, medium, high
            'priorityCounts' => array_map(fn ($p) => $tickets->where('priority', $p)->count(), ['low', 'medium', 'high']),
            'totalUsers' => User::count(),
            'totalTickets' => Ticket::count(),
            'avgRating' => (float) Rating::avg('stars'),
        ]);
    }
}
