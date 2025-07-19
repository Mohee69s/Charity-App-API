<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\CampaignVoulnteers;
use App\Models\submit_users_opportunity;
use App\Models\VolunteerOpportunities;
use Carbon\Carbon;
use Illuminate\Http\Request;

class VolunteeringController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $subs = submit_users_opportunity::where("user_id", $user->id)->get();
        $camps = [];

        foreach ($subs as $sub) {
            $vol = VolunteerOpportunities::where('id', $sub->opportunity_id)->first();
            $camp = Campaign::where('id', $vol->campaign_id)->first();
            $campvol = CampaignVoulnteers::where('campaignId', $camp->id)->first();

            $camp['submittion_status'] = $sub->status;

            if ($sub->status == 'accepted') {
                $camp['volunteering_status'] = $campvol ? $campvol->status : null;
            } else {
                $camp['volunteering_status'] = null;
            }

            // can_cancel logic
            $volunteeringStatus = $camp['volunteering_status'];

            if ($sub->status === 'accepted') {
                $startDate = Carbon::parse($camp->start_date);

                if ($startDate->isPast()) {
                    $camp['can_cancel'] = false;
                } else {
                    $hoursLeft = now()->diffInHours($startDate, false);

                    if ($hoursLeft > 24) {
                        $camp['can_cancel'] = true;
                    } else {
                        $camp['can_cancel'] = false;
                    }
                }
            } elseif ($sub->status === 'pending') {
                $camp['can_cancel'] = true;
            } else {
                $camp['can_cancel'] = false;
            }

            // force can_cancel = false if volunteering_status != pending
            if ($volunteeringStatus !== null && $volunteeringStatus !== 'pending') {
                $camp['can_cancel'] = false;
            }

            // time range formatting
            $start = Carbon::parse($camp->start_date)->format('H:i');
            $end = Carbon::parse($camp->end_date)->format('H:i');
            $camp['time'] = $start . '-' . $end;

            $camps[] = $camp;
        }

        return response()->json($camps);
    }
    public function cancelVol($id)
    {
        $user = auth()->user();
        $camp = Campaign::where('id', $id)->first();

        if (!$camp || !$camp->need_volunteers) {
            return response()->json([
                'message' => 'This campaign does not need volunteers.'
            ]);
        }

        $vol = CampaignVoulnteers::where('campaign_id', $id)
            ->where('user_id', $user->id)
            ->first();

        $submission = submit_users_opportunity::where('user_id', $user->id)
            ->whereHas('opportunity', function ($q) use ($id) {
                $q->where('campaign_id', $id);
            })
            ->first();

        if (!$vol && !$submission) {
            return response()->json([
                'message' => 'You are not registered as volunteer.'
            ]);
        }

        $startDate = Carbon::parse($camp->start_date);
        $canCancel = false;

        if ($submission && $submission->status === 'pending') {
            $canCancel = true;
        } else {
            $volunteeringStatus = $vol ? $vol->status : null;

            if ($volunteeringStatus !== 'pending') {
                $canCancel = false;
            } elseif ($startDate->isPast()) {
                $canCancel = false;
            } else {
                $hoursLeft = now()->diffInHours($startDate, false);

                if ($hoursLeft > 24) {
                    $canCancel = true;
                } else {
                    $canCancel = false;
                }
            }
        }

        if (!$canCancel) {
            return response()->json([
                'message' => 'You cannot cancel this volunteer participation.'
            ]);
        }

        if ($vol) {
            $vol->status = 'cancelled';
            $vol->save();
        }

        return response()->json([
            'message' => 'Cancelled successfully.'
        ]);
    }


}
