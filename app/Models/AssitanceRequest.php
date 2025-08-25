<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssitanceRequest extends Model
{
    protected $table = "assistance_requests";

    public function educationalForm()
    {   // FK: educational_forms.request_id -> assistance_requests.id
        return $this->hasOne(EducationalApplication::class, 'request_id', 'id');
    }
    public function medicalForm()
    {
        return $this->hasOne(MedicalApplication::class, 'request_id', 'id');
    }
    public function foodForm()
    {
        return $this->hasOne(FoodApplication::class, 'request_id', 'id');
    }
}
