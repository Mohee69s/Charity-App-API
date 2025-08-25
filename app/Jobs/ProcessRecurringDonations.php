<?php

namespace App\Jobs;

use App\Models\{RecurringDonation, Wallet, Donation, WalletTransaction, User};
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Throwable;

class ProcessRecurringDonations implements ShouldQueue
{
    use Queueable, Dispatchable, InteractsWithQueue, SerializesModels;

    public function handle(): void
    {
        $now = Carbon::now();

        // Pull due rows (include overdue)
        $due = RecurringDonation::query()
            ->where('is_active', true)
            ->where('next_run', '<=', $now)
            ->get();

        /** @var NotificationService $notifier */
        $notifier = app(NotificationService::class);

        foreach ($due as $row) {
            try {
                DB::transaction(function () use ($row, $notifier) {
                    // Re-load and lock the exact row to avoid races
                    /** @var RecurringDonation $r */
                    $r = RecurringDonation::whereKey($row->id)
                        ->lockForUpdate()
                        ->first();

                    if (!$r || !$r->is_active)
                        return;
                    if (Carbon::parse($r->next_run)->gt(now()))
                        return; // another worker advanced it

                    $user = User::find($r->user_id);
                    $wallet = Wallet::where('user_id', $r->user_id)->lockForUpdate()->first();

                    if (!$wallet || $wallet->balance < $r->amount) {
                        $r->update(['is_active' => false]);
                        if ($user) {
                            $notifier->sendToUser(
                                $user->id,
                                'Recurring Donation',
                                "Your recurring donation (started {$r->start_date}) was stopped due to insufficient balance or missing wallet.",
                                'admin_notify'
                            );
                        }
                        return;
                    }

                    // Charge
                    $wallet->balance -= $r->amount;
                    $wallet->save();

                    $donation = Donation::create([
                        'user_id' => $r->user_id,
                        'amount' => $r->amount,
                        'recurring' => true,
                        'approved' => 'approved',
                        'donation_date' => now(),
                        // 'type' => $r->type, // uncomment if you keep type on donations
                    ]);

                    WalletTransaction::create([
                        'wallet_id' => $wallet->id,
                        'type' => 'donation',
                        'amount' => $r->amount,
                        'reference_id' => $donation->id,
                    ]);

                    // Advance schedule
                    $r->run_count += 1;
                    $r->next_run = $this->advanceByPeriod($r->next_run, $r->period);
                    // If the worker lagged, roll forward until future
                    while (Carbon::parse($r->next_run)->lte(now())) {
                        $r->next_run = $this->advanceByPeriod($r->next_run, $r->period);
                    }
                    $r->save();

                    // Notifications:
                    // Always send a receipt (recommended). If you ONLY want when reminder_notification==1, wrap with the if.

                    if ($user) {
                        $notifier->sendToUser(
                            $user->id,
                            'Recurring Donation',
                            "We received your recurring donation of ({$r->amount}). Thank you!",
                            'Recurring Donation'
                        );
                    }

                }, 3);
            } catch (Throwable $e) {
                report($e);
                $this->fail($e);
            }
        }
    }

    protected function advanceByPeriod($from, string $period): Carbon
    {
        $dt = Carbon::parse($from);

        return match ($period) {
            'daily' => $dt->copy()->addDay(),
            'weekly' => $dt->copy()->addWeek(),
            'monthly' => $dt->copy()->addMonth(),
            'yearly' => $dt->copy()->addYear(),
            default => $dt->copy()->addMonth(),
        };
    }
}
