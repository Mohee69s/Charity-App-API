<?php

namespace App\Jobs;

use App\Models\RecurringDonation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Carbon\Carbon;
use App\Jobs\SendFirebaseNotification;
class RecurringDonationsPreNotifications implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $tomorrow = Carbon::tomorrow();
        $dueDonations = RecurringDonation::where('is_active', true)->whereDate('next_run', '=', $tomorrow)->with('User')->get();
        //TODO check the reminder 
        foreach ($dueDonations as $donation) {
            $user = $donation->User;
            $token = $user->fcmTokens()->latest()->first()?->token;

            SendFirebaseNotification::dispatch(
                $token,
                "Recurring Donation",
                "Your donations is placed to be made tomorrow, thank you in advance"
            );

        }

    }
}
