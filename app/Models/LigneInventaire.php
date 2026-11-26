<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Ce qu'UNE commission a constaté (ou n'a pas retrouvé) pour UN équipement.
 */
class LigneInventaire extends Model
{
    protected $table = 'ligne_inventaires';

    protected $fillable = [
        'inventaire_id', 'commission_id', 'equipement_id',
        'emplacement_attendu_id', 'etat_attendu', 'hors_perimetre',
        'emplacement_constate_id', 'etat_constate', 'scanne_le', 'scanne_par', 'remarque',
    ];

    protected function casts(): array
    {
        return [
            'scanne_le' => 'datetime',
            'hors_perimetre' => 'boolean',
        ];
    }

    public function inventaire(): BelongsTo
    {
        return $this->belongsTo(Inventaire::class);
    }

    public function commission(): BelongsTo
    {
        return $this->belongsTo(CommissionInventaire::class, 'commission_id');
    }

    public function equipement(): BelongsTo
    {
        return $this->belongsTo(Equipement::class);
    }

    public function emplacementAttendu(): BelongsTo
    {
        return $this->belongsTo(Emplacement::class, 'emplacement_attendu_id');
    }

    public function emplacementConstate(): BelongsTo
    {
        return $this->belongsTo(Emplacement::class, 'emplacement_constate_id');
    }

    public function scannePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scanne_par');
    }

    public function scopeRetrouvees(Builder $query): Builder
    {
        return $query->whereNotNull('scanne_le');
    }

    public function scopeManquantes(Builder $query): Builder
    {
        return $query->whereNull('scanne_le');
    }

    public function getRetrouveAttribute(): bool
    {
        return $this->scanne_le !== null;
    }
}
