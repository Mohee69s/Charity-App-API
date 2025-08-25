<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicalApplication extends Model
{
    protected $table = "medical_forms";
    const UPDATED_AT = null;
    const CREATED_AT = null;
    public function assistanceRequest()
    {
        return $this->belongsTo(AssitanceRequest::class, 'request_id', 'id');
    }
}
