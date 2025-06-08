<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class wallet extends Model
{
    /** @use HasFactory<\Database\Factories\WalletFactory> */
    use HasFactory;
    public function user(){
        return $this->belongsTo(User::class);
    }
    public function WalletTransactions(){
        return $this->hasMany(WalletTransaction::class);
    }
}
