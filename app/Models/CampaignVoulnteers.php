<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampaignVoulnteers extends Model
{
    /** @use HasFactory<\Database\Factories\CampaignVoulnteersFactory> */
    use HasFactory;
    protected $table = 'campaign_volunteers';
    public function campaign(){
        return $this->belongsTo(Campaign::class,'campaignId');
    }
}
