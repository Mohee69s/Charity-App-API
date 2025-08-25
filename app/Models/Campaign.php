<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    /** @use HasFactory<\Database\Factories\CampaignFactory> */
    use HasFactory;
    public function WalletTransactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }
    public function CampaignMedia()
    {
        return $this->hasOne(CampaignMedia::class);
    }
    public function Donation()
    {
        return $this->hasMany(Donation::class);
    }
    public function InKindDonation()
    {
        return $this->hasMany(InKindDonation::class);
    }
    public function VolunteerOpportunities()
    {
        return $this->hasOne(VolunteerOpportunities::class);
    }
    public function campaignvolunteers()
    {
        return $this->hasMany(CampaignVoulnteers::class);

    }
    public function inKind(){
        return $this->hasMany(InKind::class);
    }
}
