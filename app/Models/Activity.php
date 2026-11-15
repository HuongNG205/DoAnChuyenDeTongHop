<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use App\Support\ListParams;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Activity extends Model
{
    use HasFactory, SoftDeletes;

    public const UPDATED_AT = null; // bảng activities chỉ có created_at

    protected $fillable = ['ticket_id', 'action', 'message', 'notify_user_id', 'is_read'];

    protected $table = 'activities';

    protected function casts(): array
    {
        return ['is_read' => 'boolean'];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class)->withTrashed();
    }

    /** Người nhận thông báo. */
    public function notifyUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'notify_user_id');
    }

    /** Bộ lọc lịch sử hoạt động: action, created_from, created_to. */
    public function scopeFilterBy(Builder $query, array $f): Builder
    {
        $action = ListParams::in($f['action'] ?? null, array_keys(config('helpdesk.activities')));
        $from = ListParams::date($f['created_from'] ?? null);
        $to = ListParams::date($f['created_to'] ?? null);

        return $query
            ->when($action !== null, fn ($q) => $q->where('activities.action', $action))
            ->when($from !== null, fn ($q) => $q->whereDate('activities.created_at', '>=', $from))
            ->when($to !== null, fn ($q) => $q->whereDate('activities.created_at', '<=', $to));
    }

    /** Sắp xếp lịch sử hoạt động (khóa: stt, created_at, user, action, message). */
    public function scopeSortedBy(Builder $query, ?string $key, ?string $dir, string $defaultKey = 'created_at', string $defaultDir = 'desc'): Builder
    {
        return ListParams::sort($query, $key, $dir, [
            'stt' => 'activities.id',
            'created_at' => 'activities.created_at',
            'user' => fn ($q, $d) => $q->orderByRaw("(select name from users where users.id = activities.notify_user_id) {$d}"),
            'action' => ListParams::rank('activities.action', array_keys(config('helpdesk.activities'))),
            'message' => 'activities.message',
        ], $defaultKey, $defaultDir);
    }
}
