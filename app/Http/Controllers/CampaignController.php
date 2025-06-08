<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\wallet;
use App\Models\WalletTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    public function index(Request $request){
        $camps=Campaign::with('CampaignMedia')->get();
        return response()->json([
            'campaigns' => $camps
        ]);
    }
    public function camp(Request $request){
        $request->validate([
            'campaign_id'=>'required|exists:campaigns,id'
        ]);
        $camp=Campaign::where('campaign_id',$request->campaign_id)->first();
        return response()->json([
            'campaign'=>$camp
        ]);
    }
    public function cash(Request $request){
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
        if ($wallet->wallet_pin === $request->wallet_pin){
            if($wallet->balance >= $request->amount){
                $wallet->balance -=$request->amount;
                $wallet->save();
                $camp->achieved+=$request->amount;
                $camp->save();
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
            }
        }
        return response()->json([
            'message' => 'wrong pin'
        ]);

    }

}
