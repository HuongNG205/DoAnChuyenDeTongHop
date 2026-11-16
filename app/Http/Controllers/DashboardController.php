<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $mine = Ticket::where('customer_id', Auth::id());
        $count = fn (string $status) => (clone $mine)->where('status', $status)->count();

        return view('dashboard', [
            'total' => (clone $mine)->count(),
            'waiting' => $count('new'),
            'solved' => $count('solved'),
            'inProgress' => $count('in_progress'),
            'closed' => $count('closed'),
            // Chỉ gợi ý những ticket được đánh giá từ 3 sao trở lên.
            'recent' => Ticket::closed()
                ->ratedAtLeast(config('helpdesk.recent.min_stars'))
                ->with('rating')->withCount('replies')
                ->latest()
                ->limit(config('helpdesk.recent.limit'))
                ->get(),
        ]);
    }
}
