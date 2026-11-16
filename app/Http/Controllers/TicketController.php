<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\UpdateTicketRequest;
use App\Services\TicketService;
use Illuminate\Http\RedirectResponse;

class TicketController extends Controller
{
    protected TicketService $ticketService;

    public function __construct(TicketService $ticketService)
    {
        $this->ticketService = $ticketService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $tickets = Ticket::with('rating')
            ->where('customer_id', $request->user()->id)
            ->search($request->query('q'))
            ->latest()
            ->paginate(10);

        return view('tickets.index', compact('tickets'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('tickets.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTicketRequest $request): RedirectResponse
    {
        // Quyền (customer) và dữ liệu (đã lọc HTML) đã được StoreTicketRequest xử lý.
        $ticket = $this->ticketService->create($request->user(), $request->validated());

        return redirect()->route('tickets.show', $ticket);
    }

    /**
     * Display the specified resource.
     */
    public function show(Ticket $ticket)
    {
        Gate::authorize('view', $ticket);

        $ticket->load(['customer', 'replies.user', 'rating']);

        return view('tickets.show', compact('ticket'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ticket $ticket)
    {
        Gate::authorize('update', $ticket);

        return view('tickets.edit', compact('ticket'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTicketRequest $request, Ticket $ticket): RedirectResponse
    {
        $this->ticketService->update($ticket, $request->user(), $request->validated());

        $redirect = $request->boolean('_inline') ? back() : redirect()->route('tickets.show', $ticket);

        return $redirect->with('status', "Đã cập nhật ticket {$ticket->track_id}.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Ticket $ticket): RedirectResponse
    {
        Gate::authorize('delete', $ticket);

        $this->ticketService->delete($ticket, $request->user());

        // Không quay lại trang ticket vừa xóa (sẽ 404) mà về danh sách tương ứng với vai trò.
        return redirect()
            ->route($request->user()->isStaff() ? 'staff.tickets.index' : 'tickets.index')
            ->with('status', "Đã xóa ticket {$ticket->track_id}.");
    }
}
