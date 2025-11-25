<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WastePrediction extends Model
{
    protected $fillable = [
        'bin_id',
        'date',
        'predicted_overflow',
        'collection_needed',
    ];

    protected $casts = [
        'date' => 'date',
        'predicted_overflow' => 'boolean',
        'collection_needed' => 'boolean',
    ];

    public function bin()
    {
        return $this->belongsTo(Bin::class);
    }
}
