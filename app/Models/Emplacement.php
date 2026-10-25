<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Emplacement extends Model
{
    protected $table = 'emplacements';

    public const TYPES = [
        'batiment' => 'Bâtiment',
        'etage'    => 'Étage',
        'bureau'   => 'Bureau',
        'salle'    => 'Salle',
        'magasin'  => 'Magasin',
        'autre'    => 'Autre',
    ];

    protected $fillable = ['nom', 'code', 'type', 'parent_id', 'departement_id'];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function enfants(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function departement(): BelongsTo
    {
        return $this->belongsTo(Departement::class);
    }

    public function equipements(): HasMany
    {
        return $this->hasMany(Equipement::class);
    }

    // "Bâtiment A › 1er étage › Bureau 12"
    public function getCheminCompletAttribute(): string
    {
        $noms = [$this->nom];
        $courant = $this;

        while ($courant->parent_id && $courant = $courant->parent) {
            array_unshift($noms, $courant->nom);
        }

        return implode(' › ', $noms);
    }

    // Id de cet emplacement + tous ses descendants (pour limiter un récolement à un bâtiment, par ex.)
    public function idsAvecDescendants(): array
    {
        $ids = [$this->id];
        $niveau = [$this->id];

        while ($niveau) {
            $niveau = self::whereIn('parent_id', $niveau)->pluck('id')->all();
            $ids = array_merge($ids, $niveau);
        }

        return $ids;
    }
}