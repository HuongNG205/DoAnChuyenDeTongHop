<?php

use App\Http\Controllers\Admin\ActivityController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\Staff\StatsController;
use App\Http\Controllers\Staff\TicketController as StaffTicketController;
use App\Http\Controllers\TicketController;
use App\Http\Middleware\RoleMiddleware;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web routes
|--------------------------------------------------------------------------
| Các controller hiện chỉ có hàm hiện giao diện (index, show, create, edit).
| Các route gửi form (POST/PUT/DELETE) tạm dùng $stub để view không bị lỗi
| route() — thay bằng store/update/destroy khi làm chức năng xử lý.
*/

$stub = fn () => back()->with('status', 'Chức năng này chưa được xử lý.');

// ---------- Chỉ dùng khi dev: đăng nhập nhanh bằng tài khoản đã seed ----------
// /demo/guest   /demo/customer   /demo/staff   /demo/admin
if (app()->environment('local')) {
    Route::get('/demo/{role}', function (string $role) {
        abort_unless(in_array($role, ['guest', 'customer', 'staff', 'admin']), 404);

        if ($role === 'guest') {
            Auth::logout();
        } else {
            Auth::login(User::where('email', $role . '@gmail.com')->firstOrFail());
        }

        return redirect('/');
    })->name('demo');
}

// ---------- Trang chủ, tìm kiếm ----------
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/search', [SearchController::class, 'index'])->name('search');

// ---------- Đăng nhập / đăng ký ----------
Route::middleware('guest')->group(function () use ($stub) {
    Route::view('/login', 'auth.login')->name('login');
    Route::view('/register', 'auth.register')->name('register');
    Route::post('/login', $stub);
    Route::post('/register', $stub);
    Route::get('/auth/google', $stub)->name('auth.google.redirect');
});

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/');
})->name('logout');

// ---------- Đã đăng nhập ----------
Route::middleware('auth')->group(function () use ($stub) {

    // Customer
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/create', [TicketController::class, 'create'])->name('tickets.create');
    Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
    Route::get('/tickets/{ticket}/edit', [TicketController::class, 'edit'])->name('tickets.edit');
    Route::post('/tickets', $stub)->name('tickets.store');
    Route::put('/tickets/{ticket}', $stub)->name('tickets.update');
    Route::delete('/tickets/{ticket}', $stub)->name('tickets.destroy');
    Route::post('/tickets/{ticket}/reply', $stub)->name('tickets.reply');
    Route::post('/tickets/{ticket}/rating', $stub)->name('tickets.rating.store');
    Route::put('/tickets/{ticket}/rating', $stub)->name('tickets.rating.update');
    Route::delete('/tickets/{ticket}/rating', $stub)->name('tickets.rating.destroy');

    // Tài khoản
    Route::get('/account', [AccountController::class, 'edit'])->name('account.edit');
    Route::put('/account', $stub)->name('account.update');
    Route::put('/account/password', $stub)->name('account.password');

    // Staff + admin
    Route::middleware(RoleMiddleware::class . ':staff,admin')->prefix('staff')->group(function () use ($stub) {
        Route::get('/tickets', [StaffTicketController::class, 'index'])->name('staff.tickets.index');
        Route::get('/stats', [StatsController::class, 'index'])->name('staff.stats');
        Route::get('/stats/export', $stub)->name('staff.stats.export');
    });

    // Chỉ admin
    Route::middleware(RoleMiddleware::class . ':admin')->prefix('admin')->group(function () use ($stub) {
        Route::get('/users', [UserController::class, 'index'])->name('admin.users.index');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('admin.users.edit');
        Route::put('/users/{user}', $stub)->name('admin.users.update');
        Route::delete('/users/{user}', $stub)->name('admin.users.destroy');

        Route::get('/activities', [ActivityController::class, 'index'])->name('admin.activities.index');
        Route::delete('/activities/{activity}', $stub)->name('admin.activities.destroy');
    });
});