<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        // if (Auth::check()) {
        //     return redirect()->route(Auth::user()->isStaff() ? 'staff.tickets.index' : 'dashboard');
        // }

        /** @var User|null $user */
        $user = Auth::user();

        if ($user) {
            return redirect()->route(
                $user->isStaff() ? 'staff.tickets.index' : 'dashboard'
            );
        }

        $tickets = Ticket::with('rating')->withCount('replies')->latest()->paginate(10);

        return view('home', compact('tickets'));
    }
}
