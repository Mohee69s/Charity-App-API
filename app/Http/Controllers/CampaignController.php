<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\wallet;
use App\Models\WalletTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    public function donation(Request $request){
        $camps=Campaign::where('needs_donations',true)->orWhere('needs_inKindDonations',true)->with('CampaignMedia')->get();
        if ($request -> query('type')){
           $camps=$camps->where('type',$request->query('type'));
        }
        return response()->json([
            'campaigns' => $camps
        ]);
    }
    public function volunteer(Request $request){
        $camps=Campaign::where('needs_volunteers',true)->with('CampaignMedia')->get();
        if ($request -> query('type')){
           $camps=$camps->where('type',$request->query('type'));
        }
        return response()->json([
            'campaigns' => $camps
        ]);
    }
    public function camp($id){
        $camp=Campaign::where('campaign_id',$id)->with('CampaignMedia')->first();
        return response()->json([
            'campaign'=>$camp
        ]);
    }


}
