<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class Inventaire extends Model
{
    protected $table = 'inventaires';

    public const STATUTS = [
        'ouvert'  => 'Ouvert',
        'cloture' => 'Clôturé',
    ];

    protected $fillable = [
        'nom', 'date_debut', 'date_fin', 'statut', 'emplacement_id', 'cree_par', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'date_debut' => 'date',
            'date_fin'   => 'date',
        ];
    }

    public function emplacement(): BelongsTo
    {
        return $this->belongsTo(Emplacement::class);
    }

    public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cree_par');
    }

    public function lignes(): HasMany
    {
        return $this->hasMany(LigneInventaire::class);
    }

    public function getEstOuvertAttribute(): bool
    {
        return $this->statut === 'ouvert';
    }

    // Équipements attendus
    // Les biens réformés ou perdus sont exclus du contrôle.
    public function equipementsAttendus()
    {
        return Equipement::query()
            ->whereNotIn('statut', ['reforme', 'perdu'])
            ->when($this->emplacement_id, fn ($q) => $q->whereIn('emplacement_id', $this->emplacement->idsAvecDescendants()));
    }

    /** Crée une ligne « à retrouver » par équipement attendu.
     * Retourne le nombre de lignes créées.
     */
    public function genererLignes(): int
    {
        $maintenant = now();
        $ids = $this->equipementsAttendus()->pluck('id');
 
        foreach ($ids->chunk(500) as $groupe) {
            LigneInventaire::insert($groupe->map(fn ($id) => [
                'inventaire_id' => $this->id,
                'equipement_id' => $id,
                'created_at' => $maintenant,
                'updated_at' => $maintenant,
            ])->all());
        }
 
        return $ids->count();
    }

    /** Chiffres de la campagne : attendus, retrouvés, manquants, déplacés, avancement en %. */
    public function statistiques(): array
    {
        if ($this->statistiquesCache !== null) {
            return $this->statistiquesCache;
        }
 
        $attendus = $this->lignes()->count();
        $retrouves = $this->lignes()->retrouvees()->count();
 
        return $this->statistiquesCache = [
            'attendus' => $attendus,
            'retrouves' => $retrouves,
            'manquants' => $attendus - $retrouves,
            'deplaces' => $this->lignes()->deplacees()->count(),
            'avancement' => $attendus > 0 ? (int) round($retrouves / $attendus * 100) : 0,
        ];
    }

    /**
     * Pointe un équipement à partir du code lu par la douchette (code-barres ou n° d'inventaire).
     *
     * @return array{type: string, message: string, designation: ?string, code: string}
     *   type : ok | deplace | hors_perimetre | attention | deja | inconnu | ferme | vide
     */
    public function pointer(string $code, ?int $emplacementId, ?string $etat, int $userId): array
    {
        $code = trim($code);
        $retour = fn (string $type, string $message, ?Equipement $e = null) => [
            'type' => $type,
            'message' => $message,
            'designation' => $e?->designation,
            'code' => $code,
        ];
 
        if ($code === '') {
            return $retour('vide', 'Aucun code lu.');
        }
 
        if (! $this->est_ouvert) {
            return $retour('ferme', 'Ce récolement est clôturé : plus aucun pointage possible.');
        }
 
        $equipement = Equipement::with('emplacement')
            ->where('code_barre', $code)
            ->orWhere('numero_inventaire', $code)
            ->first();
 
        if (! $equipement) {
            return $retour('inconnu', "Code inconnu : « {$code} ».");
        }
 
        return DB::transaction(function () use ($equipement, $emplacementId, $etat, $userId, $retour) {
            $ligne = LigneInventaire::where('inventaire_id', $this->id)
                ->where('equipement_id', $equipement->id)
                ->lockForUpdate()
                ->first();
 
            if ($ligne?->scanne_le) {
                return $retour('deja', 'Déjà pointé à ' . $ligne->scanne_le->format('H:i') . '.', $equipement);
            }
 
            $horsPerimetre = $ligne === null;
            $ligne ??= new LigneInventaire(['inventaire_id' => $this->id, 'equipement_id' => $equipement->id]);
 
            $constate = $emplacementId ?? $equipement->emplacement_id;
 
            $ligne->fill([
                'emplacement_constate_id' => $constate,
                'etat_constate' => $etat ?? $equipement->etat,
                'scanne_le' => now(),
                'scanne_par' => $userId,
                'remarque' => $horsPerimetre ? 'Hors périmètre de la campagne' : $ligne->remarque,
            ])->save();
 
            if ($horsPerimetre) {
                return $retour('hors_perimetre', 'Pointé, mais cet équipement n\'était pas attendu dans ce périmètre.', $equipement);
            }
 
            if (in_array($equipement->statut, ['reforme', 'perdu'], true)) {
                return $retour('attention', 'Pointé, mais ce bien est enregistré comme « ' . Equipement::STATUTS[$equipement->statut] . ' ».', $equipement);
            }
 
            if ($constate !== $equipement->emplacement_id) {
                $prevu = $equipement->emplacement?->chemin_complet ?? 'non renseigné';
 
                return $retour('deplace', "Pointé, mais l'emplacement enregistré est : {$prevu}.", $equipement);
            }
 
            return $retour('ok', 'Pointé.', $equipement);
        });
    }

    /**
     * Clôture la campagne. Si demandé, les biens retrouvés ailleurs prennent leur emplacement constaté.
     *
     * @throws RuntimeException si déjà clôturée
     */
    public function cloturer(bool $appliquerEmplacements = false): void
    {
        DB::transaction(function () use ($appliquerEmplacements) {
            $inventaire = self::whereKey($this->getKey())->lockForUpdate()->firstOrFail();
 
            if (! $inventaire->est_ouvert) {
                throw new RuntimeException('Ce récolement est déjà clôturé.');
            }
 
            if ($appliquerEmplacements) {
                $inventaire->lignes()->deplacees()->with('equipement')->get()
                    ->each(fn (LigneInventaire $l) => $l->equipement->update(['emplacement_id' => $l->emplacement_constate_id]));
            }
 
            $inventaire->update(['statut' => 'cloture', 'date_fin' => today()]);
        });
 
        $this->refresh();
        $this->statistiquesCache = null;
    }
}