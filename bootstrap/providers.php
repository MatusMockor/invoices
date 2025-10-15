<?php

declare(strict_types=1);

return [
    App\Providers\AppServiceProvider::class,
    App\Modules\VehicleLogbook\Providers\VehicleLogbookServiceProvider::class,
    App\Modules\CRM\Providers\CrmServiceProvider::class,
    App\Modules\TaskManagement\Providers\TaskManagementServiceProvider::class,
    App\Modules\Attendance\Providers\AttendanceServiceProvider::class,
];
