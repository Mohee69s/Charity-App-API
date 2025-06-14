<?php

use App\Jobs\ProcessRecurringDonations;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Schedule::job(new ProcessRecurringDonations)->daily();