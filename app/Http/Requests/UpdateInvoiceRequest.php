<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Http\Requests\Concerns\HasInvoiceValidationRules;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateInvoiceRequest extends FormRequest
{
    use HasInvoiceValidationRules;

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

        return array_merge(
            $this->getClientValidationRules(required: false),
            $this->getInvoiceDetailsValidationRules(required: false, invoiceId: $invoiceId),
            $this->getInvoiceItemsValidationRules(required: false, invoiceId: $invoiceId)
        );
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

    public function getUseCustomCompany(): bool
    {
        return $this->boolean('useCustomCompany', false);
    }

    public function getClientName(): ?string
    {
        return $this->validated('clientName');
    }

    public function getClientIco(): ?string
    {
        return $this->validated('clientIco');
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

    public function getInvoiceNumber(): ?string
    {
        return $this->validated('invoiceNumber');
    }

    public function getIssueDate(): ?string
    {
        return $this->validated('issue_date');
    }

    public function getDueDate(): ?string
    {
        return $this->validated('due_date');
    }

    public function getDeliveryDate(): ?string
    {
        return $this->validated('delivery_date');
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

    public function getCurrency(): ?string
    {
        return $this->validated('currency');
    }

    public function getNotes(): ?string
    {
        return $this->validated('notes');
    }

    public function getStatus(): ?string
    {
        return $this->validated('status');
    }

    public function getItems(): ?array
    {
        return $this->validated('items');
    }
}
