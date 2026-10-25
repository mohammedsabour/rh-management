<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Equipement extends Model
{
    protected $table = 'equipements';

    public const ETATS = [
        'neuf'         => 'Neuf',
        'bon'          => 'Bon',
        'use'          => 'Usé',
        'degrade'      => 'Dégradé',
        'hors_service' => 'Hors service',
    ];

    public const STATUTS = [
        'en_stock'       => 'En stock',
        'affecte'        => 'Affecté',
        'en_maintenance' => 'En maintenance',
        'reforme'        => 'Réformé',
        'perdu'          => 'Perdu',
    ];

    // code_barre n'est pas fillable : il est généré automatiquement (voir booted)
    protected $fillable = [
        'numero_inventaire', 'designation', 'categorie_id',
        'marque', 'modele', 'numero_serie',
        'etat', 'statut', 'emplacement_id',
        'date_acquisition', 'prix_acquisition', 'reference_facture', 'date_fin_garantie',
        'photo', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'date_acquisition'  => 'date',
            'date_fin_garantie' => 'date',
            'prix_acquisition'  => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        // Le code-barres a besoin de l'id : on le génère juste après l'insertion.
        // Format : CAT-ANNEE-000123 (ex. INF-2026-000123)
        static::created(function (self $equipement) {
            $prefixe = $equipement->categorie->code;
            $equipement->code_barre = sprintf('%s-%d-%06d', $prefixe, now()->year, $equipement->id);
            $equipement->saveQuietly();
        });
    }

    /* ---------- Relations ---------- */

    public function categorie(): BelongsTo
    {
        return $this->belongsTo(CategorieEquipement::class, 'categorie_id');
    }

    public function emplacement(): BelongsTo
    {
        return $this->belongsTo(Emplacement::class);
    }

   /*  public function affectations(): HasMany
    {
        return $this->hasMany(Affectation::class)->latest('date_debut');
    }

    // Affectation en cours (date_fin nulle), s'il y en a une
    public function affectationEnCours(): HasOne
    {
        return $this->hasOne(Affectation::class)->whereNull('date_fin')->latestOfMany('date_debut');
    }

    public function lignesInventaire(): HasMany
    {
        return $this->hasMany(LigneInventaire::class);
    } */

    /* ---------- Scopes ---------- */

    public function scopeStatut(Builder $query, string $statut): Builder
    {
        return $query->where('statut', $statut);
    }

    public function scopeDisponibles(Builder $query): Builder
    {
        return $query->where('statut', 'en_stock');
    }

    /* ---------- Utilitaires ---------- */

    // Recherche par scan : Equipement::parCodeBarre($valeur)->first()
    public function scopeParCodeBarre(Builder $query, string $code): Builder
    {
        return $query->where('code_barre', trim($code));
    }

    public function getSousGarantieAttribute(): bool
    {
        return $this->date_fin_garantie !== null && $this->date_fin_garantie->isFuture();
    }
}