<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LigneInventaire extends Model
{
    protected $table = 'ligne_inventaires';

    protected $fillable = [
        'inventaire_id', 'equipement_id', 'emplacement_constate_id',
        'etat_constate', 'scanne_le', 'scanne_par', 'remarque',
    ];

    protected function casts(): array
    {
        return [
            'scanne_le' => 'datetime',
        ];
    }

    public function inventaire(): BelongsTo
    {
        return $this->belongsTo(Inventaire::class);
    }

    public function equipement(): BelongsTo
    {
        return $this->belongsTo(Equipement::class);
    }

    public function emplacementConstate(): BelongsTo
    {
        return $this->belongsTo(Emplacement::class, 'emplacement_constate_id');
    }

    public function scannePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scanne_par');
    }

    // Biens retrouvés / introuvables
    public function scopeRetrouvees(Builder $query): Builder
    {
        return $query->whereNotNull('scanne_le');
    }

    public function scopeManquantes(Builder $query): Builder
    {
        return $query->whereNull('scanne_le');
    }

    // Retrouvé, mais pas à l'emplacement enregistré
    public function scopeDeplacees(Builder $query): Builder
    {
        return $query->whereNotNull('scanne_le')
            ->whereNotNull('emplacement_constate_id')
            ->whereHas('equipement', fn ($q) => $q->where(
                fn ($w) => $w->whereColumn('equipements.emplacement_id', '<>', 'ligne_inventaires.emplacement_constate_id')
                    ->orWhereNull('equipements.emplacement_id')
            ));
    }

    public function getDeplaceAttribute(): bool
    {
        return $this->scanne_le !== null
            && $this->emplacement_constate_id !== null
            && $this->emplacement_constate_id !== $this->equipement->emplacement_id;
    }

    /** Situation pour l'affichage : A_RETROUVER | RETROUVE | DEPLACE */
    public function getSituationAttribute(): string
    {
        if ($this->scanne_le === null) {
            return 'a_retrouver';
        }
 
        return $this->deplace ? 'deplace' : 'retrouve';
    }

}