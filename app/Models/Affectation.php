<?php


namespace App\Models;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Affectation extends Model
{
    protected $table = 'affectations';
 
    protected $fillable = [
        'equipement_id', 'employe_id', 'departement_id',
        'date_debut', 'date_fin', 'notes', 'cree_par',
    ];
 
    protected function casts(): array
    {
        return [
            'date_debut' => 'date',
            'date_fin'   => 'date',
        ];
    }
 
    public function equipement(): BelongsTo
    {
        return $this->belongsTo(Equipement::class);
    }
 
    public function employe(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employe_id');
    }
 
    public function departement(): BelongsTo
    {
        return $this->belongsTo(Departement::class);
    }
 
    public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cree_par');
    }
 
    public function scopeEnCours(Builder $query): Builder
    {
        return $query->whereNull('date_fin');
    }
 
    public function getEnCoursAttribute(): bool
    {
        return $this->date_fin === null;
    }
}
