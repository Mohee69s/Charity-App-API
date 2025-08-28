<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\wallet; // <-- Uppercase model
use App\Models\WalletTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash; // <-- add this
use Ichtrojan\Otp\Otp;

class WalletController extends Controller
{
    public function store(Request $request)
    {
        if (auth()->user()->wallet) {
            return response()->json([
                'message' => 'you have a wallet, you can not create another one',
            ], 422);
        }

        // e.g. exactly 4 digits; adjust as you like
        $request->validate([
            'wallet_pin' => ['required', 'digits:4'],
        ]);

        $authId = auth()->id();

        // Model mutator will hash
        wallet::create([
            'user_id' => $authId,
            'wallet_pin' => $request->wallet_pin,
            'balance' => 0,
        ]);

        $user = auth()->user();
        $user->has_wallet = true;
        $user->save();

        // Assign donator role
        $roleId = Role::where('name', 'donator')->value('id');
        if ($roleId) {
            $user->roles()->syncWithoutDetaching([$roleId]);
        }

        return response()->json([
            'message' => 'Wallet has been created',
            'data' => $this->allwalletdata()->getData(true), // keep your existing helper’s payload
        ]);
    }

    public function index(Request $request)
    {
        $request->validate([
            'wallet_pin' => ['required', 'digits:4'],
        ]);

        $wallet = wallet::where('user_id', $request->user()->id)->first();
        if (!$wallet) {
            return response()->json([
                'message' => 'you haven\'t created a wallet yet',
            ], 404);
        }

        // Verify hashed pin
        if (!Hash::check($request->wallet_pin, $wallet->wallet_pin)) {
            return response()->json([
                'message' => 'Wrong pin',
            ], 422);
        }

        return response()->json([
            'balance' => $wallet->balance,
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'wallet_pin' => ['required', 'digits:4'],
            'amount' => ['required', 'numeric'],
        ]);

        $wallet = wallet::where('user_id', $request->user()->id)->first();
        if (!$wallet) {
            return response()->json(['message' => 'Wallet not found'], 404);
        }

        // Verify hashed pin
        if (!Hash::check($request->wallet_pin, $wallet->wallet_pin)) {
            return response()->json([
                'message' => 'invalid pin',
            ], 422);
        }

        $wallet->balance += $request->amount;
        $wallet->save();

        WalletTransaction::create([
            'wallet_id' => $wallet->id,
            'type' => 'topup',
            'amount' => $request->amount,
        ]);

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

        if (!$wallet) {
            return response()->json([
                'Wallet' => null,
                'total_payed' => 0,
                'total_donated' => 0,
                'Transactions' => [],
            ]);
        }

        $trans = WalletTransaction::where('wallet_id', $wallet->id)
            ->with('campaign')
            ->with('campaign.CampaignMedia')
            ->get();

        $totaltopped = $trans->where('type', 'topup')->sum('amount');
        $totaldonated = $trans->where('type', 'donation')->sum('amount');

        return response()->json([
            'Wallet' => $wallet,
            'total_payed' => $totaltopped,
            'total_donated' => $totaldonated,
            'Transactions' => $trans,
        ]);
    }

    public function forgetPin(Request $request)
    {
        $request->validate([
            'otp' => ['required', 'numeric'],
            'new_pin' => ['required', 'digits:4'],
        ]);

        $user = auth()->user();
        $result = (new Otp())->validate($user->email, $request->otp);

        if (!$result->status) {
            return response()->json(['message' => 'Invalid or expired OTP.'], 422);
        }

        $wallet = wallet::where('user_id', $user->id)->first();
        if (!$wallet) {
            return response()->json(['message' => 'Wallet not found'], 404);
        }

        // Mutator will hash
        $wallet->wallet_pin = $request->new_pin;
        $wallet->save();

        return response()->json([
            'message' => 'PIN updated successfully'
        ], 200);
    }
}























