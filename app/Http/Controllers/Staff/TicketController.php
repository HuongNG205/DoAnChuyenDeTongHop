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
            ->latest('updated_at')
            ->paginate(10);

        return view('staff.tickets', compact('tickets'));
    }
}
