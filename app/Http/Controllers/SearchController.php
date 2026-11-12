<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $tickets = Ticket::with('rating')->withCount('replies')
            ->search($request->query('q'))
            // Khách hàng chỉ tìm trong ticket của mình; khách vãng lai và staff/admin tìm trong tất cả
            ->when($user && ! $user->isStaff(), fn ($q) => $q->where('customer_id', $user->id))
            ->latest()
            ->paginate(10);

        return view('search.index', compact('tickets'));
    }
}
