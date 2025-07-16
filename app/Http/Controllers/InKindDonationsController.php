<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\InKind;
use App\Models\InKindDonation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class InKindDonationsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $id = auth()->user()->id;
        $don = InKindDonation::where('user_id', $id)->with('InKind')->with('campaign')->get();
        if ($request -> query('status')){
            $don = $don->where('status',$request->query('status'));
        }
        return response()->json([
            'inkinddonations' => $don
        ]);
    }
    public function store(Request $request, $id): JsonResponse
    {
        $request->validate([
            'name' => 'required',
            'quantity' => 'required|numeric',
            'description' => 'string'
        ]);
        $des = null;
        if ($request->description) {
            $des = $request->description;
        }

        $camp = Campaign::where('id', $id)->first();
        if (!$camp->need_in_kind_donations) {
            return response()->json([
                'message' => 'this Campaign doesn\'t need in kind donations'
            ]);
        }
        $inkind = InKind::where('campaign_id',$id)->where('name',$request->name)->first();
        if ($inkind->goal <= $inkind->cost){
            return response()->json([
                'message'=>'this campaign doesn\'t need {$inkind->name} anymore'
            ]);
        }
        InKindDonation::create([
            'status' => 'pending',
            'approved' => false,
            'in_kind_id'=> $inkind->id,
            'campaign_id'=>$camp->id,
            'user_id'=>auth()->user()->id,
            //TODO check if description is required in the system
            'description'=> 'null'
        ])->save();
        return response()->json([
            'message'=> 'donation made, wait for approval'
        ]);

        

    }
}
