<?php

declare(strict_types=1);

use App\Enums\Currency;
use App\Enums\InvoiceStatus;
use App\Enums\InvoiceTemplate;

return [
    /*
    |--------------------------------------------------------------------------
    | Invoice Templates
    |--------------------------------------------------------------------------
    |
    | Available invoice template designs that users can choose from.
    | These templates control the visual appearance of generated invoices.
    |
    */
    'templates' => InvoiceTemplate::values(),

    /*
    |--------------------------------------------------------------------------
    | Default Template
    |--------------------------------------------------------------------------
    |
    | The default template used for new users or when no template is selected.
    |
    */
    'default_template' => InvoiceTemplate::CLASSIC->value,

    /*
    |--------------------------------------------------------------------------
    | Default Invoice Status
    |--------------------------------------------------------------------------
    |
    | The default status assigned to newly created invoices.
    |
    */
    'default_status' => InvoiceStatus::DRAFT->value,

    /*
    |--------------------------------------------------------------------------
    | Default Currency
    |--------------------------------------------------------------------------
    |
    | The default currency code (ISO 4217) used for invoices.
    |
    */
    'default_currency' => Currency::EUR->value,

    /*
    |--------------------------------------------------------------------------
    | Available Currencies
    |--------------------------------------------------------------------------
    |
    | List of all available currencies that can be used for invoices.
    |
    */
    'currencies' => Currency::values(),

    /*
    |--------------------------------------------------------------------------
    | Default Country
    |--------------------------------------------------------------------------
    |
    | The default country code (ISO 3166-1 alpha-2) used for companies.
    |
    */
    'default_country' => env('INVOICE_DEFAULT_COUNTRY', 'SK'),

    /*
    |--------------------------------------------------------------------------
    | Default Company Type
    |--------------------------------------------------------------------------
    |
    | The default legal form for newly registered companies.
    |
    */
    'default_company_type' => env('INVOICE_DEFAULT_COMPANY_TYPE', 's.r.o.'),
];
