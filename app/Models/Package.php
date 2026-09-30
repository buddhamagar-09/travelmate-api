<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

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

    public function trekHighlights()
    {
        return $this->hasMany(Trekhighlight::class);
    }
}
