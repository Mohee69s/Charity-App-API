<?php

namespace App\Http\Controllers;

use App\Mail\SendEmail;
use App\Models\User;
use Ichtrojan\Otp\Otp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Mail;
use Tymon\JWTAuth\Facades\JWTAuth;
class ProfileController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        return response()->json([
            'User' => $user,
        ]);
    }

    public function forgetPassword(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'digits:6'],
            'new_password' => 'required'
        ]);
        $user = User::where('email', $data['email'])->first();
        // Return generic message to avoid email enumeration
        if (!$user) {
            return response()->json(['message' => 'Invalid OTP or expired.'], 422);
        }

        $attemptKey = 'pw:otp:attempts:' . sha1(strtolower($data['email']));
        if (RateLimiter::tooManyAttempts($attemptKey, 5)) {
            $sec = RateLimiter::availableIn($attemptKey);
            return response()->json(['message' => "Too many attempts. Try again in {$sec}s."], 429);
        }

        $result = (new Otp)->validate($data['email'], $data['otp']); // returns object with ->status(bool) and ->message

        if (!$result->status) {
            RateLimiter::hit($attemptKey, 60); // penalize for 1 minute
            return response()->json(['message' => 'Invalid or expired OTP.'], 422);
        }

        RateLimiter::clear($attemptKey);
        $user->password_hash = Hash::make($data['new_password']);
        $user->save();
        JWTAuth::invalidate(JWTAuth::fromUser($user));
        return response()->json(['message' => 'Password updated successfully.']);

    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'full_name' => 'string|max:255',
            'email' => 'email|max:255|unique:users,email,' . $user->id,
            'phone_number' => 'string|max:20',
            'address' => 'string|max:255',
            'birth_date' => 'date',
            'profile_url' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
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
            'user' => $user,
        ]);
    }

    public function requestOTP(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email'
        ]);
        $email = User::where('email', $request->email)->first();
        if (!$email)
            return response()->json(['message' => 'if this email exists, the otp is sent, check your email address']);


        $key = 'pw:otp:send:' . sha1(strtolower($data['email']));

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $sec = RateLimiter::availableIn($key);
            return response()->json(['message' => "Please wait {$sec}s before requesting another code."], 429);

        }
        $otp = (new Otp)->generate($request->email, 'numeric', 6, 15);

        $message = 'here is your one time password to reset your password/pin ' . $otp->token . ' this will be valide for 15 minutes';

        Mail::to($request->email)->send(new SendEmail($message));
        RateLimiter::hit($key, 60);
        return response()->json([
            'message' => 'if this email exists, the otp is sent, check your email address',
        ]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'password' => ['required'],
            'new_password' => ['required'],
        ]);
        $user = auth()->user();
        if (!Hash::check($request->password, $user->password_hash)) {
            return response()->json([
                'message' => 'Current password is incorrect.'
            ], 422);
        }

        // 2. Update password
        $user->password_hash = Hash::make($request->new_password);
        
        $user->save();

        JWTAuth::invalidate(JWTAuth::getToken());
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'Password updated successfully.',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60 * 24 * 30,
        ]);
    }
}
