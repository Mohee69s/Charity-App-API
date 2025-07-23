<?php



namespace App\Http\Controllers;

use App\Models\Campaign;
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
        $camps = Campaign::where('status', 'active')->orWhere('status', '')->where('need_donations', true)->orWhere('need_in_kind_donations', true)->with('CampaignMedia')->get();
        if ($request->query('type')) {
            $camps = $camps->where('type', $request->query('type'));
        }
        return response()->json([
            'campaigns' => $camps
        ]);
    }
    public function volunteer(Request $request)
    {
        // This is for returning Volunteering campaigns
        $user = auth()->user();
        if (!$user->is_volunteer) {
            return response()->json([
                'message' => 'You\'re not volunteered'
            ]);
        }
        $camps = Campaign::where('need_volunteers', true)->with('CampaignMedia')->get();
        if ($request->query('type')) {
            $camps = $camps->where('type', $request->query('type'));
        }
        return response()->json([
            'campaigns' => $camps
        ]);
    }
    public function camp($id)
    {
        $user = auth()->user();
        if (\Route::current()->uri() == 'api/campaigns/{id}') {
            $camp = Campaign::where('id', $id)->with('CampaignMedia')->first();

            return response()->json([
                'campaign' => $camp,
            ]);
        }
        if (!$user->is_volunteer) {
            return response()->json([
                'message' => 'you are not volunteered'
            ]);
        }
        $camp = Campaign::where('id', $id)->with(relations: 'CampaignMedia')->with('VolunteerOpportunities')->first();
        $opp = VolunteerOpportunities::where('campaign_id', $id)->first();
        $sub = submit_users_opportunity::where('user_id', $user->id)->where('opportunity_id', $opp->id)->first();
        if ($sub) {
            $submitted = true;
        } else {
            $submitted = false;
        }
        return response()->json([
            'camp' => $camp,
            'submitted' => $submitted,
        ]);
    }

    public function donate(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric',
            'wallet_pin' => 'required',
            'campaign_id' => 'required|exists:campaigns,id'
        ]);

        $user = auth();
        $wallet = wallet::where('user_id', $user->id())->first();

        $camp = Campaign::where('id', $request->campaign_id)->first();
        if ($camp->status != 'active') {
            return response()->json([
                'message' => "the requested camp is in phase {$camp->status}, you can\'t make donations"
            ]);
        }
        if ($camp->cost >= $camp->goal) {
            return response()->json([
                'message' => 'the goal has been achieved'
            ]);
        }
        if (!$camp->need_donations) {
            return response()->json([
                'message' => 'the campaign doesn\'t need donations'
            ]);
        }

        if ($wallet->wallet_pin == $request->wallet_pin) {
            if ($wallet->balance >= $request->amount) {
                $wallet->balance -= $request->amount;
                $wallet->save();
                $camp->cost += $request->amount;
                $camp->save();
                Donation::create([
                    'amount' => $request->amount,
                    'donation_date' => Carbon::now(),
                    'recurring' => false,
                    'campaign_id' => $camp->id,
                    'user_id' => auth()->user()->id,
                ])->save();
                // dd('mohee');
                WalletTransaction::create([
                    'wallet_id' => $wallet->id,
                    'type' => 'donation',
                    'amount' => $request->amount,
                    'reference_id' => $camp->id,
                ])->save();
                return response()->json([
                    'message' => 'Donation Completed',
                    'from' => $user->user()->full_name,
                    'to' => $camp->name,
                    'amount' => $request->amount,
                    'payment method' => 'Donation wallet',
                    'time' => Carbon::now()

                ]);
            } else {
                return response()->json([
                    'message' => 'no enough balance'
                ]);
            }
        }
        return response()->json([
            'message' => 'wrong pin'
        ]);
    }


}
