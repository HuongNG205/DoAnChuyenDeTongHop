<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use Illuminate\Http\Request;
use App\Http\Requests\StoreRatingRequest;
use App\Models\Ticket;
use App\Services\TicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class RatingController extends Controller
{
    protected TicketService $ticketService;

    public function __construct(TicketService $ticketService)
    {
        $this->ticketService = $ticketService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Lưu đánh giá của customer. Quyền (chủ ticket, role customer, ticket đã giải quyết/đóng)
     * và dữ liệu (1-5 sao) do StoreRatingRequest xử lý.
     */
    public function store(StoreRatingRequest $request, Ticket $ticket): RedirectResponse
    {
        $this->ticketService->rate($ticket, $request->user(), $request->validated('stars'));

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('status', 'Cảm ơn bạn đã đánh giá.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Rating $rating)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Rating $rating)
    {
        //
    }

    /** Cập nhật số sao (cùng quy tắc với store). */
    public function update(StoreRatingRequest $request, Ticket $ticket): RedirectResponse
    {
        $this->ticketService->rate($ticket, $request->user(), $request->validated('stars'));

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('status', 'Đã cập nhật đánh giá.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Ticket $ticket): RedirectResponse
    {
        Gate::authorize('rate', $ticket);

        $this->ticketService->removeRating($ticket, $request->user());

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('status', 'Đã xóa đánh giá.');
    }
}
