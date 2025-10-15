<?php

declare(strict_types=1);

namespace App\Modules\Attendance\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceBreakResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'attendance_id' => $this->attendance_id,
            'break_start' => $this->break_start->toIso8601String(),
            'break_start_formatted' => $this->break_start->format('H:i'),
            'break_end' => $this->break_end?->toIso8601String(),
            'break_end_formatted' => $this->break_end?->format('H:i'),
            'break_type' => $this->break_type->value,
            'break_type_label' => $this->break_type->label(),
            'note' => $this->note,
            'duration_minutes' => $this->duration_minutes,
            'formatted_duration' => $this->formatted_duration,
            'is_active' => $this->isActive(),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
