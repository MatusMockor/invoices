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
        $invoiceId = $this->route('invoice')?->id;

        return [
            // Client information
            'clientName' => 'sometimes|required|string|max:255',
            'clientIco' => 'sometimes|required|string|max:20',
            'clientDic' => 'sometimes|required|string|max:20',
            'clientIcDph' => 'nullable|string|max:20',
            'clientStreet' => 'sometimes|required|string|max:255',
            'clientCity' => 'sometimes|required|string|max:100',
            'clientPostalCode' => 'sometimes|required|string|max:20',
            'clientCountry' => 'nullable|string|max:2',

            // Invoice details
            'invoiceNumber' => 'sometimes|required|string|max:50|unique:invoices,invoice_number,'.$invoiceId,
            'issue_date' => 'sometimes|required|date',
            'due_date' => 'sometimes|required|date|after_or_equal:issue_date',
            'delivery_date' => 'sometimes|required|date',
            'variableSymbol' => 'nullable|string|max:50',
            'constantSymbol' => 'nullable|string|max:50',
            'specificSymbol' => 'nullable|string|max:50',
            'currency' => 'nullable|string|max:3',
            'notes' => 'nullable|string',
            'status' => 'nullable|string|in:draft,sent,paid,overdue,cancelled',

            // Invoice items
            'items' => 'sometimes|required|array|min:1',
            'items.*.id' => 'nullable|integer|exists:invoice_items,id',
            'items.*.description' => 'required|string|max:500',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.price' => 'required|numeric|min:0',
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
            'items.min' => 'At least one item is required.',
        ];
    }
}
