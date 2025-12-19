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

    /*
    |--------------------------------------------------------------------------
    | HTTP Timeouts
    |--------------------------------------------------------------------------
    |
    | Timeout values for different HTTP operations (in seconds).
    |
    */
    'timeouts' => [
        'short' => 30,   // For quick operations like listing files
        'default' => 60, // For standard operations
        'long' => 600,   // For large file downloads (10 minutes)
    ],

    /*
    |--------------------------------------------------------------------------
    | Processing Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration values for file processing operations.
    |
    */
    'processing' => [
        'gzip_chunk_size' => 8192,           // 8KB chunks for gzip decompression
        'json_stream_chunk_size' => 4194304, // 4MB chunks for JSON streaming
        'gc_interval' => 10000,              // Run garbage collection every N records
        'json_decode_depth' => 512,          // Maximum JSON nesting depth
        'buffer_keep_size' => 20,            // Bytes to keep in buffer when searching
    ],

    /*
    |--------------------------------------------------------------------------
    | Batch File Prefixes
    |--------------------------------------------------------------------------
    |
    | Prefixes for different types of batch files in the bucket.
    |
    */
    'prefixes' => [
        'daily' => 'batch-daily/',
        'init' => 'batch-init/',
    ],

    /*
    |--------------------------------------------------------------------------
    | Init File Patterns
    |--------------------------------------------------------------------------
    |
    | Patterns for init list files.
    |
    */
    'init_list_pattern' => 'batch-init/init_%s_list.txt',
    'init_date_regex' => '/init_(\d{4}-\d{2}-\d{2})_list\.txt$/',
];
