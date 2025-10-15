<?php

declare(strict_types=1);

namespace App\Modules\Attendance\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Attendance\Models\Attendance;
use Illuminate\Contracts\View\View;

class AttendanceController extends Controller
{
    /**
     * Display the attendance management page.
     */
    public function index(): View
    {
        $this->authorize('viewAny', Attendance::class);

        return view('attendance::index');
    }
}
