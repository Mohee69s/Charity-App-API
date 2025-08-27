<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\VolunteerApplications;
use Carbon\Carbon;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Hash;
use Tymon\JWTAuth\Facades\JWTAuth;


class RegisterController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'max:255','email','unique:users,email'],
            'password' => ['required', 'min:8'],
            'phone'=>'required',
        ]);

        $user = User::create([
            'full_name'=>'HasNotBeenSet',
            'email' => $request->email,
            'phone_number' => $request-> phone,
            'password_hash' => Hash::make($request->password),
            'birth_date'=>Carbon::now(),
            'has_wallet'=>false,
            'is_volunteer' => false
        ]);

        event(new Registered($user));

        $credentials = $request -> only('email', 'password');
        JWTAuth::factory()->setTTL(60*24*30);
        if (!$token = JWTAuth::attempt($credentials) ){
            return response()->json(['error'=> 'Invalid credentials'],401);
        }
        $user = auth()->user()->makeHidden(['password_hash','is_volunteer']);
        $sub = VolunteerApplications::where('user_id', auth()->user()->id)->first();
        $result = $sub->status ?? 'have_not_applied';
        $user['volunteer']=$result;
        return response()->json([
            'user'=>$user,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60*24*30,
        ]);
    }
}
