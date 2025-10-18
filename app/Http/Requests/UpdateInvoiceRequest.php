<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'business_entity_id' => 'sometimes|required|integer|exists:business_entities,id',
            'invoice_number' => 'sometimes|required|string|max:50',
            'issue_date' => 'sometimes|required|date',
            'due_date' => 'sometimes|required|date',
            'variable_symbol' => 'nullable|string|max:50',
            'constant_symbol' => 'nullable|string|max:50',
            'specific_symbol' => 'nullable|string|max:50',
            'currency' => 'sometimes|required|string|max:3',
            'notes' => 'nullable|string',
            'status' => 'nullable|string|in:draft,sent,paid,overdue,cancelled',
            'items' => 'sometimes|required|array|min:1',
            'items.*.id' => 'nullable|integer|exists:invoice_items,id',
            'items.*.description' => 'required|string|max:500',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.vat_rate' => 'required|numeric|min:0|max:100',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'business_entity_id.exists' => 'The selected business entity does not exist.',
            'items.min' => 'At least one item is required.',
        ];
    }
}
