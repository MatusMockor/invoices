<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class CompanyCollection extends ResourceCollection
{
    public $collects = CompanyResource::class;

    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
        ];
    }
}
