<?php

use App\Jobs\ProcessRecurringDonations;
use App\Jobs\RecurringDonationsPreNotifications;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Schedule::job(new RecurringDonationsPreNotifications)
    ->hourly()
    ->withoutOverlapping()
    ->onOneServer();

Schedule::job(new ProcessRecurringDonations)
    ->hourly()
    ->withoutOverlapping()
    ->onOneServer();