<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bin extends Model
{
    protected $fillable = ['level_id', 'type', 'capacity'];

    public function level()
    {
        return $this->belongsTo(Level::class);
    }

    public function wasteLevels()
    {
        return $this->hasMany(WasteLevel::class);
    }
}
