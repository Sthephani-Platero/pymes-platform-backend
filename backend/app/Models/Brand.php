<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    protected $fillable = [
        'pulsar_id',
        'name'
    ];

    public function profiles()
    {
        return $this->hasMany(Profile::class);
    }
}