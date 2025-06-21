<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\VolunteerOpportunities;
use Illuminate\Http\Request;

class VolunteerOpportunitiesController extends Controller
{
    public function index(Request $request){
        $camp = Campaign::where('id',$request->campaign_id)->first();
        $opp = VolunteerOpportunities::where('campaign_id',$camp->id)->first();
        return response()->json($opp);
    }
    public function store(Request $request){
        //TODO 
    }
}
