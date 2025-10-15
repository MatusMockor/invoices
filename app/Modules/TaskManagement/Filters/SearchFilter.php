<?php

declare(strict_types=1);

namespace App\Modules\TaskManagement\Filters;

use Closure;
use Illuminate\Database\Eloquent\Builder;

class SearchFilter
{
    public function handle(Builder $query, Closure $next): Builder
    {
        if (! request()->has('search') || empty(request('search'))) {
            return $next($query);
        }

        $query->search(request('search'));

        return $next($query);
    }
}
