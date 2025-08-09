<?php

namespace App\Http\Controllers;

use Ichtrojan\Otp\Otp;
use Illuminate\Http\Request;

class OTPController extends Controller
{
    public function request()
    {
        $email = auth()->user()->email;
        $otp = (new Otp)->generate($email, 'numeric', 6, 15);

        return response()->json([
            'otp' => $otp,
        ]);
    }
    public function send(Request $request){
        $request->validate([
            'otp'=>'required|numeric'
        ]);
        $otp = $request->otp;
        $user = auth()->user();
        if ((new Otp)->validate($user->email, $request->otp)->status){
            return response()->json([
                'status'=>true,
            ]);
        }
        else return response()->json(['status'=>false]);
    }
}
