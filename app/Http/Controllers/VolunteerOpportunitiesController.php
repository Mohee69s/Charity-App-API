<?php

namespace App\Http\Controllers;

use App\Models\VolunteerOpportunities;
use Illuminate\Http\JsonResponse;

class VolunteerOpportunitiesController extends Controller
{
    public function index($id): JsonResponse{
        $opp = VolunteerOpportunities::where('campaign_id',$id)->first();
        return response()->json([$opp]);
    }
}
