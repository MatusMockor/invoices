<?php

declare(strict_types=1);

namespace App\Http\Requests\Concerns;

trait HasInvoiceValidationRules
{
    /**
     * Get client information validation rules.
     *
     * @return array<string, string>
     */
    protected function getClientValidationRules(bool $required = true): array
    {
        $requiredRule = $required ? 'required' : 'sometimes|required';

        return [
            'clientName' => "{$requiredRule}|string|max:255",
            'clientIco' => "{$requiredRule}|string|max:20",
            'clientDic' => "{$requiredRule}|string|max:20",
            'clientIcDph' => 'nullable|string|max:20',
            'clientStreet' => "{$requiredRule}|string|max:255",
            'clientCity' => "{$requiredRule}|string|max:100",
            'clientPostalCode' => "{$requiredRule}|string|max:20",
            'clientCountry' => 'nullable|string|max:2',
        ];
    }

    /**
     * Get invoice details validation rules.
     *
     * @return array<string, string|array<int, mixed>>
     */
    protected function getInvoiceDetailsValidationRules(bool $required = true, ?int $invoiceId = null): array
    {
        $requiredRule = $required ? 'required' : 'sometimes|required';
        $uniqueRule = $invoiceId
            ? "unique:invoices,invoice_number,{$invoiceId}"
            : 'unique:invoices,invoice_number';

        return [
            'invoiceNumber' => "{$requiredRule}|string|max:50|{$uniqueRule}",
            'issue_date' => "{$requiredRule}|date",
            'due_date' => "{$requiredRule}|date|after_or_equal:issue_date",
            'delivery_date' => "{$requiredRule}|date",
            'variableSymbol' => 'nullable|string|max:50',
            'constantSymbol' => 'nullable|string|max:50',
            'specificSymbol' => 'nullable|string|max:50',
            'currency' => 'nullable|string|max:3',
            'notes' => 'nullable|string',
            'status' => 'nullable|string|in:draft,sent,paid,overdue,cancelled',
        ];
    }

    /**
     * Get invoice items validation rules.
     *
     * @return array<string, string|array<int, mixed>>
     */
    protected function getInvoiceItemsValidationRules(bool $required = true, ?int $invoiceId = null): array
    {
        $requiredRule = $required ? 'required' : 'sometimes|required';

        $rules = [
            'items' => "{$requiredRule}|array|min:1",
            'items.*.description' => 'required|string|max:500',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.price' => 'required|numeric|min:0',
        ];

        if ($invoiceId !== null) {
            $rules['items.*.id'] = [
                'nullable',
                'integer',
                \Illuminate\Validation\Rule::exists('invoice_items', 'id')
                    ->where('invoice_id', $invoiceId),
            ];
        } else {
            $rules['items.*.id'] = 'nullable|integer|exists:invoice_items,id';
        }

        return $rules;
    }
}
