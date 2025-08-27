<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\VolunteerApplications;
use App\Models\VolunteerOpportunities;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VolunteerApplicationsController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            "full_name" => "required",
            "phone_number" => "required|numeric",
            "skills" => "required",
            "previous_experience" => "required",
            "gender" => "required",
            "age" => "required"
        ]);
        VolunteerApplications::create(attributes: [
            "user_id" => auth()->user()->id,
            "full_name" => $request->full_name,
            "phone_number" => $request->phone_number,
            "skills" => $request->skills,
            "available_time" => 'notUsed',
            "hours_per_week" => 0,
            "previous_experience" => $request->previous_experience,
            "gender" => $request->gender,
            
            "age" => $request->age,
        ])->save();

        return response()->json(data: [
            "message" => "Volunteer application sent successfully, wait for your approval in the next 48 hours"
        ]);
    }
    public function status(){
        $user = auth()->user();
        $sub = VolunteerApplications::where('user_id', auth()->user()->id)->first();
        $result = $sub->status ?? 'have_not_applied';
        return response()->json([
           'status'=>$result
        ]);
    }
}
