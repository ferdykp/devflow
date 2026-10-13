<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

Schedule::command('devflow:github-sync --stale=60')->hourly()->withoutOverlapping();

Schedule::command('devflow:github-reconcile --stale=30')->everyTenMinutes()->withoutOverlapping();
