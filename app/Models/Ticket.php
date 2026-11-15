<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

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
}
