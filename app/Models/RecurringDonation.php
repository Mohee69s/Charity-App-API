<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecurringDonation extends Model
{
    /** @use HasFactory<\Database\Factories\RecurringDonationFactory> */
    use HasFactory;
    public function User(){
        return $this->belongsTo(User::class);
    }
    public const UPDATED_AT = null;
}
