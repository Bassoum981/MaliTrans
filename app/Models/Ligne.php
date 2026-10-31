<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ligne extends Model
{
    protected $fillable = [
        'ville_depart',
        'ville_arrivee',
        'distance_km',
        'prix_base',
    ];

    public function trajets(): HasMany
    {
        return $this->hasMany(Trajet::class);
    }
}
