<?php

namespace App\Facades;

use App\Services\JwtToken;
use Illuminate\Support\Facades\Facade;

/**
 * @method static string generateToken()
 * @method static bool validateToken(string $token)
 *
 * @see JwtToken
 *
 * @mixin  JwtToken
 */
class JwtFacade extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return JwtToken::class;
    }
}
