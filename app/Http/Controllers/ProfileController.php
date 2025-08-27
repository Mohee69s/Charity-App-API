<?php

namespace App\Http\Controllers;

use App\Mail\SendEmail;
use Ichtrojan\Otp\Otp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\Mail;

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
            'full_name'    => 'string|max:255',
            'email'        => 'email|max:255|unique:users,email,' . $user->id,
            'phone_number' => 'string|max:20',
            'address'      => 'string|max:255',
            'birth_date'   => 'date',
            'profile_url'  => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data = $request->only([
            'full_name',
            'email',
            'phone_number',
            'address',
            'birth_date',
        ]);

        if ($request->hasFile('profile_url')) {
            $path = $request->file('profile_url')->store('profiles', 'public');
            $data['profile_url'] = $path;
        }

        $user->update($data);

        return response()->json([
            'message' => 'Data updated successfully',
            'user'    => $user,
        ]);
    }

    public function requestOTP(Request $request)
    {
       $request->validate([
        'email'=>'required|email'
        ]);
        $otp = (new Otp)->generate($request->email, 'numeric', 6, 15);
        $message = 'here is your one time password to reset your password/pin '. $otp->token .' this will be valide for 15 minutes';
        Mail::to($request->email)->send(new SendEmail($message));
        return response()->json([
            'message'=> 'OTP has been sent, check you email address',
        ]);
    }
}
