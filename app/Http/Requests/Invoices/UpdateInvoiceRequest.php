<?php

namespace App\Http\Requests\Invoices;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'ico' => 'required|string|max:12',
            'invoice_number' => [
                'required',
                'string',
                'max:20',
                Rule::unique('invoices', 'invoice_number')->ignore($this->route('invoice')),
            ],
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
            'delivery_date' => 'required|date',
            'total_amount' => 'required|numeric|min:0',
            'currency' => 'required|string|in:EUR,USD,CZK',
            'constant_symbol' => 'nullable|string|max:4',
            'note' => 'nullable|string|max:1000',
            'status' => 'required|string|in:draft,sent,paid,overdue',
            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable|integer|exists:invoice_items,id',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'ico' => 'IČO',
            'invoice_number' => 'číslo faktúry',
            'issue_date' => 'dátum vystavenia',
            'due_date' => 'dátum splatnosti',
            'delivery_date' => 'dátum dodania',
            'total_amount' => 'celková suma',
            'currency' => 'mena',
            'note' => 'poznámka',
            'status' => 'stav faktúry',
            'items' => 'položky faktúry',
            'items.*.description' => 'popis položky',
            'items.*.quantity' => 'množstvo',
            'items.*.unit_price' => 'jednotková cena',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'items.min' => 'Faktúra musí obsahovať aspoň jednu položku.',
            'due_date.after_or_equal' => 'Dátum splatnosti musí byť rovnaký alebo neskorší ako dátum vystavenia.',
        ];
    }
}