// namespace App\Http\Controllers;

// use App\Models\Role;
// use App\Models\wallet;
// use App\Models\WalletTransaction;
// use Carbon\Carbon;
// use Illuminate\Http\Request;
// use Ichtrojan\Otp\Otp;


// class WalletController extends Controller
// {
//     public function store(Request $request)
//     {
//         if (auth()->user()->wallet) {
//             return response()->json([
//                 'message' => 'you have a wallet, you can not create another one',
//             ]);
//         }
//         $request->validate([
//             'wallet_pin' => 'required|min:4|max:5',
//         ]);

//         $auth = auth()->id();

//         wallet::create([
//             'user_id' => $auth,
//             'wallet_pin' => $request->wallet_pin,
//             'balance' => 0,
//         ])->save();

//         $user = auth()->user();
//         $user->has_wallet = true;
//         $user->save();

//         // Assign donator role
//         $roleId = Role::where('name', 'donator')->value('id');
//         if ($roleId) {
//             $user->roles()->syncWithoutDetaching([$roleId]);
//         }

//         $all = $this->allwalletdata();

//         return response()->json([
//             'message' => 'Wallet has been created',
//             $all,
//         ]);
//     }

//     public function index(Request $request)
//     {
//         $request->validate([
//             'wallet_pin' => 'required',
//         ]);
//         $wallet = wallet::where('user_id', $request->user()->id)->first();
//         if (!$wallet) {
//             return response()->json([
//                 'message' => 'you haven\'t created a wallet yet',
//             ]);
//         }
//         if ($request->wallet_pin != $wallet->wallet_pin) {
//             return response()->json([
//                 'message' => 'Wrong pin',
//             ]);
//         }

//         return response()->json([
//             'balance' => $wallet->balance,
//         ]);
//     }

//     public function update(Request $request)
//     {
//         $request->validate([
//             'wallet_pin' => 'required',
//             'amount' => 'required',
//         ]);

//         $wallet = wallet::where('user_id', $request->user()->id)->first();
//         if ($request->wallet_pin != $wallet->wallet_pin) {
//             return response()->json([
//                 'message' => 'invalid pin',
//             ]);
//         }
//         $wallet->balance += $request->amount;

//         $wallet->save();
//         WalletTransaction::create([
//             'wallet_id' => $wallet->id,
//             'type' => 'topup',
//             'amount' => $request->amount,
//         ])->save();

//         return response()->json([
//             'from' => auth()->user()->full_name,
//             'to' => 'My charity wallet',
//             'amount' => $request->amount,
//             'method' => 'My wallet',
//             'time' => Carbon::now(),
//         ]);
//     }

//     public function allwalletdata()
//     {
//         $user = auth()->user();
//         $wallet = wallet::where('user_id', $user->id)->first();
//         $trans = WalletTransaction::where('wallet_id', $wallet->id)->with('campaign')->with('campaign.CampaignMedia')->get();
//         $totaltopped = $trans->where('type', 'topup')->sum('amount');
//         $totaldonated = $trans->where('type', 'donation')->sum('amount');

//         return response()->json([
//             'Wallet' => $wallet,
//             'total_payed' => $totaltopped,
//             'total_donated' => $totaldonated,
//             'Transactions' => $trans,
//         ]);
//     }

//     public function forgetPin(Request $request)
//     {
//         $request->validate([
//             'otp' => 'required|numeric',
//             'new_pin' => 'required|numeric',
//         ]);
//         $user = auth()->user();
//         $result = (new Otp())->validate($user->email, $request->otp);
//         if (!$result->status)
//             return response()->json(['message' => 'Invalid or expired OTP.'], 422);
//         $wallet = wallet::where('user_id', $user->id)->first();
//         $wallet->wallet_pin = $request->new_pin;
//         $wallet->save();
//         return response()->json([
//             'message'=>'PIN updated successfully'
//         ],200);


//     }
// }
