<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Tasks
|--------------------------------------------------------------------------
|
| Daily Sales Report: Runs every evening at 6:00 PM
| Sends a summary of all products sold that day to the admin email.
|
|
*/

Schedule::command('report:daily-sales')->dailyAt('18:00');

