<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class submit_users_opportunity extends Model
{
    /** @use HasFactory<\Database\Factories\SubmitUsersOpportunityFactory> */
    use HasFactory;
    const UPDATED_AT = null;
    const CREATED_AT = null;
    public $incrementing = false;
    protected $primaryKey = null;
    protected $keyType = 'string';
    public function opportunity()
    {
        return $this->belongsTo(VolunteerOpportunities::class);
    }
}
