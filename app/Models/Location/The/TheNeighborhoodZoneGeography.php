<?php

namespace App\Models\Location\The;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TheNeighborhoodZoneGeography extends Model
{
    use HasFactory;

    public function neighborhoodZone()
    {
        return $this->belongsTo(TheNeighborhoodZone::class);
    }

}
