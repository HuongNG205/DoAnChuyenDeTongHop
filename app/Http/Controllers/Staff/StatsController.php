<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Rating;
use App\Models\Reply;
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
    private function buildReport(int $month, int $year): array
    {
        $start = Carbon::create($year, $month, 1)->startOfDay();
        $end = $start->copy()->endOfMonth();

        $days = range(1, $start->daysInMonth);

        // ==================== TICKET ====================

        $tickets = Ticket::whereBetween('created_at', [$start, $end])
            ->get(['id', 'track_id', 'status', 'priority', 'created_at', 'closed_at']);

        $byDay = $tickets->groupBy(fn ($t) => $t->created_at->day);

        $statuses = [
            'new' => 'Chờ giải quyết',
            'in_progress' => 'Đang giải quyết',
            'solved' => 'Đã giải quyết',
            'closed' => 'Đã đóng',
        ];

        $priorities = [
            'low' => 'Thấp',
            'medium' => 'Trung bình',
            'high' => 'Cao',
        ];

        $statusCounts = [];
        foreach ($statuses as $key => $label) {
            $statusCounts[$key] = $tickets->where('status', $key)->count();
        }

        $priorityCounts = [];
        foreach ($priorities as $key => $label) {
            $priorityCounts[$key] = $tickets->where('priority', $key)->count();
        }

        $created = array_map(
            fn ($day) => $byDay->get($day, collect())->count(),
            $days
        );

        $dailyStatus = [];
        foreach ($statuses as $key => $label) {
            $dailyStatus[$key] = array_map(
                fn ($day) => $byDay->get($day, collect())
                    ->where('status', $key)->count(),
                $days
            );
        }

        $totalMonthlyTickets = $tickets->count();

        $resolvedCount = $statusCounts['solved'] + $statusCounts['closed'];

        $resolutionRate = $totalMonthlyTickets > 0
            ? $resolvedCount / $totalMonthlyTickets * 100
            : 0;

        // ==================== USER ====================

        $totalUsers = User::count();

        $customerCount = User::where('role', 'customer')->count();
        $staffCount = User::where('role', 'staff')->count();
        $adminCount = User::where('role', 'admin')->count();

        $blockedUsers = User::where('is_blocked', true)->count();

        // ==================== RATING ====================

        $ratings = Rating::whereBetween('created_at', [$start, $end])
            ->get(['stars']);

        $ratingCounts = array_map(
            fn ($star) => $ratings->where('stars', $star)->count(),
            range(1, 5)
        );

        $avgRating = $ratings->isNotEmpty()
            ? (float) $ratings->avg('stars')
            : 0;

        $totalRatings = $ratings->count();

        $positiveRatings = $ratings->where('stars', '>=', 4)->count();

        $positiveRate = $totalRatings > 0
            ? $positiveRatings / $totalRatings * 100
            : 0;

        // ==================== REPLY ====================

        $replies = Reply::whereBetween('created_at', [$start, $end])
            ->get(['id', 'ticket_id', 'created_at']);

        $replyByDay = $replies->groupBy(
            fn ($reply) => $reply->created_at->day
        );

        $dailyReplies = array_map(
            fn ($day) => $replyByDay->get($day, collect())->count(),
            $days
        );

        // ==================== ACTIVITY ====================

        $activities = Activity::whereBetween('created_at', [$start, $end])
            ->get(['id', 'action']);

        $activityCounts = $activities->groupBy('action')
            ->map(fn ($items) => $items->count())
            ->sortDesc();

        // ==================== RESOLUTION TIME ====================

        $closedTickets = $tickets->filter(
            fn ($ticket) => $ticket->closed_at !== null
        );

        $avgResolutionHours = $closedTickets->isNotEmpty()
            ? $closedTickets->avg(
                fn ($ticket) => $ticket->created_at
                    ->diffInMinutes($ticket->closed_at) / 60
            )
            : 0;

        // ==================== GLOBAL SUMMARY ====================

        $totalTickets = Ticket::count();

        // ==================== TEXT REPORT ====================

        $statusSummary = $resolvedCount > 0
            ? "Có {$resolvedCount} Ticket đã giải quyết và đóng."
            : "Chưa có Ticket nào được giải quyết và đóng.";

        $resolutionSummary = $totalMonthlyTickets > 0
            ? "Tỷ lệ giải quyết Ticket trong tháng đạt "
                . number_format($resolutionRate, 1) . "%."
            : "Tháng này chưa phát sinh Ticket.";

        $ratingSummary = $totalRatings > 0
            ? "Điểm đánh giá trung bình là "
                . number_format($avgRating, 2) . "/5. "
                . "Có " . number_format($positiveRate, 1)
                . "% đánh giá từ 4 sao trở lên."
            : "Chưa có đánh giá trong tháng.";

        $prioritySummary = $priorityCounts['high'] > 0
            ? "Có {$priorityCounts['high']} Ticket ưu tiên cao cần được quan tâm."
            : "Không có Ticket ưu tiên cao trong tháng.";

        return [
            'month' => $month,
            'year' => $year,

            // Ngày
            'labels' => array_map(
                fn ($day) => sprintf('%02d/%02d', $day, $month),
                $days
            ),

            // Ticket
            'created' => $created,
            'dailyStatus' => $dailyStatus,
            'statusCounts' => $statusCounts,
            'statuses' => $statuses,
            'priorityCounts' => $priorityCounts,
            'priorities' => $priorities,
            'totalMonthlyTickets' => $totalMonthlyTickets,
            'resolvedCount' => $resolvedCount,
            'resolutionRate' => $resolutionRate,

            // User
            'totalUsers' => $totalUsers,
            'customerCount' => $customerCount,
            'staffCount' => $staffCount,
            'adminCount' => $adminCount,
            'blockedUsers' => $blockedUsers,

            // Rating
            'ratingCounts' => $ratingCounts,
            'avgRating' => $avgRating,
            'totalRatings' => $totalRatings,
            'positiveRatings' => $positiveRatings,
            'positiveRate' => $positiveRate,

            // Reply
            'totalReplies' => $replies->count(),
            'dailyReplies' => $dailyReplies,

            // Activity
            'totalActivities' => $activities->count(),
            'activityCounts' => $activityCounts,

            // Resolution
            'avgResolutionHours' => $avgResolutionHours,

            // Global
            'totalTickets' => $totalTickets,

            // Text report
            'textReports' => [
                'status' => $statusSummary,
                'resolution' => $resolutionSummary,
                'rating' => $ratingSummary,
                'priority' => $prioritySummary,
            ],
        ];
    }

    /**
     * Hiển thị báo cáo và thống kê.
     */
    public function index(Request $request)
    {
        $month = min(max((int) $request->query('month', now()->month), 1), 12);
        $year = min(max((int) $request->query('year', now()->year), 2000), 2100);

        $report = $this->buildReport($month, $year);

        return view('staff.stats', $report);
    }

    /**
     * Xuất toàn bộ báo cáo CSV.
     */
    public function export(Request $request)
    {
        $month = min(max((int) $request->query('month', now()->month), 1), 12);
        $year = min(max((int) $request->query('year', now()->year), 2000), 2100);

        $report = $this->buildReport($month, $year);

        $filename = "bao-cao-thong-ke-{$month}-{$year}.csv";

        return response()->streamDownload(function () use ($report) {
            $output = fopen('php://output', 'w');

            // BOM để Excel đọc được tiếng Việt
            fwrite($output, "\xEF\xBB\xBF");

            // 1. Tổng quan
            fputcsv($output, ['BÁO CÁO TỔNG QUAN']);
            fputcsv($output, ['Thông số', 'Giá trị']);

            $summary = [
                ['Tổng User', $report['totalUsers']],
                ['Tổng Ticket toàn hệ thống', $report['totalTickets']],
                ['Ticket trong tháng', $report['totalMonthlyTickets']],
                ['Tỷ lệ giải quyết (%)', round($report['resolutionRate'], 2)],
                ['Số Ticket đã giải quyết/đóng', $report['resolvedCount']],
                ['Customer', $report['customerCount']],
                ['Staff', $report['staffCount']],
                ['Admin', $report['adminCount']],
                ['User bị khóa', $report['blockedUsers']],
                ['Tổng phản hồi trong tháng', $report['totalReplies']],
                ['Tổng hoạt động trong tháng', $report['totalActivities']],
                ['Số lượt đánh giá', $report['totalRatings']],
                ['Điểm đánh giá trung bình', round($report['avgRating'], 2)],
                ['Tỷ lệ đánh giá tích cực (%)', round($report['positiveRate'], 2)],
                ['Thời gian xử lý trung bình (giờ)', round($report['avgResolutionHours'], 2)],
            ];

            foreach ($summary as $row) {
                fputcsv($output, $row);
            }

            // 2. Ticket theo trạng thái
            fputcsv($output, []);
            fputcsv($output, ['TICKET THEO TRẠNG THÁI']);
            fputcsv($output, ['Trạng thái', 'Số lượng']);

            foreach ($report['statuses'] as $key => $label) {
                fputcsv($output, [
                    $label,
                    $report['statusCounts'][$key],
                ]);
            }

            // 3. Ticket theo mức độ ưu tiên
            fputcsv($output, []);
            fputcsv($output, ['TICKET THEO MỨC ĐỘ ƯU TIÊN']);
            fputcsv($output, ['Mức độ', 'Số lượng']);

            foreach ($report['priorities'] as $key => $label) {
                fputcsv($output, [
                    $label,
                    $report['priorityCounts'][$key],
                ]);
            }

            // 4. Đánh giá
            fputcsv($output, []);
            fputcsv($output, ['ĐÁNH GIÁ']);
            fputcsv($output, ['Số sao', 'Số lượng']);

            foreach ($report['ratingCounts'] as $index => $count) {
                fputcsv($output, [$index + 1, $count]);
            }

            // 5. Thống kê theo ngày
            fputcsv($output, []);
            fputcsv($output, ['THỐNG KÊ THEO NGÀY']);
            fputcsv($output, [
                'Ngày',
                'Ticket tạo mới',
                'Chờ giải quyết',
                'Đang giải quyết',
                'Đã giải quyết',
                'Đã đóng',
                'Phản hồi',
            ]);

            foreach ($report['labels'] as $index => $label) {
                fputcsv($output, [
                    $label,
                    $report['created'][$index],
                    $report['dailyStatus']['new'][$index],
                    $report['dailyStatus']['in_progress'][$index],
                    $report['dailyStatus']['solved'][$index],
                    $report['dailyStatus']['closed'][$index],
                    $report['dailyReplies'][$index],
                ]);
            }

            // 6. Hoạt động
            fputcsv($output, []);
            fputcsv($output, ['THỐNG KÊ HOẠT ĐỘNG']);
            fputcsv($output, ['Loại hoạt động', 'Số lượng']);

            foreach ($report['activityCounts'] as $action => $count) {
                fputcsv($output, [$action, $count]);
            }

            // 7. Báo cáo chữ
            fputcsv($output, []);
            fputcsv($output, ['NHẬN XÉT VÀ ĐÁNH GIÁ']);

            foreach ($report['textReports'] as $content) {
                fputcsv($output, [$content]);
            }

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
