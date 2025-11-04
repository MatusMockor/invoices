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
        $useCustomCompany = $this->boolean('useCustomCompany', false);

        $clientRequired = ! $useCustomCompany && $required ? 'required' : 'nullable';
        $customCompanyRequired = $useCustomCompany && $required ? 'required' : 'nullable';

        return [
            'useCustomCompany' => 'boolean',
            'clientName' => "{$clientRequired}|string|max:255",
            'clientIco' => "{$clientRequired}|string|max:20|regex:/^\d+$/",
            'clientDic' => 'nullable|string|max:20|regex:/^\d*$/',
            'clientIcDph' => 'nullable|string|max:20',
            'clientStreet' => "{$clientRequired}|string|max:255",
            'clientCity' => "{$clientRequired}|string|max:100",
            'clientPostalCode' => "{$clientRequired}|string|max:20",
            'clientCountry' => 'nullable|string|max:2',
            'customCompanyIco' => "{$customCompanyRequired}|string|max:12|regex:/^\d+$/",
            'customCompanyDic' => 'nullable|string|max:20|regex:/^\d*$/',
            'customCompanyIcDph' => 'nullable|string|max:20',
            'customCompanyName' => "{$customCompanyRequired}|string|max:255",
            'customCompanyAddress' => 'nullable|string|max:500',
            'customCompanyCity' => 'nullable|string|max:100',
            'customCompanyZip' => 'nullable|string|max:20',
            'customCompanyCountry' => 'nullable|string|max:100',
        ];
    }

    /**
     * Get invoice details validation rules.
     *
     * @return array<string, string|array<int, mixed>>
     */
    protected function getInvoiceDetailsValidationRules(bool $required = true, ?int $invoiceId = null): array
    {
        // Get the current user's supplier company ID
        $supplierCompanyId = auth()->user()?->currentCompany?->id;

        // Build unique rule that checks uniqueness per supplier_company_id
        $uniqueRule = \Illuminate\Validation\Rule::unique('invoices', 'invoice_number')
            ->where('supplier_company_id', $supplierCompanyId);

        if ($invoiceId) {
            $uniqueRule->ignore($invoiceId);
        }

        // Build invoice number validation rules
        $invoiceNumberRules = $required
            ? ['required', 'string', 'max:50', $uniqueRule]
            : ['sometimes', 'required', 'string', 'max:50', $uniqueRule];

        $requiredRule = $required ? 'required' : 'sometimes|required';

        return [
            'invoiceNumber' => $invoiceNumberRules,
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
