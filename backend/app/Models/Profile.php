<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $fillable = [
        'pulsar_id',
        'brand_id',
        'name',
        'source',
        'plugged'
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
}