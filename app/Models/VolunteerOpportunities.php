<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VolunteerOpportunities extends Model
{
    /** @use HasFactory<\Database\Factories\VolunteerOpportunitiesFactory> */
    use HasFactory;
    public function campaign(){
        return $this->belongsTo(Campaign::class);
    }
    public function submit(){
        return $this->hasMany(submit_users_opportunity::class);
    }
}
