<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        return response()->json([
            'User' => $user
        ]);
    }


public function updatePassword(Request $request)
{
    $user = auth()->user();

    $request->validate([
        'password' => 'required',
    ]);

    $user->password_hash = Hash::make($request->password);
    $user->save();

    return response()->json([
        'message' => 'Password updated successfully.'
    ]);
}

public function store(Request $request){
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
    
}

}
