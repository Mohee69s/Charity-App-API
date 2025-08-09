<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Services\VolunteerService;
use App\Models\CampaignVoulnteers;
use App\Models\Donation;
use App\Models\submit_users_opportunity;
use App\Models\VolunteerOpportunities;
use App\Models\wallet;
use App\Models\WalletTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    // This is for returning donations campaigns
    public function donation(Request $request)
    {
        $type = $request->query("type");

        $campaigns = Campaign::where("status", "active")
            ->where(function ($query) use ($type) {
                $query->where("need_donations", true)
                    ->orWhere("campaign_type", $type);
            })
            ->get();

        return response()->json($campaigns);
    }


public function volunteer(Request $request)
{
    $user = auth()->user();
    if (!$user->is_volunteer) {
        return response()->json(["message" => "You're not volunteered"]);
    }

    $camps = Campaign::query()
        ->where('need_volunteers', true)
        ->when($request->query('type'), fn ($q, $type) => $q->where('type', $type))
        ->with('campaignMedia:id,campaign_id,url,media_type')
        ->get()
        ->map(function ($camp) {
            $media = $camp->campaignMedia; // Model or null (not a Collection)
            $camp->setAttribute('url', $media?->url);
            $camp->setAttribute('media_type', $media?->media_type);
            $camp->unsetRelation('campaignMedia');
            return $camp;
        });

    return response()->json(['campaigns' => $camps]);
}


public function volcamp($id, VolunteerService $volunteerservice)
{
    $user = auth()->user();

    $camp = Campaign::where('id', $id)
        ->with('campaignMedia') // hasOne relation
        ->with('VolunteerOpportunities')
        ->firstOrFail();

    $opp = VolunteerOpportunities::where('campaign_id', $id)->first();
    if (!$opp) {
        dd('no opp');
    }

    $sub = submit_users_opportunity::where('user_id', $user->id)
        ->where('opportunity_id', $opp->id)
        ->first();

    $can_cancel = $volunteerservice->canCancel($user->id, $id);

    $submitted = (bool) $sub;

    // Merge media data into campaign
    $media = $camp->campaignMedia;
    $camp->setAttribute('url', $media?->url);
    $camp->setAttribute('media_type', $media?->media_type);
    $camp->unsetRelation('campaignMedia');

    return response()->json([
        'camp'       => $camp,
        'submitted'  => $submitted,
        'can_cancel' => $can_cancel,
    ]);
}
   public function doncamp($id)
    {
        $camp = Campaign::where("id", $id)->with("CampaignMedia")->first();
        return response()->json([
            'campaign' => $camp
        ]);
    }

    public function donate(Request $request)
    {
        $request->validate([
            "amount" => "required|numeric",
            "wallet_pin" => "required",
            "campaign_id" => "required|exists:campaigns,id",
        ]);

        $user = auth();
        $wallet = wallet::where("user_id", $user->id())->first();

        $camp = Campaign::where("id", $request->campaign_id)->first();
        if ($camp->status != "active") {
            return response()->json([
                "message" => "the requested camp is in phase {$camp->status}, you can\'t make donations",
            ]);
        }
        if ($camp->cost >= $camp->goal) {
            return response()->json([
                "message" => "the goal has been achieved",
            ]);
        }
        if (!$camp->need_donations) {
            return response()->json([
                "message" => 'the campaign doesn\'t need donations',
            ]);
        }

        if ($wallet->wallet_pin == $request->wallet_pin) {
            if ($wallet->balance >= $request->amount) {
                $wallet->balance -= $request->amount;
                $wallet->save();
                $camp->cost += $request->amount;
                $camp->save();
                Donation::create([
                    "amount" => $request->amount,
                    "donation_date" => Carbon::now(),
                    "recurring" => false,
                    "campaign_id" => $camp->id,
                    "user_id" => auth()->user()->id,
                ])->save();
                // dd('mohee');
                WalletTransaction::create([
                    "wallet_id" => $wallet->id,
                    "type" => "donation",
                    "amount" => $request->amount,
                    "reference_id" => $camp->id,
                ])->save();
                return response()->json([
                    "message" => "Donation Completed",
                    "from" => $user->user()->full_name,
                    "to" => $camp->name,
                    "amount" => $request->amount,
                    "payment method" => "Donation wallet",
                    "time" => Carbon::now(),
                ]);
            } else {
                return response()->json([
                    "message" => "no enough balance",
                ]);
            }
        }
        return response()->json([
            "message" => "wrong pin",
        ]);
    }
    public function typesOfCampaigns()
    {
        $types = Campaign::where('status', 'active')->where('need_volunteers',true )
            ->distinct()
            ->pluck('campaign_type');
        return response()->json([
            'types' => $types
        ]);
    }
}
