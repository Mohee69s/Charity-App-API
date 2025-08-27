<?php

namespace App\Jobs;

use App\Models\RecurringDonation;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class RecurringDonationsPreNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // optional tuning
    public int $tries = 3;
    public int $backoff = 5; // seconds

    public function handle(): void
    {
        /** @var NotificationService $notifier */
        $notifier = app(NotificationService::class);

        // 24h ± 20min window around the due time, using the DB clock
        // NOTE: if your column is `reminder_notification` instead of `reminder`,
        // change the `where('reminder', 2)` line accordingly.
        $rows = RecurringDonation::query()
            ->whereRaw('is_active IS TRUE')
            ->where('reminder', 2)  // <-- use 'reminder_notification' if that's your column
            ->whereRaw("next_run BETWEEN now() + interval '24 hours' - interval '20 minutes'
                                   AND     now() + interval '24 hours' + interval '20 minutes'")
            ->orderBy('next_run')
            ->limit(500)
            ->get();

        foreach ($rows as $r) {
            // Ensure Carbon cast so we can format nicely
            $next = $r->next_run instanceof Carbon ? $r->next_run : Carbon::parse($r->next_run);

            // De-dupe key per (recurring id, specific scheduled run)
            $key = "recurring:pre_reminder:{$r->id}:".$next->timestamp;

            // Cache::add returns false if key already exists (prevents duplicates across scheduler runs)
            if (!Cache::add($key, 1, now()->addHours(48))) {
                continue;
            }

            if ($user = User::find($r->user_id)) {
                $notifier->sendToUser(
                    $user->id,
                    'Recurring Donation Reminder',
                    "Reminder: your recurring donation of ({$r->amount}) is scheduled for ".$next->toDayDateTimeString().".",
                    'Recurring Donation'
                );
            }
        }
    }
}
