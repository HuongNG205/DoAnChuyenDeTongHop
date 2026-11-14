<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReplyRequest;
use App\Models\Ticket;
use App\Models\Reply;
use Illuminate\Http\Request;
use App\Services\TicketService;
use Illuminate\Http\RedirectResponse;

class ReplyController extends Controller
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
     * Trả lời ticket (dùng chung cho customer và staff/admin).
     * Quyền (chủ ticket hoặc staff, ticket chưa đóng) và việc lọc HTML do StoreReplyRequest xử lý.
     */
    public function store(StoreReplyRequest $request, Ticket $ticket): RedirectResponse
    {
        $this->ticketService->reply($ticket, $request->user(), $request->validated('message'));

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('status', 'Đã gửi trả lời.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Reply $reply)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Reply $reply)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Reply $reply)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Reply $reply)
    {
        //
    }
}
