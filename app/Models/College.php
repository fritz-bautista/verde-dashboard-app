<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class College extends Model
{
    protected $fillable = ['name', 'floor'];

    public function levels()
    {
        return $this->hasMany(Level::class, 'floor_number', 'floor');
    }

    // App\Models\College.php

    public function students()
    {
        // Only count users with role 'student'
        return $this->hasMany(\App\Models\User::class)->where('role', 'student');
    }

}
