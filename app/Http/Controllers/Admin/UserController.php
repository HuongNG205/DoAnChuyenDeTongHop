<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\UpdateUserRequest;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;

class UserController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }
    
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
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->userService->update($user, $request->validated());

        $redirect = $request->boolean('_inline') ? back() : redirect()->route('admin.users.edit', $user);

        return $redirect->with('status', "Đã cập nhật tài khoản {$user->name}.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->withErrors(['user' => 'Bạn không thể xóa chính mình.']);
        }

        $this->userService->delete($user);

        return redirect()
            ->route('admin.users.index')
            ->with('status', "Đã xóa tài khoản {$user->name}.");
    }
}
