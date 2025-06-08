<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    /** @use HasFactory<\Database\Factories\CampaignFactory> */
    use HasFactory;
    public function WalletTransactions(){
        return $this->hasMany(WalletTransaction::class);
    }
    public function CampaignMedia(){
        return $this->hasOne(CampaignMedia::class);
    }
}
