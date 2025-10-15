<?php

declare(strict_types=1);

namespace App\Modules\Attendance\Models;

use App\Models\Company;
use App\Models\User;
use App\Modules\Attendance\Factories\WorkScheduleFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'user_id',
        'day_of_week',
        'start_time',
        'end_time',
        'is_active',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): WorkScheduleFactory
    {
        return WorkScheduleFactory::new();
    }

    /**
     * Get the company that owns the work schedule.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the user that owns the work schedule.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include schedules for a specific company.
     */
    public function scopeByCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope a query to only include schedules for a specific user.
     */
    public function scopeByUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to only include active schedules.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the day name.
     */
    public function getDayNameAttribute(): string
    {
        return match ($this->day_of_week) {
            0 => 'Nedeľa',
            1 => 'Pondelok',
            2 => 'Utorok',
            3 => 'Streda',
            4 => 'Štvrtok',
            5 => 'Piatok',
            6 => 'Sobota',
            default => 'Unknown',
        };
    }

    /**
     * Get working hours for the day.
     */
    public function getWorkingHoursAttribute(): float
    {
        $start = \Carbon\Carbon::parse($this->start_time);
        $end = \Carbon\Carbon::parse($this->end_time);

        return $start->diffInHours($end, true);
    }
}
