<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WasteLevel extends Model
{
    protected $fillable = ['bin_id', 'weight', 'level']; // add distance if needed


    public function bin()
    {
        return $this->belongsTo(Bin::class);
    }
}
