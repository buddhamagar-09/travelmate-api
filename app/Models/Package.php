<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Review;

class Package extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'short_description',
        'long_description',
        'price',
        'duration',
        'difficulty',
        'max_altitude',
        'group_size',
        'best_season',
        'location',
        'featured_image',
        'is_featured',
    ];

    // A package has many itinerary days
    public function itineraries()
    {
        return $this->hasMany(Itinerary::class);
    }

    // A package has many gallery images
    public function galleries()
    {
        return $this->hasMany(Gallery::class);
    }

    // A package has many includes
    public function includes()
    {
        return $this->hasMany(Includes::class);
    }
    // A package has many excludes
    public function excludes()
    {
        return $this->hasMany(Excludes::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}

// it is used to define the Package model in a Laravel application. The model represents the "packages" table in the database 
// and defines the fillable attributes that can be mass-assigned. It also establishes relationships with the Itinerary and Gallery models,
// indicating that a package can have many itineraries and galleries associated with it.