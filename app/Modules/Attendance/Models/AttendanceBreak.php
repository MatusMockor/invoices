<?php

declare(strict_types=1);

namespace App\Modules\Attendance\Models;

use App\Modules\Attendance\Enums\BreakType;
use App\Modules\Attendance\Factories\AttendanceBreakFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceBreak extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'break_start',
        'break_end',
        'break_type',
        'note',
        'duration_minutes',
    ];

    protected $casts = [
        'break_start' => 'datetime',
        'break_end' => 'datetime',
        'break_type' => BreakType::class,
        'duration_minutes' => 'integer',
    ];

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): AttendanceBreakFactory
    {
        return AttendanceBreakFactory::new();
    }

    /**
     * Get the attendance that owns the break.
     */
    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    /**
     * Check if the break is still active (not ended).
     */
    public function isActive(): bool
    {
        return is_null($this->break_end);
    }

    /**
     * Calculate the duration of the break in minutes.
     */
    public function calculateDuration(): int
    {
        if (is_null($this->break_end)) {
            return 0;
        }

        return $this->break_start->diffInMinutes($this->break_end);
    }

    /**
     * Get the formatted duration.
     */
    public function getFormattedDurationAttribute(): string
    {
        if (is_null($this->duration_minutes)) {
            return '--:--';
        }

        $hours = floor($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;

        return sprintf('%02d:%02d', $hours, $minutes);
    }
}
