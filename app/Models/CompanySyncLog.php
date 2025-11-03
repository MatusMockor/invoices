<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CompanySyncStatus;
use App\Enums\CompanySyncType;
use Illuminate\Database\Eloquent\Model;

final class CompanySyncLog extends Model
{
    protected $fillable = [
        'sync_date',
        'sync_type',
        'files_processed',
        'companies_created',
        'companies_updated',
        'companies_not_found',
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
        'companies_updated' => 'integer',
        'companies_not_found' => 'integer',
        'errors' => 'integer',
        'status' => CompanySyncStatus::class,
        'sync_type' => CompanySyncType::class,
    ];
}
