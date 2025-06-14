<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\wallet;
use App\Models\WalletTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    public function index(Request $request){
        $camps=Campaign::with('CampaignMedia')->get();
        return response()->json([
            'campaigns' => $camps
        ]);
    }
    public function camp(Request $request){
        $request->validate([
            'campaign_id'=>'required|exists:campaigns,id'
        ]);
        $camp=Campaign::where('campaign_id',$request->campaign_id)->first();
        return response()->json([
            'campaign'=>$camp
        ]);
    }
    
    
}
