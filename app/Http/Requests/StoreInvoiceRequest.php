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
            // Client information
            'clientName' => 'required|string|max:255',
            'clientIco' => 'required|string|max:20',
            'clientDic' => 'required|string|max:20',
            'clientIcDph' => 'nullable|string|max:20',
            'clientStreet' => 'required|string|max:255',
            'clientCity' => 'required|string|max:100',
            'clientPostalCode' => 'required|string|max:20',
            'clientCountry' => 'nullable|string|max:2',

            // Invoice details
            'invoiceNumber' => 'required|string|max:50|unique:invoices,invoice_number',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
            'delivery_date' => 'required|date',
            'variableSymbol' => 'nullable|string|max:50',
            'constantSymbol' => 'nullable|string|max:50',
            'specificSymbol' => 'nullable|string|max:50',
            'currency' => 'nullable|string|max:3',
            'notes' => 'nullable|string',
            'status' => 'nullable|string|in:draft,sent,paid,overdue,cancelled',

            // Invoice items
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:500',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.price' => 'required|numeric|min:0',
        ];
    }
}
