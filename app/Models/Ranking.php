<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ranking extends Model
{
    use HasFactory;

    protected $fillable = [
        'college_id',
        'score',
        'waste_managed',
    ];

    public function college()
    {
        return $this->belongsTo(College::class);
    }
}
