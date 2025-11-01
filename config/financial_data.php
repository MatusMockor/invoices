<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Financial Data Source URL
    |--------------------------------------------------------------------------
    |
    | The URL to download the ZIP file containing Slovak company data from
    | the Financial Administration (Financna sprava).
    |
    */
    'source_url' => env('FINANCIAL_DATA_URL', 'https://report.financnasprava.sk/ds_dsrdp.zip'),

    /*
    |--------------------------------------------------------------------------
    | VAT Data Source URL
    |--------------------------------------------------------------------------
    |
    | The URL to download the ZIP file containing Slovak VAT registration data
    | from the Financial Administration (Financna sprava).
    |
    */
    'vat_source_url' => env('FINANCIAL_DATA_VAT_URL', 'https://report.financnasprava.sk/ds_dphs.zip'),

    /*
    |--------------------------------------------------------------------------
    | Batch Size
    |--------------------------------------------------------------------------
    |
    | The number of records to process in each database transaction batch.
    | Adjust this value based on available memory and performance requirements.
    | Increased from 1000 to 5000 for better performance (fewer DB round trips).
    |
    */
    'batch_size' => env('FINANCIAL_DATA_BATCH_SIZE', 5000),

    /*
    |--------------------------------------------------------------------------
    | Temporary Storage Path
    |--------------------------------------------------------------------------
    |
    | The temporary directory path where downloaded ZIP files will be stored
    | and extracted. This path is relative to the storage directory.
    |
    */
    'temp_path' => 'temp/financial-data',
];
