<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trajet extends Model
{
    use HasFactory;

    protected $fillable = [
        'bus_id',
        'ligne_id',
        'chauffeur_id',
        'heure_depart_prevue',
        'heure_depart_reelle',
        'heure_arrivee_reelle',
        'statut',
    ];

    protected $casts = [
        'heure_depart_prevue' => 'datetime',
        'heure_depart_reelle' => 'datetime',
        'heure_arrivee_reelle' => 'datetime',
    ];

    public function bus(): BelongsTo
    {
        return $this->belongsTo(Bus::class);
    }

    public function ligne(): BelongsTo
    {
        return $this->belongsTo(Ligne::class);
    }

    public function chauffeur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'chauffeur_id');
    }

    public function billets(): HasMany
    {
        return $this->hasMany(Billet::class);
    }

    public function positionsGps(): HasMany
    {
        return $this->hasMany(PositionGps::class);
    }
}
