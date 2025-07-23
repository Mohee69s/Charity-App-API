<?php

namespace App\Http\Controllers;

use App\Models\wallet;
use App\Models\WalletTransaction;

class WalletTransactionsController extends Controller
{
    public function index(){
        $user_id=auth()->user()->id;
        $wallet=wallet::where('user_id',$user_id)->first();
        $transactions=WalletTransaction::where('wallet_id',$wallet->id)->get();
        return response()->json([
            'transactions'=>$transactions
        ]);
    }
}
