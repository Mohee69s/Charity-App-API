<?php

namespace App\Http\Controllers;

use App\Models\wallet;
use App\Models\WalletTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function store(Request $request){
        $request->validate([
            "wallet_pin" => 'required|min:4|max:5' 
        ]); 
        $auth = auth()->id();

        wallet::create([
            'user_id' => $auth,
            'wallet_pin' => $request->wallet_pin,
            'balance'=>0
        ])->save();

        return response()->json([
            'message'=>'Wallet has been created'
        ]);
    }


    public function index(Request $request){
        $request->validate([
            'wallet_pin' => 'required'
        ]);
        $wallet = wallet::where('user_id',$request->user()->id)->first();
        if ($request -> wallet_pin != $wallet -> wallet_pin){
            return response()->json([
                'message' => 'Wrong pin'
            ]);
        }
        return response()->json([
            'balance'=>$wallet->balance
        ]);
    }
    public function update(Request $request){
        $request -> validate([
            'wallet_pin' => 'required',
            'amount'=> 'required'
        ]);
        $wallet = wallet::where('user_id',$request->user()->id)->first();
        if($request -> wallet_pin != $wallet->wallet_pin){
            return response()->json([
                'message' => 'invalid pin'
            ]);
        }
        $wallet->balance +=$request->amount;
        
        $wallet->save();
        WalletTransaction::create([
            'wallet_id'=>$wallet->id,
            'type'=>'topup',
            'amount'=>$request->amount,
            'campaign_id'=>null,
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now()
        ])->save();

        
        return response()->json([
            'message' => 'balance updated successfully',
            'balance' => $wallet->balance
        ]) ;
    }  
    
}
