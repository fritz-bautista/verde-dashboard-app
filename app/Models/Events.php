<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Events extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'date',
        'floor',
        'attendees',
    ];

    protected $casts = [
        'date' => 'date',
        'attendees' => 'integer',
        'floor' => 'integer',
    ];
}
