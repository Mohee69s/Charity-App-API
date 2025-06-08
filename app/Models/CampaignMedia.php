<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampaignMedia extends Model
{
    /** @use HasFactory<\Database\Factories\CampaignMediaFactory> */
    use HasFactory;
    public function campaign(){
        return $this->belongsTo(Campaign::class);
    }
}
