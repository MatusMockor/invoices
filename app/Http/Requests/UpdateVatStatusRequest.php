<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\VatPayerStatus;
use App\Enums\VatPeriod;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateVatStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'vat_status' => ['required', Rule::in(VatPayerStatus::values())],
            'vat_period' => ['nullable', Rule::in(VatPeriod::values())],
            'valid_from' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $status = $this->getVatStatus();

            // VAT payers must have a VAT period
            if ($status->requiresVatPeriod() && $this->getVatPeriod() === null) {
                $validator->errors()->add(
                    'vat_period',
                    'Platca DPH musí mať určené zdaňovacie obdobie (mesačné alebo štvrťročné).'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'vat_status.required' => 'Status DPH je povinný.',
            'vat_status.in' => 'Neplatný status DPH.',
            'vat_period.in' => 'Neplatné zdaňovacie obdobie.',
            'valid_from.required' => 'Dátum platnosti je povinný.',
            'valid_from.date' => 'Neplatný formát dátumu.',
            'notes.max' => 'Poznámka môže mať maximálne 1000 znakov.',
        ];
    }

    public function getVatStatus(): VatPayerStatus
    {
        return VatPayerStatus::from($this->validated('vat_status'));
    }

    public function getVatPeriod(): ?VatPeriod
    {
        $value = $this->validated('vat_period');

        return $value ? VatPeriod::from($value) : null;
    }

    public function getValidFrom(): Carbon
    {
        return Carbon::parse($this->validated('valid_from'));
    }

    public function getNotes(): ?string
    {
        return $this->validated('notes');
    }
}
