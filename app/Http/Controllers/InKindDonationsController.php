<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\InKindDonation;
use Illuminate\Http\Request;

class InKindDonationsController extends Controller
{
    public function index(Request $request){
        $id=auth()->user()->id;
        $don=InKindDonation::where('user_id',$id)->get();
        return response()->json([
            'inkinddonations'=>$don
        ]);
    }
    public function store(Request $request){
        $request->validate([
            'user_id'=>'required',
            'name'=>'required',
            'description'=>'required',
        ]);
        InKindDonation::create([
            'user_id'=>auth()->user()->id,
            'name'=>$request->name,
            'description'=>$request->description,
        ])->save();
        return response()->json([
            'message'=>'success',
            'name'=> $request->name,
            'description'=>$request->description
        ]);
    }
    public function storeforcamp(Request $request){
        $request->validate([
            'user_id'=>'required',
            'campaign_id'=>'required|exists:campaigns,id',
            'name'=>'required',
            'description'=>'required'
        ]);
        InKindDonation::create([
            'user_id'=>auth()->user()->id,
            'campaign_id'=>$request->campaign_id,
            'name'=>$request->name,
            'description'=>$request->description
        ])->save();
        $camp = Campaign::where('id',$request->campaign_id)->first();
        return response()->json([
            'message'=>'donation made',
            'User name' => auth()->user()->name,
            'campaign' => $camp->name,
            'donation'=>$request->name,
            'description' =>$request->description
        ]);

    }
}
