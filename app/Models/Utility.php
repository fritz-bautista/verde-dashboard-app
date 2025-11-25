<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Utility extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'contact', 'status'];

    public function assignments()
    {
        return $this->hasMany(UtilityAssignment::class);
    }
}
