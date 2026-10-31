<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Billet extends Model
{
    protected $fillable = [
        'code_qr_hash',
        'trajet_id',
        'nom_passager',
        'telephone_passager',
        'numero_siege',
        'type_vente',
        'vendu_par',
        'prix',
        'statut',
        'date_validation',
    ];

    protected $casts = [
        'date_validation' => 'datetime',
        'prix' => 'decimal:2',
    ];

    public function trajet(): BelongsTo
    {
        return $this->belongsTo(Trajet::class);
    }

    public function vendeur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendu_par');
    }
}
