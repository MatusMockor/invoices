<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
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
            'business_entity_id' => 'required|integer|exists:business_entities,id',
            'invoice_number' => 'required|string|max:50',
            'issue_date' => 'required|date',
            'due_date' => 'required|date',
            'variable_symbol' => 'nullable|string|max:50',
            'constant_symbol' => 'nullable|string|max:50',
            'specific_symbol' => 'nullable|string|max:50',
            'currency' => 'required|string|max:3',
            'notes' => 'nullable|string',
            'status' => 'nullable|string|in:draft,sent,paid,overdue,cancelled',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:500',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.vat_rate' => 'required|numeric|min:0|max:100',
        ];
    }
}
