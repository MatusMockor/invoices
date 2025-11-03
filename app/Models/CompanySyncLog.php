<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanySyncLog extends Model
{
    protected $fillable = [
        'sync_date',
        'sync_type',
        'files_processed',
        'companies_created',
        'errors',
        'status',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'sync_date' => 'date',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'files_processed' => 'integer',
        'companies_created' => 'integer',
        'errors' => 'integer',
    ];
}
