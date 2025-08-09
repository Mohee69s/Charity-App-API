<?php

namespace App\Http\Controllers;

use Ichtrojan\Otp\Otp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        return response()->json([
            'User' => $user,
        ]);
    }

    public function updatePassword(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'otp' => 'required|numeric',
            'password' => 'required',
        ]);


        if ((new Otp)->validate($user->email, $request->otp)->status) {
            $user->password_hash = Hash::make($request->password);
            $user->save();

            return response()->json([
                'message' => 'Password updated successfully.',
            ]);
        }
        return response()->json([
            'message' => 'wrong otp'
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'full_name' => 'string|max:255',
            'email' => 'email|max:255|unique:users,email',
            'phone_number' => 'string|max:20',
            'address' => 'string|max:255',
            'birth_date' => 'date',
            'profile_url' => 'url|max:255',
        ]);

        $data = collect($request->only([
            'full_name',
            'email',
            'phone_number',
            'address',
            'birth_date',
            'profile_url',
        ]))->filter()->all(); // remove null, empty, etc.

        $user->update($data);
        return response()->json([
            'message' => 'data updated successfully',
            'user' => $user,
        ]);
    }
    public function requestOTP()
    {
        $email = auth()->user()->email;
        $otp = (new Otp)->generate($email, 'numeric', 6, 15);

        //TODO notify with otp
        return response()->json([
            'otp' => $otp,
        ]);
    }
}
