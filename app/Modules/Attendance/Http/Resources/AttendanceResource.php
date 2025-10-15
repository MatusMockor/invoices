<?php

declare(strict_types=1);

namespace App\Modules\Attendance\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
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
            'user_id' => $this->user_id,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ],
            'check_in' => $this->check_in->toIso8601String(),
            'check_in_formatted' => $this->check_in->format('d.m.Y H:i'),
            'check_out' => $this->check_out?->toIso8601String(),
            'check_out_formatted' => $this->check_out?->format('d.m.Y H:i'),
            'work_type' => $this->work_type->value,
            'work_type_label' => $this->work_type->label(),
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'status_color' => $this->status->color(),
            'note' => $this->note,
            'total_minutes' => $this->total_minutes,
            'formatted_working_hours' => $this->formatted_working_hours,
            'is_active' => $this->isActive(),
            'breaks' => AttendanceBreakResource::collection($this->whenLoaded('breaks')),
            'approved_by' => $this->when($this->approved_by, [
                'id' => $this->approvedBy?->id,
                'name' => $this->approvedBy?->name,
            ]),
            'approved_at' => $this->approved_at?->toIso8601String(),
            'approval_note' => $this->approval_note,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
