<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UtilityAssignment extends Model
{
    use HasFactory;
    protected $fillable = ['utility_id', 'level_id', 'assigned_date', 'status'];

    public function utility()
    {
        return $this->belongsTo(Utility::class);
    }

    public function level()
    {
        return $this->belongsTo(Level::class);
    }
}
