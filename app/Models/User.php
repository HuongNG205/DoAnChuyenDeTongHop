<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
// use Database\Factories\UserFactory;
use App\Support\ListParams;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = ['name', 'email', 'password', 'role', 'is_blocked', 'blocked_reason'];
    protected $hidden = ['password', 'remember_token'];

    protected $table = 'users';
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            // 'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_blocked' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isStaff(): bool
    {
        return in_array($this->role, ['staff', 'admin'], true);
    }

    /** Ticket do user này tạo (khách hàng). */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'customer_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Reply::class);
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }

    /** Thông báo gửi tới user này. */
    public function notifications(): HasMany
    {
        return $this->hasMany(Activity::class, 'notify_user_id');
    }

    /** Bộ lọc danh sách user: role, status (0 hoạt động | 1 đã chặn), created_from, created_to. */
    public function scopeFilterBy(Builder $query, array $f): Builder
    {
        $role = ListParams::in($f['role'] ?? null, array_keys(config('helpdesk.roles')));
        $status = ListParams::in($f['status'] ?? null, array_keys(config('helpdesk.user_status')));
        $from = ListParams::date($f['created_from'] ?? null);
        $to = ListParams::date($f['created_to'] ?? null);

        return $query
            ->when($role !== null, fn ($q) => $q->where('role', $role))
            ->when($status !== null, fn ($q) => $q->where('is_blocked', (int) $status))
            ->when($from !== null, fn ($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to !== null, fn ($q) => $q->whereDate('created_at', '<=', $to));
    }

    /** Sắp xếp danh sách user (khóa: id, name, email, role, status, created_at). */
    public function scopeSortedBy(Builder $query, ?string $key, ?string $dir, string $defaultKey = 'id', string $defaultDir = 'asc'): Builder
    {
        return ListParams::sort($query, $key, $dir, [
            'id' => 'id',
            'name' => 'name',
            'email' => 'email',
            'role' => 'role',
            'status' => 'is_blocked',
            'created_at' => 'created_at',
        ], $defaultKey, $defaultDir);
    }
}
