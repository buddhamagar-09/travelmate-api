<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'category',
        'location',
        'budget_min',
        'budget_max',
        'duration',
        'difficulty',
        'group_size',
    ];

    protected $casts = [
        'budget_min' => 'integer',
        'budget_max' => 'integer',
        'group_size' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
