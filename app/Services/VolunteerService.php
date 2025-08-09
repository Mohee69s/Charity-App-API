<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\CampaignVoulnteers;
use App\Models\submit_users_opportunity;
use Carbon\Carbon;

class VolunteerService
{
    public function canCancel(int $userId, int $campaignId): bool
    {
        $camp = Campaign::find($campaignId);
        if (!$camp || !$camp->need_volunteers) {
            return false;
        }

        $vol = CampaignVoulnteers::where('campaign_id', $campaignId)
            ->where('user_id', $userId)
            ->first();

        $submission = submit_users_opportunity::where('user_id', $userId)
            ->whereHas('opportunity', function ($q) use ($campaignId) {
                $q->where('campaign_id', $campaignId);
            })
            ->first();

        if (!$vol && !$submission) {
            return false;
        }

        $startDate = Carbon::parse($camp->start_date);

        if ($submission && $submission->status === 'pending') {
            return true;
        }

        if (($vol->status ?? null) !== 'pending') {
            return false;
        }

        if ($startDate->isPast()) {
            return false;
        }

        return now()->diffInHours($startDate, false) > 24;
    }
}

