<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Excludes extends Model
{
    protected $fillable = [
        'package_id',
        'item',
    ];

    // A exclude belongs to a package
    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}
