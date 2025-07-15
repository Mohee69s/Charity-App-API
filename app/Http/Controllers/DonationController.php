<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Models\wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
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
            'wallet_pin'=>'required|numeric',
            'amount'=>'required|numeric'
        ]);
        $wallet=wallet::where('user_id',auth()->user()->id)->first();
        if ($wallet->wallet_pin != $request->wallet_pin){
            return response()->json([
                'message'=> 'Invalid PIN'
            ]);
        }
        if ($wallet->balance < $request->amount){
            return response()->json([
                'message'=>'No enough balance'
            ]);
        }
        $wallet->balance -=$request->amount;
        $wallet->save();
        
        Donation::create([
            'amount'=>$request->amount,
            'donation_date'=>Carbon::now(),
            'recurring'=>false,
            'campaign_id'=>null,
            'user_id'=>auth()->user()->id
        ])->save();
        WalletTransaction::create([
            'type'=>'donation',
            'amount'=>$request->amount,
            'reference_id'=>null,
            'wallet_id'=>$wallet->id
        ])->save();
        return response()->json([
                    'message'=>'Donation Completed',
                    'from'=>auth()->user()->full_name,
                    'to'=>'General charity',
                    'amount'=>$request->amount,
                    'payment method'=>'Donation wallet',
                    'time'=>Carbon::now()

                ]);
    }

}
