<?php

declare(strict_types=1);

namespace App\Modules\Attendance\Models;

use App\Models\Company;
use App\Models\User;
use App\Modules\Attendance\Enums\AttendanceStatus;
use App\Modules\Attendance\Enums\WorkType;
use App\Modules\Attendance\Factories\AttendanceFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attendance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'user_id',
        'check_in',
        'check_out',
        'work_type',
        'status',
        'note',
        'check_in_ip',
        'check_out_ip',
        'check_in_latitude',
        'check_in_longitude',
        'check_out_latitude',
        'check_out_longitude',
        'total_minutes',
        'approved_by',
        'approved_at',
        'approval_note',
    ];

    protected $casts = [
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'approved_at' => 'datetime',
        'work_type' => WorkType::class,
        'status' => AttendanceStatus::class,
        'check_in_latitude' => 'decimal:8',
        'check_in_longitude' => 'decimal:8',
        'check_out_latitude' => 'decimal:8',
        'check_out_longitude' => 'decimal:8',
        'total_minutes' => 'integer',
    ];

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): AttendanceFactory
    {
        return AttendanceFactory::new();
    }

    /**
     * Get the company that owns the attendance.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the user (employee) that owns the attendance.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who approved the attendance.
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the breaks for the attendance.
     */
    public function breaks(): HasMany
    {
        return $this->hasMany(AttendanceBreak::class);
    }

    /**
     * Scope a query to only include attendances for a specific company.
     */
    public function scopeByCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope a query to only include attendances for a specific user.
     */
    public function scopeByUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to only include attendances within a date range.
     */
    public function scopeDateRange(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereBetween('check_in', [$startDate, $endDate]);
    }

    /**
     * Scope a query to only include attendances with a specific status.
     */
    public function scopeByStatus(Builder $query, AttendanceStatus $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include attendances that are still active (not checked out).
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('check_out');
    }

    /**
     * Check if the attendance is currently active (not checked out).
     */
    public function isActive(): bool
    {
        return is_null($this->check_out);
    }

    /**
     * Get the total working hours (formatted).
     */
    public function getFormattedWorkingHoursAttribute(): string
    {
        if (is_null($this->total_minutes)) {
            return '--:--';
        }

        $hours = floor($this->total_minutes / 60);
        $minutes = $this->total_minutes % 60;

        return sprintf('%02d:%02d', $hours, $minutes);
    }

    /**
     * Calculate total minutes including breaks.
     */
    public function calculateTotalMinutes(): int
    {
        if (is_null($this->check_out)) {
            return 0;
        }

        $totalMinutes = $this->check_in->diffInMinutes($this->check_out);
        $breakMinutes = $this->breaks()->sum('duration_minutes') ?? 0;

        return (int) ($totalMinutes - $breakMinutes);
    }
}
