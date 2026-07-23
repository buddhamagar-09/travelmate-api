<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Includes extends Model
{
    protected $fillable = [
        'package_id',
        'item',
    ];

    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}
