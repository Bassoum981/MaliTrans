<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bus extends Model
{
    protected $table = 'buses';

    protected $fillable = [
        'immatriculation',
        'capacite',
        'statut',
    ];

    public function trajets(): HasMany
    {
        return $this->hasMany(Trajet::class);
    }

}
