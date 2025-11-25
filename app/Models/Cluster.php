<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Cluster extends Model
{
    protected $fillable = ['name', 'floor'];

    public function bins()
    {
        return $this->hasMany(Bin::class);
    }
}