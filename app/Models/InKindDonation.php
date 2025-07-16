<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InKindDonation extends Model
{
    /** @use HasFactory<\Database\Factories\InKindDonationFactory> */
    use HasFactory;

    public function InKind(){
        return $this->belongsTo(InKind::class);
    }
    public function campaign(){
        return $this->belongsTo(Campaign::class);
    }
}
