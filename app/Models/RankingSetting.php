<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RankingSetting extends Model
{
    protected $fillable = [
        'semester_name', 'is_active', 'started_at', 'stopped_at', 'status'
    ];
}
