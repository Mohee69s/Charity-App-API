<?php

namespace App\Jobs;

use App\Models\{RecurringDonation, Donation, WalletTransaction, User};
// If your model class is literally `wallet` (lowercase), keep this alias.
// If it's `Wallet` (capital W), change this line to: use App\Models\Wallet;
use App\Models\wallet as Wallet;

use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ProcessRecurringDonations implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** optional tuning */
    public int $tries = 3;
    public int $backoff = 5; // seconds

    public function handle(): void
    {
        $processed = 0;

        $batchSize = 50;

        while (true) {
            // 1) Find a small batch of due IDs using DB time and Postgres boolean
            $ids = RecurringDonation::query()
                ->whereRaw('is_active IS TRUE')
                ->whereRaw('next_run <= now()')
                ->orderBy('next_run')   // oldest first
                ->orderBy('id')
                ->limit($batchSize)
                ->pluck('id');

            if ($ids->isEmpty()) {
                break; // nothing due
            }

            // 2) Process each ID atomically; re-check under row locks
            foreach ($ids as $id) {
                try {
                    DB::transaction(function () use ($id) {
                        /** @var RecurringDonation|null $r */
                        $r = RecurringDonation::whereKey($id)->lockForUpdate()->first();
                        if (!$r)
                            return;

                        // Still due & active?
                        if (!$r->is_active || Carbon::parse($r->next_run)->gt(now())) {
                            return;
                        }

                        // Lock wallet to avoid concurrent charges
                        $wallet = Wallet::where('user_id', $r->user_id)->lockForUpdate()->first();
                        if (!$wallet || $wallet->balance < $r->amount) {
                            // Deactivate if we cannot charge
                            $r->is_active = false;
                            $r->save();
                            if ($user = User::find($r->user_id)) {
                            app(NotificationService::class)->sendToUser(
                                $user->id,
                                'Recurring Donation',
                                "Your wallet does not have enough balance for the recurring donation, it will be stopped",
                                'Recurring Donation'
                            );
                            return;
                        }
                        }

                        // Charge
                        $wallet->balance -= $r->amount;
                        $wallet->save();

                        $donation = Donation::create([
                            'user_id' => $r->user_id,
                            'amount' => $r->amount,
                            'recurring' => true,
                            'donation_date' => now(),
                            'campaign_id' => null,
                            // 'type'       => $r->type, // uncomment if your donations table has this
                            // 'recurring_donation_id' => $r->id, // if you add the FK later
                        ]);

                        WalletTransaction::create([
                            'wallet_id' => $wallet->id,
                            'type' => 'donation',
                            'amount' => $r->amount,
                            'reference_id' => null,
                            'created_at' => null
                        ]);

                        // Advance next_run once, then roll forward until it's in the future
                        $r->run_count = ($r->run_count ?? 0) + 1;
                        $r->next_run = self::advanceOnce($r->next_run, $r->period);
                        while (Carbon::parse($r->next_run)->lte(now())) {
                            $r->next_run = self::advanceOnce($r->next_run, $r->period);
                        }
                        $r->save();

                        // Optional: receipt/notification
                        if ($user = User::find($r->user_id)) {
                            app(NotificationService::class)->sendToUser(
                                $user->id,
                                'Recurring Donation',
                                "We received your recurring donation of ({$r->amount}). Thank you!",
                                'Recurring Donation'
                            );
                        }

                    }, 3);
                } catch (\Throwable $e) {   // note the leading backslash
                    report($e);
                    $this->release($this->backoff);
                }
            }
        }
        logger()->info('ProcessRecurringDonations summary', [
            'processed' =>  'eww', // increment this in your loop
        ]);


    }

    private static function advanceOnce($from, string $period): Carbon
    {
        $dt = Carbon::parse($from);

        return match ($period) {
            'daily' => $dt->addDay(),
            'weekly' => $dt->addWeek(),
            'monthly' => $dt->addMonth(),
            'yearly' => $dt->addYear(),
            default => $dt->addMonth(),
        };
    }
}
