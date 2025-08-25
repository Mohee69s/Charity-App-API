<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InKind extends Model
{
    protected $table = 'in_kind';
    public function InKindDonation(){
        return $this -> hasMany(InKindDonation::class);
    }
    public function campaign(){
        return $this -> belongsTo(Campaign::class);
    }
}
