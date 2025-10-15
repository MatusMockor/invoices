<?php

declare(strict_types=1);

namespace App\Modules\TaskManagement\Models;

use App\Models\Company;
use App\Models\User;
use App\Modules\TaskManagement\Enums\TaskPriority;
use App\Modules\TaskManagement\Enums\TaskStatus;
use App\Modules\TaskManagement\Factories\TaskFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'user_id',
        'assigned_to',
        'taskable_id',
        'taskable_type',
        'title',
        'description',
        'status',
        'priority',
        'due_date',
        'completed_at',
        'metadata',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'completed_at' => 'datetime',
        'metadata' => 'array',
        'status' => TaskStatus::class,
        'priority' => TaskPriority::class,
    ];

    protected static function newFactory(): TaskFactory
    {
        return TaskFactory::new();
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function taskable(): MorphTo
    {
        return $this->morphTo();
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(FollowUp::class);
    }

    public function scopeByCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeByStatus(Builder $query, TaskStatus $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeByPriority(Builder $query, TaskPriority $priority): Builder
    {
        return $query->where('priority', $priority);
    }

    public function scopeAssignedTo(Builder $query, int $userId): Builder
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeCreatedBy(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('due_date', '<', now())
            ->whereNotIn('status', [TaskStatus::COMPLETED, TaskStatus::CANCELLED]);
    }

    public function scopeDueToday(Builder $query): Builder
    {
        return $query->whereDate('due_date', today())
            ->whereNotIn('status', [TaskStatus::COMPLETED, TaskStatus::CANCELLED]);
    }

    public function scopeDueThisWeek(Builder $query): Builder
    {
        return $query->whereBetween('due_date', [now()->startOfWeek(), now()->endOfWeek()])
            ->whereNotIn('status', [TaskStatus::COMPLETED, TaskStatus::CANCELLED]);
    }

    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function (Builder $q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        });
    }

    public function isOverdue(): bool
    {
        if (! $this->due_date) {
            return false;
        }

        return $this->due_date->isPast() &&
            ! in_array($this->status, [TaskStatus::COMPLETED, TaskStatus::CANCELLED]);
    }

    public function markAsCompleted(): void
    {
        $this->status = TaskStatus::COMPLETED;
        $this->completed_at = now();
        $this->save();
    }

    public function markAsInProgress(): void
    {
        $this->status = TaskStatus::IN_PROGRESS;
        $this->save();
    }

    public function markAsCancelled(): void
    {
        $this->status = TaskStatus::CANCELLED;
        $this->save();
    }
}
