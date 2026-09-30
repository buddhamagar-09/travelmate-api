<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class TrekHighlight extends Model
{
    protected $table = 'trek_highlights';
    protected $fillable = [
        'package_id',
        'highlight',
    ];

    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}
