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
            'replied' => $count('replied'),
            'inProgress' => $count('in_progress'),
            'closed' => $count('closed'),
            'recent' => (clone $mine)->with('rating')->withCount('replies')->latest()->limit(5)->get(),
        ]);
    }
}
