<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WastePickup extends Model
{
    protected $fillable = ['bin_id', 'scheduled_time', 'status', 'user_id'];

    public function bin()
    {
        return $this->belongsTo(Bin::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}