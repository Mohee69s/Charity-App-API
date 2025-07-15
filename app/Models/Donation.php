<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Donation extends Model
{
    /** @use HasFactory<\Database\Factories\DonationFactory> */
    use HasFactory;
    public function User(){
        return $this->belongsTo(User::class);
    }
    public function Campaign(){
        return $this->belongsTo(Campaign::class);
    }
    public const UPDATED_AT = null;
    public const CREATED_AT = null;
}
