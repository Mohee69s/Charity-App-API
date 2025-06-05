<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
            'email' => ['required', 'string', 'max:255'],
            'password' => ['required', 'min:8'],
            'name'=>'required',
            'phone'=>'required'
        ]);

        $user = User::create([
            'name' => $request -> name,
            'email' => $request->email,
            'phone' => $request-> phone,
            'password'=> $request->password,
        ]);

        event(new Registered($user));


        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
        ]);
    }
}