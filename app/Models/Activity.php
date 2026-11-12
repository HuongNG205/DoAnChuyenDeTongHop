<?php

namespace App\Models;

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
}
