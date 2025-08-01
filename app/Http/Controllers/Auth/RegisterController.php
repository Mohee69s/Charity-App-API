<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Hash;

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
            'name'=>['required','string'],
            'email' => ['required', 'string', 'max:255','email','unique:users,email'],
            'password' => ['required', 'min:8'],
            'phone'=>'required',
            'birth_date' => 'required'
        ]);

        $user = User::create([
            'full_name'=>$request->name,
            'email' => $request->email,
            'phone_number' => $request-> phone,
            'password_hash' => Hash::make($request->password),
            'birth_date'=>$request->birth_date,
            'is_volunteer' => false
        ]);

        event(new Registered($user));


        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
        ]);
    }
}
