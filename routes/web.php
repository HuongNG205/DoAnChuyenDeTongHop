<?php

use App\Http\Controllers\Admin\ActivityController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ReplyController;
use App\Http\Controllers\RatingController;
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

// Xem chi tiết ticket: công khai với ticket đã đóng (TicketPolicy::view). Không đăng nhập vẫn xem được,
// nhưng sửa / xóa / trả lời / đánh giá vẫn nằm trong nhóm "auth" bên dưới và bị policy chặn.
// whereNumber để không nuốt /tickets/create.
Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->whereNumber('ticket')->name('tickets.show');

// ---------- Đăng nhập / đăng ký ----------
Route::middleware('guest')->group(function () {
    Route::view('/login', 'auth.login')->name('login');
    Route::view('/register', 'auth.register')->name('register');
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ---------- Đã đăng nhập ----------
Route::middleware('auth')->group(function () use ($stub) {

    // Customer
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/create', [TicketController::class, 'create'])->name('tickets.create');
    Route::get('/tickets/{ticket}/edit', [TicketController::class, 'edit'])->name('tickets.edit');
    // Mỗi lần tạo ticket / reply có thể gửi email => giới hạn 10 lần/phút/người dùng để chống spam.
    Route::post('/tickets', [TicketController::class, 'store'])->middleware('throttle:10,1')->name('tickets.store');
    // Customer không được cập nhật ticket: chặn bằng TicketPolicy::update (chỉ staff/admin).
    Route::put('/tickets/{ticket}', $stub)->middleware('can:update,ticket')->name('tickets.update');
    Route::delete('/tickets/{ticket}', [TicketController::class, 'destroy'])->name('tickets.destroy');
    Route::post('/tickets/{ticket}/reply', [ReplyController::class, 'store'])->middleware('throttle:10,1')->name('tickets.reply');
    // Đánh giá: chỉ customer là chủ ticket (TicketPolicy::rate).
    Route::post('/tickets/{ticket}/rating', [RatingController::class, 'store'])->name('tickets.rating.store');
    Route::put('/tickets/{ticket}/rating', [RatingController::class, 'update'])->name('tickets.rating.update');
    Route::delete('/tickets/{ticket}/rating', [RatingController::class, 'destroy'])->name('tickets.rating.destroy');

    // Tài khoản
    Route::get('/account', [AccountController::class, 'edit'])->name('account.edit');
    Route::put('/account', [AccountController::class, 'update'])->name('account.update');
    // Giới hạn 5 lần/phút để chống dò mật khẩu cũ.
    Route::put('/account/password', [AccountController::class, 'password'])->middleware('throttle:5,1')->name('account.password');

    // Staff + admin
    Route::middleware(RoleMiddleware::class . ':staff,admin')->prefix('staff')->group(function () use ($stub) {
        Route::get('/tickets', [StaffTicketController::class, 'index'])->name('staff.tickets.index');
        Route::get('/stats', [StatsController::class, 'index'])->name('staff.stats');
        Route::get('/stats/export', [StatsController::class, 'export'])->name('staff.stats.export');
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