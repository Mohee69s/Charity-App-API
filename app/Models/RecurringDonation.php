<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecurringDonation extends Model
{
    /** @use HasFactory<\Database\Factories\RecurringDonationFactory> */
    use HasFactory;
    public function User()
    {
        return $this->belongsTo(User::class);
    }
    public const UPDATED_AT = null;
    // App\Models\RecurringDonation.php
    // App\Models\RecurringDonation.php
    protected $casts = [
        'is_active' => 'boolean',
        'next_run' => 'datetime',
        'start_date' => 'datetime',
        'amount' => 'decimal:2',
    ];


}
