<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    protected $fillable = ['college_id', 'name', 'floor_number'];

    // A level belongs to a college
    public function college()
    {
        return $this->belongsTo(College::class);
    }

    // A level has many bins
    public function bins()
    {
        return $this->hasMany(Bin::class);
    }
}
