<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Itinerary extends Model
{
    protected $fillable = [
        'package_id',
        'day_number',
        'title',
        'description',
        'overnight_stay',
    ];

    // Each itinerary day belongs to one package
    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}
// This code defines the Itinerary model in a Laravel application. 
//The model represents the "itineraries" table in the database and 
//defines the fillable attributes that can be mass-assigned. It also establishes a relationship with the Package model, 
//indicating that each itinerary day belongs to one package.
