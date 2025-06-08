<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    /** @use HasFactory<\Database\Factories\WalletTransactionFactory> */
    use HasFactory;
    public function wallet(){
        return $this->belongsTo(wallet::class);
    }
    public function campaign (){
        return $this->belongsTo(Campaign::class);
    }
}
