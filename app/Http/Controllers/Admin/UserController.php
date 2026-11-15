<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $term = trim((string) $request->query('q'));

        // Trạng thái: gõ nhãn tiếng Việt ("Hoạt động", "Đã chặn") -> lọc theo is_blocked.
        $blockedFlags = collect(config('helpdesk.user_status'))
            ->filter(fn ($label) => $term !== '' && mb_stripos($label, $term) !== false)
            ->keys();

        $users = User::query()
            ->when($term !== '', fn ($q) => $q->where(fn ($q) => $q
                ->when(ctype_digit($term), fn ($q) => $q->orWhere('id', (int) $term))
                ->orWhere('name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhereIn('is_blocked', $blockedFlags)))
            ->filterBy($request->query())
            ->sortedBy($request->query('sort'), $request->query('dir'))
            ->paginate(10);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, User $user)
    {
        $tickets = $user->tickets()->with('rating')
            ->sortedBy($request->query('sort'), $request->query('dir'))
            ->paginate(5);

        return view('admin.users.edit', compact('user', 'tickets'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //
    }
}
