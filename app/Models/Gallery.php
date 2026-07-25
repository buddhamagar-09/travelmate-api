<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gallery extends Model
{
    protected $fillable = [
        'package_id',
        'image_path',
       
    ];

    // Each gallery image belongs to one package
    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}

// This code defines the Gallery model in a Laravel application.
// The model represents the "galleries" table in the database