<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('payments:reconcile')->everyFiveMinutes();
Schedule::command('orders:expire-unpaid')->everyTenMinutes();
Schedule::command('contracts:generate-interventions')->dailyAt('06:00');
