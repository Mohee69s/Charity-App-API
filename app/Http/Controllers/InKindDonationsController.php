<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\InKindDonation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class InKindDonationsController extends Controller
{
    public function index($status = 'approved'): JsonResponse
    {
        $id=auth()->user()->id;
        $don=InKindDonation::where('user_id',$id)->where('status',$status)->get();
        return response()->json([
            'inkinddonations'=>$don
        ]);
    }
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'user_id'=>'required',
            'campaign_id'=>'required|exists:campaigns,id',
            'name'=>'required',
            'description'=>'required'
        ]);
        $camp = Campaign::where('id',$request->campaign_id)->first();
        if (!$camp->needs_inKindDonations){
            return response()->json([
                'message' => "the campaign {$camp->name} Doesn't need in-kind donations}"
            ]);
        }
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
