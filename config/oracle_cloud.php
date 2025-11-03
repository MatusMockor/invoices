<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Oracle Cloud Storage Bucket URL
    |--------------------------------------------------------------------------
    |
    | The base URL for the Oracle Cloud Storage bucket containing company data.
    |
    */
    'bucket_url' => env('ORACLE_CLOUD_BUCKET_URL'),

    /*
    |--------------------------------------------------------------------------
    | Batch Size
    |--------------------------------------------------------------------------
    |
    | The number of records to process in each database transaction batch.
    |
    */
    'batch_size' => env('ORACLE_CLOUD_BATCH_SIZE', 5000),

    /*
    |--------------------------------------------------------------------------
    | Temporary Storage Path
    |--------------------------------------------------------------------------
    |
    | The temporary directory path where downloaded files will be stored.
    | This path is relative to the storage directory.
    |
    */
    'temp_path' => 'temp/company-sync',
];
