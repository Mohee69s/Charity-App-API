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
            ->with("inKind")->get();

        return response()->json($campaigns);
    }


    public function volunteer(Request $request, VolunteerService $volunteerservice)
    {
        $user = auth()->user();
        if (!$user->is_volunteer) {
            return response()->json(['message' => "You're not volunteered"], 403);
        }

        $camps = Campaign::query()
            ->where('need_volunteers', true)
            ->when($request->query('type'), fn($q, $type) => $q->where('type', $type))
            ->with([
                'campaignMedia:id,campaign_id,url,media_type',
                'VolunteerOpportunities' => fn($q) => $q->select(
                    'id',
                    'campaign_id',
                    'title',
                    'tasks',
                    'duration',
                    'location',
                    'created_at'
                ),
            ])
            ->get();

        $oppByCamp = [];
        foreach ($camps as $camp) {
            $firstOpp = $camp->VolunteerOpportunities?->first();
            if ($firstOpp) {
                $oppByCamp[$camp->id] = $firstOpp->id;
            }
        }


        $subs = submit_users_opportunity::query()
            ->where('user_id', $user->id)
            ->when($oppByCamp, fn($q) => $q->whereIn('opportunity_id', array_values($oppByCamp)))
            ->get()
            ->keyBy('opportunity_id');
        $camps = $camps->map(function ($camp) use ($user, $volunteerservice, $oppByCamp, $subs) {
            $media = $camp->campaignMedia;
            $camp->setAttribute('url', $media?->url);
            $camp->setAttribute('media_type', $media?->media_type);

            $oppId = $oppByCamp[$camp->id] ?? null;
            $camp->setAttribute('submitted', $oppId ? $subs->has($oppId) : false);
            $camp->setAttribute('can_cancel', (bool) $volunteerservice->canCancel($user->id, $camp->id));

            $camp->unsetRelation('campaignMedia');
            $datett= Carbon::parse($camp->date)->toDateString();
            $camp->setAttribute('date',$datett);
            $days_left = -Carbon::parse($camp->start_date)->diffInUTCDays(Carbon::now());
            if ($days_left < 0) {
                $days_left = 0;
            }else{
                $days_left = floor($days_left );
            }
            $camp->setAttribute('days_left', $days_left);
            $start = $camp->start_date ? Carbon::parse($camp->start_date)->format('H:i') : null;
            $end = $camp->end_date ? Carbon::parse($camp->end_date)->format('H:i') : null;
            $camp->setAttribute('work_time', ($start && $end) ? "{$start}-{$end}" : null);

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
            dd("mohee");
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
            'camp' => $camp,
            'submitted' => $submitted,
            'can_cancel' => $can_cancel,
        ]);
    }
    public function doncamp($id)
    {
        $camp = Campaign::where("id", $id)->with("CampaignMedia")->with("inKind")->first();
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
        $types = Campaign::where('status', 'active')->where('need_volunteers', true)
            ->distinct()
            ->pluck('campaign_type');
        return response()->json([
            'types' => $types
        ]);
    }
    public function completedCampaigns()
    {
        $completed = Campaign::where('status', 'completed')
            ->with('VolunteerOpportunities')
            ->get();

        $withOpportunities = $completed->filter(function ($campaign) {
            return $campaign->VolunteerOpportunities && $campaign->VolunteerOpportunities->count() > 0;
        });

        $withoutOpportunities = $completed->filter(function ($campaign) {
            return !$campaign->VolunteerOpportunities || $campaign->VolunteerOpportunities->count() === 0;
        });
        return response()->json([
            'Donation campaigns'=> $withoutOpportunities,
            'Volunteering campaigns'=> $withOpportunities
        ]);

    }
}
