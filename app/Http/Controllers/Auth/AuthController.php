<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use App\Models\VolunteerApplications;

class AuthController extends Controller
{
    public function login(Request $request)
    {

        $credentials = $request->only('email', 'password');
        JWTAuth::factory()->setTTL(60 * 24 * 30);
        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }
        $user = auth()->user()->makeHidden(['password_hash', 'is_volunteer']);
        $sub = VolunteerApplications::where('user_id', auth()->user()->id)->first();
        $result = $sub->status ?? 'have_not_applied';
        $user['volunteer'] = $result;
        return response()->json([
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60 * 24 * 30,
        ]);
    }
    public function destroy(Request $request)
    {
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
