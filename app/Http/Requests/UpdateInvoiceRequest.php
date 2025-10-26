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
}
