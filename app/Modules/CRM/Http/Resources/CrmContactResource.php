<?php

declare(strict_types=1);

namespace App\Modules\CRM\Http\Resources;

use App\Http\Resources\UserResource;
use App\Modules\CRM\Models\CrmContact;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin CrmContact
 */
class CrmContactResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'primary_email' => $this->primary_email?->email,
            'primary_phone' => $this->primary_phone?->phone,
            'job_title' => $this->job_title,
            'company_id' => $this->company_id,
            'user_id' => $this->user_id,
            'is_active' => $this->is_active,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'status_color' => $this->status->color(),
            'last_contacted_at' => $this->last_contacted_at?->toISOString(),
            'metadata' => $this->metadata,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),

            'company' => new CompanyResource($this->whenLoaded('company')),
            'user' => new UserResource($this->whenLoaded('user')),
            'emails' => ContactEmailResource::collection($this->whenLoaded('emails')),
            'phones' => ContactPhoneResource::collection($this->whenLoaded('phones')),
            'addresses' => ContactAddressResource::collection($this->whenLoaded('addresses')),
            'tags' => ContactTagResource::collection($this->whenLoaded('tags')),
            'custom_field_values' => ContactCustomFieldValueResource::collection($this->whenLoaded('customFieldValues')),
            'activities' => ContactActivityResource::collection($this->whenLoaded('activities')),
            'notes' => NoteResource::collection($this->whenLoaded('notes')),
        ];
    }
}
