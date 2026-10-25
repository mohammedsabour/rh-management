<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategorieEquipement extends Model
{
    protected $table = 'categorie_equipements';

    protected $fillable = ['nom', 'code', 'numero_compte', 'parent_id', 'description'];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function enfants(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function equipements(): HasMany
    {
        return $this->hasMany(Equipement::class, 'categorie_id');
    }

    protected function setCodeAttribute(string $value): void
    {
        $this->attributes['code'] = strtoupper(trim($value));
    }
}