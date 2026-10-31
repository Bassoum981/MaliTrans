<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PositionGps extends Model
{
    protected $table = 'positions_gps';

    // Désactiver les timestamps automatiques si seule la colonne 'recorded_at' existe
    public $timestamps = false;

    protected $fillable = [
        'trajet_id',
        'latitude',
        'longitude',
        'recorded_at',
    ];

    public function trajet(): BelongsTo
    {
        return $this->belongsTo(Trajet::class);
    }
}
