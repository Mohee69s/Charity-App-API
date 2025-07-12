<?php

namespace App\Jobs;

use App\Models\Donation;
use App\Models\RecurringDonation;
use App\Models\wallet;
use App\Models\WalletTransaction;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessRecurringDonations implements ShouldQueue
{
    use Queueable, Dispatchable,InteractsWithQueue,SerializesModels;

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
        $today  = Carbon::today();
        $dueDonations=RecurringDonation::where('is_active',true)
        ->whereDate('next_run','<=',$today)
        ->get();

        foreach($dueDonations as $recurring){
            if($recurring->max_runs !== null && $recurring ->run_count >= $recurring->max_runs){
                $recurring->update(['is_active'=>false]);
                continue;
            }
            $wallet=wallet::where('user_id',$recurring->user_id);
            if ($wallet->balance >= $recurring->amount){
                Donation::create([
                    'user_id'=>$recurring->user_id,
                    'amount'=>$recurring->amount,
                    'recurring'=>true,
                    'approved' => 'approved',
                ])->save();
                $wallet->balance-=$recurring->amount;
                $wallet->save();
                WalletTransaction::create([
                    'wallet_id'=>$wallet->id,
                    'type'=>'donation',
                    'amount'=>$recurring->amount,

                ])->save();
                $recurring->runcount+=1;
                $recurring->save();
            }
            else{
                //TODO add a condition for no enough balance
                $recurring->update(['is_active'=>false]);

            }

        }
    }
}
