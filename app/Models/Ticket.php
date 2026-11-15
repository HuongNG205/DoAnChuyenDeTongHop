<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Support\ListParams;

class Ticket extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'track_id', 'customer_id', 'title', 'message', 'priority', 'status', 'closed_at',
    ];

    protected $table = 'tickets';

    protected function casts(): array
    {
        return ['closed_at' => 'datetime'];
    }

    /** Sinh mã dạng ABC-123-XYZ, không trùng. */
    public static function generateTrackId(): string
    {
        $letters = fn () => collect(range('A', 'Z'))->random(3)->implode('');

        do {
            $id = $letters() . '-' . random_int(100, 999) . '-' . $letters();
        } while (static::withTrashed()->where('track_id', $id)->exists());

        return $id;
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Reply::class)->oldest('created_at');
    }

    public function rating(): HasOne
    {
        return $this->hasOne(Rating::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    /** Chỉ các Ticket đã đóng (hiển thị công khai ở trang chủ, "Gần đây", tìm kiếm trên header). */
    public function scopeClosed(Builder $query): Builder
    {
        return $query->where('status', 'closed');
    }

    /** Thanh tìm kiếm trên header: chỉ tìm theo tiêu đề. */
    public function scopeSearchTitle(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        return $query->when($term !== '', fn ($q) => $q->where('title', 'like', "%{$term}%"));
    }

    /**
     * Thanh tìm kiếm trong các trang danh sách: tìm theo mã ticket, tiêu đề và trạng thái.
     * Trạng thái có thể gõ theo mã (in_progress) hoặc theo nhãn tiếng Việt ("Đang giải quyết").
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        $statuses = collect(config('helpdesk.status'))
            ->filter(fn ($label, $key) => mb_stripos($label, $term) !== false || mb_stripos($key, $term) !== false)
            ->keys();

        return $query->where(fn ($q) => $q
            ->where('track_id', 'like', "%{$term}%")
            ->orWhere('title', 'like', "%{$term}%")
            ->orWhereIn('status', $statuses));
    }

    /**
     * Bộ lọc cho các trang danh sách ticket (đọc từ query string):
     * status, priority, rating (none|1..5), created_from, created_to.
     */
    public function scopeFilterBy(Builder $query, array $f): Builder
    {
        $status = ListParams::in($f['status'] ?? null, array_keys(config('helpdesk.status')));
        $priority = ListParams::in($f['priority'] ?? null, array_keys(config('helpdesk.priority')));
        $rating = ListParams::in($f['rating'] ?? null, ['none', 1, 2, 3, 4, 5]);
        $from = ListParams::date($f['created_from'] ?? null);
        $to = ListParams::date($f['created_to'] ?? null);

        return $query
            ->when($status !== null, fn ($q) => $q->where('status', $status))
            ->when($priority !== null, fn ($q) => $q->where('priority', $priority))
            ->when($rating === 'none', fn ($q) => $q->whereDoesntHave('rating'))
            ->when($rating !== null && $rating !== 'none', fn ($q) => $q->whereHas('rating', fn ($r) => $r->where('stars', (int) $rating)))
            ->when($from !== null, fn ($q) => $q->whereDate('tickets.created_at', '>=', $from))
            ->when($to !== null, fn ($q) => $q->whereDate('tickets.created_at', '<=', $to));
    }

    /** Sắp xếp theo cột của bảng danh sách (khóa: track_id, title, customer, status, priority, created_at, updated_at, rating). */
    public function scopeSortedBy(Builder $query, ?string $key, ?string $dir, string $defaultKey = 'created_at', string $defaultDir = 'desc'): Builder
    {
        return ListParams::sort($query, $key, $dir, [
            'track_id' => 'tickets.track_id',
            'title' => 'tickets.title',
            'customer' => fn ($q, $d) => $q->orderByRaw("(select name from users where users.id = tickets.customer_id) {$d}"),
            'status' => ListParams::rank('tickets.status', array_keys(config('helpdesk.status'))),
            'priority' => ListParams::rank('tickets.priority', array_keys(config('helpdesk.priority'))),
            'created_at' => 'tickets.created_at',
            'updated_at' => 'tickets.updated_at',
            'rating' => fn ($q, $d) => $q->orderByRaw("coalesce((select stars from ratings where ratings.ticket_id = tickets.id and ratings.deleted_at is null), 0) {$d}"),
        ], $defaultKey, $defaultDir);
    }
}
