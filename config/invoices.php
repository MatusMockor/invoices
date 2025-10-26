<?php

declare(strict_types=1);

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
];
