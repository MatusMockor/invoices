<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('app:sync-companies')
    ->dailyAt('01:00')
    ->timezone('Europe/Bratislava')
    ->withoutOverlapping()
    ->onOneServer();
