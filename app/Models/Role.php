<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    public $table = "roles";
    public function users()
{
    return $this->belongsToMany(User::class, 'user_roles');
}

}

