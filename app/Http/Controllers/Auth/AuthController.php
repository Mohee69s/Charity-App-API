<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    public function login(Request $request){

        $credentials = $request -> only('email', 'password');
        JWTAuth::factory()->setTTL(60*24*30);
        if (!$token = JWTAuth::attempt($credentials) ){
            return response()->json(['error'=> 'Invalid credentials'],401);            
        }
        return response()->json([
            auth()->user(),
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60*24*30,
        ]);
    }
    public function destroy(Request $request){
        JWTAuth::parseToken()->invalidate(true);
        return response()->json([
            'message' => 'Logged out'
        ]);
    }
    public function me()
    {
        return response()->json(auth()->user());
    }
}
