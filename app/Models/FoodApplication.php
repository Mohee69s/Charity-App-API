<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FoodApplication extends Model
{
    protected $table = "food_forms";
    const UPDATED_AT = null;
    const CREATED_AT = null;
    public function assistanceRequest()
    {
        return $this->belongsTo(AssitanceRequest::class, 'request_id', 'id');
    }
}
