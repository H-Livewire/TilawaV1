<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Signed-in users' daily-ayah push notifications (needs the Laravel scheduler running).
Schedule::command('ayah:send-daily')->everyMinute()->withoutOverlapping();
