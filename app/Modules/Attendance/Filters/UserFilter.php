<?php

declare(strict_types=1);

namespace App\Modules\Attendance\Filters;

use Closure;
use Illuminate\Database\Eloquent\Builder;

class UserFilter
{
    public function handle(Builder $query, Closure $next)
    {
        if (request()->has('user_id')) {
            $query->where('user_id', request('user_id'));
        }

        return $next($query);
    }
}
