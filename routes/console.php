<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Console\Commands\Tasks\UpdateDangerRatings;
use App\Console\Commands\Tasks\DeleteOldDangerRates;


Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::call(new UpdateDangerRatings)->hourlyAt(25);

Schedule::call(new DeleteOldDangerRates)->daily();

Schedule::command('model:prune')->daily();