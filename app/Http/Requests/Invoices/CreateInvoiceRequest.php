<?php

declare(strict_types=1);

namespace App\Http\Requests\Invoices;

use App\Enums\InvoiceStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class CreateInvoiceRequest extends FormRequest
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
        $useCustomCompany = $this->boolean('useCustomCompany', false);

        return [
            'invoiceNumber' => 'required|string|max:20|unique:invoices,invoice_number',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
            'delivery_date' => 'required|date',
            'currency' => 'nullable|string|in:EUR,USD,CZK',
            'variableSymbol' => 'nullable|string|max:20',
            'constantSymbol' => 'nullable|string|max:20',
            'specificSymbol' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:1000',
            'status' => ['required', Rule::enum(InvoiceStatus::class)],
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'useCustomCompany' => 'boolean',
            'customCompanyIco' => $useCustomCompany ? 'required|string|max:12|regex:/^\d+$/' : 'nullable|string|max:12|regex:/^\d+$/',
            'customCompanyDic' => 'nullable|string|max:20|regex:/^\d*$/',
            'customCompanyIcDph' => 'nullable|string|max:20',
            'customCompanyName' => $useCustomCompany ? 'required|string|max:255' : 'nullable|string|max:255',
            'customCompanyAddress' => 'nullable|string|max:500',
            'customCompanyCity' => 'nullable|string|max:100',
            'customCompanyZip' => 'nullable|string|max:20',
            'customCompanyCountry' => 'nullable|string|max:100',
            'clientIco' => ! $useCustomCompany ? 'required|string|max:20|regex:/^\d+$/' : 'nullable|string|max:20|regex:/^\d+$/',
            'clientName' => ! $useCustomCompany ? 'required|string|max:100' : 'nullable|string|max:100',
            'clientDic' => 'nullable|string|max:20|regex:/^\d*$/',
            'clientIcDph' => 'nullable|string|max:20',
            'clientStreet' => ! $useCustomCompany ? 'required|string|max:255' : 'nullable|string|max:255',
            'clientCity' => ! $useCustomCompany ? 'required|string|max:100' : 'nullable|string|max:100',
            'clientPostalCode' => ! $useCustomCompany ? 'required|string|max:20' : 'nullable|string|max:20',
            'clientCountry' => 'nullable|string|max:100',
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
            'invoiceNumber' => 'číslo faktúry',
            'issue_date' => 'dátum vystavenia',
            'due_date' => 'dátum splatnosti',
            'delivery_date' => 'dátum dodania',
            'currency' => 'mena',
            'variableSymbol' => 'variabilný symbol',
            'constantSymbol' => 'konštantný symbol',
            'specificSymbol' => 'špecifický symbol',
            'notes' => 'poznámka',
            'status' => 'stav faktúry',
            'items' => 'položky faktúry',
            'items.*.description' => 'popis položky',
            'items.*.quantity' => 'množstvo',
            'items.*.price' => 'jednotková cena',
            'useCustomCompany' => 'vlastné údaje spoločnosti',
            'customCompanyIco' => 'IČO spoločnosti',
            'customCompanyDic' => 'DIČ spoločnosti',
            'customCompanyIcDph' => 'IČ DPH spoločnosti',
            'customCompanyName' => 'názov spoločnosti',
            'customCompanyAddress' => 'adresa spoločnosti',
            'customCompanyCity' => 'mesto spoločnosti',
            'customCompanyZip' => 'PSČ spoločnosti',
            'customCompanyCountry' => 'krajina spoločnosti',
            'clientIco' => 'IČO klienta',
            'clientName' => 'názov klienta',
            'clientDic' => 'DIČ klienta',
            'clientIcDph' => 'IČ DPH klienta',
            'clientStreet' => 'ulica klienta',
            'clientCity' => 'mesto klienta',
            'clientPostalCode' => 'PSČ klienta',
            'clientCountry' => 'krajina klienta',
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

    public function getInvoiceNumber(): string
    {
        return $this->validated('invoiceNumber');
    }

    public function getIssueDate(): string
    {
        return $this->validated('issue_date');
    }

    public function getDueDate(): string
    {
        return $this->validated('due_date');
    }

    public function getDeliveryDate(): string
    {
        return $this->validated('delivery_date');
    }

    public function getCurrency(): ?string
    {
        return $this->validated('currency');
    }

    public function getVariableSymbol(): ?string
    {
        return $this->validated('variableSymbol');
    }

    public function getConstantSymbol(): ?string
    {
        return $this->validated('constantSymbol');
    }

    public function getSpecificSymbol(): ?string
    {
        return $this->validated('specificSymbol');
    }

    public function getNotes(): ?string
    {
        return $this->validated('notes');
    }

    public function getStatus(): InvoiceStatus
    {
        return InvoiceStatus::from($this->validated('status'));
    }

    public function getItems(): array
    {
        return $this->validated('items');
    }

    public function usesCustomCompany(): bool
    {
        return $this->boolean('useCustomCompany', false);
    }

    public function getCustomCompanyIco(): ?string
    {
        return $this->validated('customCompanyIco');
    }

    public function getCustomCompanyDic(): ?string
    {
        return $this->validated('customCompanyDic');
    }

    public function getCustomCompanyIcDph(): ?string
    {
        return $this->validated('customCompanyIcDph');
    }

    public function getCustomCompanyName(): ?string
    {
        return $this->validated('customCompanyName');
    }

    public function getCustomCompanyAddress(): ?string
    {
        return $this->validated('customCompanyAddress');
    }

    public function getCustomCompanyCity(): ?string
    {
        return $this->validated('customCompanyCity');
    }

    public function getCustomCompanyZip(): ?string
    {
        return $this->validated('customCompanyZip');
    }

    public function getCustomCompanyCountry(): ?string
    {
        return $this->validated('customCompanyCountry');
    }

    public function getClientIco(): ?string
    {
        return $this->validated('clientIco');
    }

    public function getClientName(): ?string
    {
        return $this->validated('clientName');
    }

    public function getClientDic(): ?string
    {
        return $this->validated('clientDic');
    }

    public function getClientIcDph(): ?string
    {
        return $this->validated('clientIcDph');
    }

    public function getClientStreet(): ?string
    {
        return $this->validated('clientStreet');
    }

    public function getClientCity(): ?string
    {
        return $this->validated('clientCity');
    }

    public function getClientPostalCode(): ?string
    {
        return $this->validated('clientPostalCode');
    }

    public function getClientCountry(): ?string
    {
        return $this->validated('clientCountry');
    }
}
