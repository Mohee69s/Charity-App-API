<?php

use App\Jobs\ProcessRecurringDonations;
use App\Jobs\RecurringDonationsPreNotifications;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Schedule::job(new ProcessRecurringDonations)->daily();
Schedule::job(new RecurringDonationsPreNotifications)->daily();