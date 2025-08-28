<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class wallet extends Model
{
    /** @use HasFactory<\Database\Factories\WalletFactory> */
    use HasFactory;
    public function User()
    {
        return $this->belongsTo(User::class);
    }
    public function WalletTransactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }
    public const CREATED_AT = null;

    public function setWalletPinAttribute($value): void
    {
        if (is_null($value) || $value === '') {
            $this->attributes['wallet_pin'] = null;
            return;
        }

        $value = (string) $value;

        // If it already looks like a bcrypt hash, don't re-hash
        if (Str::startsWith($value, '$2y$') && strlen($value) === 60) {
            $this->attributes['wallet_pin'] = $value;
            return;
        }

        $this->attributes['wallet_pin'] = Hash::make($value);
    }
}
