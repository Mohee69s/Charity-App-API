<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use Illuminate\Http\Request;
use app\Models\WalletTransaction;
use app\Models\wallet;
use app\Models\Campaign;
use Carbon\Carbon;
class DonationController extends Controller
{
    public function index(){
        $user_id=auth()->user()->id;
        $don=Donation::where('user_id',$user_id)->get();
        return response()->json([
            'donations'=>$don
        ]);
    }
    public function store(Request $request){
        $request->validate([
            'amount'=>'required|numeric',
            'wallet_pin'=>'required',
            'campaign_id'=>'required|exists:campaigns,id'
        ]);

        $user = auth();
        $wallet = wallet::where('user_id',$user->id());
        $camp=Campaign::where('campaign_id',$request->campaign_id);
        if($camp->status != 'active'){
            return response()->json([
                'message'=>"the requested camp is in phase {$camp->status}, you can\'t make donations"
            ]);
        }
        if($camp->achieved >= $camp->goal){
            return response()->json([
                'message'=>'the goal has been achieved'
            ]);
        }
        if(!$camp->needs_donations){
            return response()->json([
                'message' => 'the campaign doesn\'t need donations'
            ]);
        }
        if ($wallet->wallet_pin === $request->wallet_pin){
            if($wallet->balance >= $request->amount){
                $wallet->balance -=$request->amount;
                $wallet->save();
                $camp->achieved+=$request->amount;
                $camp->save();
                Donation::create([
                    'user_id'=>auth()->user()->id,
                    'campaign_id'=>$camp->id,
                    'amount'=>$request->amount
                ])->save();
                WalletTransaction::create([
                    'wallet_id'=>$wallet->id,
                    'type'=>'donation',
                    'amount'=>$request->amount,
                    'campaign_id'=>$camp->id,
                ])->save();
                return response()->json([
                    'message'=>'Donation Completed',
                    'from'=>$user->user()->name,
                    'to'=>$camp->name,
                    'amount'=>$request->amount,
                    'payment method'=>'Donation wallet',
                    'time'=>Carbon::now()

                ]);
            }else{
                return response()->json([
                    'message' => 'no enough balance'
                ]);
            }
        }
        return response()->json([
            'message' => 'wrong pin'
        ]);

    }

}
