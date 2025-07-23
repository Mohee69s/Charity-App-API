<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function index(){
        $user = auth()->user();
        return response()->json([
            'User'=>$user
        ]);
    }

    public function store(Request $request){
        $user = auth()->user();
        $request->validate([
            'full_name'=>$request->full_name,
            'email'=>$request->email,
            'password_hash'=>$request->password,
            'phone_number'=> $request->phone_number,
            'address'=>$request->address,
            'profile_url'=> $request->profile_url,
        ])
        
    }
}
