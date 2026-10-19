<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

Schedule::command('activitylog:clean')->daily();
Schedule::command('app:send-reminders')->dailyAt('07:00');
Schedule::command('app:send-apar-expiry-notification')->dailyAt('08:00');
