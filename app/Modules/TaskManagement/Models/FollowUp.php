<?php

declare(strict_types=1);

namespace App\Modules\TaskManagement\Models;

use App\Models\User;
use App\Modules\TaskManagement\Factories\FollowUpFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FollowUp extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'user_id',
        'note',
        'follow_up_date',
        'is_completed',
        'completed_at',
    ];

    protected $casts = [
        'follow_up_date' => 'datetime',
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    protected static function newFactory(): FollowUpFactory
    {
        return FollowUpFactory::new();
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('is_completed', true);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('is_completed', false);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('follow_up_date', '<', now())
            ->where('is_completed', false);
    }

    public function scopeDueToday(Builder $query): Builder
    {
        return $query->whereDate('follow_up_date', today())
            ->where('is_completed', false);
    }

    public function markAsCompleted(): void
    {
        $this->is_completed = true;
        $this->completed_at = now();
        $this->save();
    }
}
