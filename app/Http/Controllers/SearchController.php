<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        // Thanh tìm kiếm trên header: chỉ tìm theo tiêu đề, và chỉ các ticket đã closed.
        $tickets = Ticket::closed()->with('rating')->withCount('replies')
            ->searchTitle($request->query('q'))
            ->latest()
            ->paginate(10);

        return view('search.index', compact('tickets'));
    }
}
