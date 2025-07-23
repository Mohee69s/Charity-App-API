<?php

namespace App\Http\Controllers;

use App\Models\wallet;
use App\Models\Role;
use App\Models\WalletTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function store(Request $request)
    {
        if (auth()->user()->wallet) {
            return response()->json([
                'message' => 'you have a wallet, you can not create another one'
            ]);
        }
        $request->validate([
            "wallet_pin" => 'required|min:4|max:5'
        ]);

        $auth = auth()->id();

        wallet::create([
            'user_id' => $auth,
            'wallet_pin' => $request->wallet_pin,
            'balance' => 0
        ])->save();

        $user = auth()->user();
        $user->has_wallet = true;
        $user->save();

        // Assign donator role
        $roleId = Role::where('name', 'donator')->value('id');
        if ($roleId) {
            $user->roles()->syncWithoutDetaching([$roleId]);
        }

        $all = $this->allwalletdata();
        return response()->json([
            'message' => 'Wallet has been created',
            $all
        ]);
    }


    public function index(Request $request)
    {
        $request->validate([
            'wallet_pin' => 'required'
        ]);
        $wallet = wallet::where('user_id', $request->user()->id)->first();
        if (!$wallet) {
            return response()->json([
                'message' => 'you haven\'t created a wallet yet'
            ]);
        }
        if ($request->wallet_pin != $wallet->wallet_pin) {
            return response()->json([
                'message' => 'Wrong pin'
            ]);
        }
        return response()->json([
            'balance' => $wallet->balance
        ]);
    }
    public function update(Request $request)
    {
        $request->validate([
            'wallet_pin' => 'required',
            'amount' => 'required',
            // 'method' =>' required'
        ]);
        $method = $request->method;
        $wallet = wallet::where('user_id', $request->user()->id)->first();
        if ($request->wallet_pin != $wallet->wallet_pin) {
            return response()->json([
                'message' => 'invalid pin'
            ]);
        }
        $wallet->balance += $request->amount;

        $wallet->save();
        WalletTransaction::create([
            'wallet_id' => $wallet->id,
            'type' => 'topup',
            'amount' => $request->amount,
            // 'created_at'=>Carbon::now(),
        ])->save();


        return response()->json([
            'from' => auth()->user()->full_name,
            'to' => 'My charity wallet',
            'amount' => $request->amount,
            'method' => 'My wallet',
            'time' => Carbon::now(),
        ]);
    }

    public function allwalletdata()
    {
        $user = auth()->user();
        $wallet = wallet::where('user_id', $user->id)->first();
        $trans = WalletTransaction::where('wallet_id', $wallet->id)->with('campaign')->with('campaign.CampaignMedia')->get();
        $totaltopped = $trans->where('type', 'topup')->sum('amount');
        $totaldonated = $trans->where('type', 'donation')->sum('amount');
        return response()->json([
            'Wallet' => $wallet,
            'total_payed' => $totaltopped,
            'total_donated' => $totaldonated,
            'Transactions' => $trans,
        ]);
    }
}
