<?php

namespace App\Jobs;

use App\Models\RecurringDonation;
use App\Models\User;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class RecurringDonationsPreNotifications implements ShouldQueue
{
    use Queueable, Dispatchable, InteractsWithQueue, SerializesModels;

    public function handle(): void
    {
        $now  = Carbon::now();
        $from = $now->copy()->addDay()->subMinutes(20);
        $to   = $now->copy()->addDay()->addMinutes(20);

        /** @var NotificationService $notifier */
        $notifier = app(NotificationService::class);

        $rows = RecurringDonation::query()
            ->where('is_active', true)
            ->where('reminder_notification', 2) // one-day-before
            ->whereBetween('next_run', [$from, $to])
            ->get();

        foreach ($rows as $r) {
            $user = User::find($r->user_id);
            if (!$user) continue;

            // Deterministic de-dupe key per (recurring_id, next_run)
            $key = "recurring:pre_reminder:{$r->id}:".Carbon::parse($r->next_run)->timestamp;

            // Cache::add returns false if key already exists -> prevents dups.
            if (!Cache::add($key, 1, now()->addHours(48))) {
                continue;
            }

            $notifier->sendToUser(
                $user->id,
                'Recurring Donation Reminder',
                "Reminder: your recurring donation of ({$r->amount}) is scheduled for ".Carbon::parse($r->next_run)->toDayDateTimeString().".",
                'Recurring Donation'
            );
        }
    }
}
