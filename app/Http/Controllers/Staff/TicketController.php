<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ticket;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $tickets = Ticket::with(['customer', 'rating'])
            ->search($request->query('q'))
            ->filterBy($request->query())
            ->sortedBy($request->query('sort'), $request->query('dir'), 'updated_at', 'desc')
            ->paginate(10);

        return view('staff.tickets', compact('tickets'));
    }
}
