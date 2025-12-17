<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * Base controller class for all application controllers.
 *
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
abstract class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
