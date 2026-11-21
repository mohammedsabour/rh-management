<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Récolement parent : périmètre + deux commissions indépendantes (A et B) + rapprochement par la direction.
 *
 *   comptage       → les deux commissions comptent à l'aveugle
 *   rapprochement  → les deux ont terminé ; la direction compare et tranche
 *   cloture        → résultat final appliqué, PV final établi
 */
class Inventaire extends Model
{
    protected $table = 'inventaires';

    public const STATUTS = [
        'comptage' => 'Comptage en cours',
        'rapprochement' => 'Rapprochement',
        'cloture' => 'Clôturé',
    ];

    public const RESULTATS = [
        'total' => 'Concordance totale entre les deux commissions',
        'limite' => 'Écarts limités, revus, réglés et documentés',
        'substantiel' => 'Écarts substantiels : à soumettre au directeur (troisième commission)',
    ];

    public const VISAS = [
        'adopte' => 'Adopter les résultats',
        'complement' => 'Demander un complément / une vérification',
        'troisieme' => 'Former une troisième commission',
    ];

    protected $fillable = [
        'nom', 'date_debut', 'date_fin', 'statut', 'emplacement_id', 'cree_par', 'notes',
        'decision_numero', 'decision_date', 'resultat_rapprochement', 'details_rapprochement',
        'visa_directeur', 'observations_directeur',
    ];

    protected function casts(): array
    {
        return [
            'date_debut' => 'date',
            'date_fin' => 'date',
            'decision_date' => 'date',
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

    public function commissions(): HasMany
    {
        return $this->hasMany(CommissionInventaire::class)->orderBy('code');
    }

    public function lignes(): HasMany
    {
        return $this->hasMany(LigneInventaire::class);
    }

    public function rapprochements(): HasMany
    {
        return $this->hasMany(RapprochementLigne::class);
    }

    public function commission(string $code): ?CommissionInventaire
    {
        return $this->commissions->firstWhere('code', $code);
    }

    public function getEnComptageAttribute(): bool
    {
        return $this->statut === 'comptage';
    }

    public function getEnRapprochementAttribute(): bool
    {
        return $this->statut === 'rapprochement';
    }

    public function getEstClotureAttribute(): bool
    {
        return $this->statut === 'cloture';
    }

    /** L'utilisateur siège-t-il dans l'une des deux commissions ? (il ne doit alors rien voir du travail de l'autre) */
    public function aPourMembre(int $userId): bool
    {
        return CommissionMembre::where('user_id', $userId)
            ->whereHas('commission', fn ($q) => $q->where('inventaire_id', $this->id))
            ->exists();
    }

    // Équipements attendus : ceux du périmètre (emplacement + sous-emplacements), ou tous si pas de périmètre.
    // Les biens réformés ou perdus sont exclus du contrôle.
    public function equipementsAttendus()
    {
        return Equipement::query()
            ->whereNotIn('statut', ['reforme', 'perdu'])
            ->when($this->emplacement_id, fn ($q) => $q->whereIn('emplacement_id', $this->emplacement->idsAvecDescendants()));
    }

    /**
     * Crée, pour CHAQUE commission, une ligne par équipement attendu (même liste pour A et B),
     * avec la photo du patrimoine à cet instant. Retourne le nombre d'équipements.
     */
    public function genererLignes(): int
    {
        $maintenant = now();
        $equipements = $this->equipementsAttendus()->get(['id', 'emplacement_id', 'etat']);

        foreach ($this->commissions as $commission) {
            foreach ($equipements->chunk(500) as $groupe) {
                LigneInventaire::insert($groupe->map(fn ($e) => [
                    'inventaire_id' => $this->id,
                    'commission_id' => $commission->id,
                    'equipement_id' => $e->id,
                    'emplacement_attendu_id' => $e->emplacement_id,
                    'etat_attendu' => $e->etat,
                    'hors_perimetre' => false,
                    'created_at' => $maintenant,
                    'updated_at' => $maintenant,
                ])->all());
            }
        }

        return $equipements->count();
    }

    /** Appelée quand une commission termine : si les deux ont fini, on passe au rapprochement. */
    public function verifierFinComptage(): void
    {
        DB::transaction(function () {
            $inventaire = self::whereKey($this->getKey())->lockForUpdate()->firstOrFail();

            if ($inventaire->statut !== 'comptage') {
                return;
            }

            $terminees = $inventaire->commissions()->where('statut', 'terminee')->count();

            if ($terminees < 2) {
                return;
            }

            $inventaire->genererRapprochement();
            $inventaire->update(['statut' => 'rapprochement']);
        });

        $this->refresh();
    }

    /**
     * Fige la comparaison A / B, équipement par équipement.
     * Décisions automatiques : concordant → retrouvé tel que constaté ; introuvable des deux côtés → non retrouvé.
     * Tout le reste est laissé à la direction.
     */
    public function genererRapprochement(): void
    {
        $this->rapprochements()->delete();

        $codes = $this->commissions()->pluck('id', 'code');
        $lignes = $this->lignes()->get()->groupBy('equipement_id');
        $maintenant = now();

        $rows = [];

        foreach ($lignes as $equipementId => $paire) {
            $a = $paire->firstWhere('commission_id', $codes['A']);
            $b = $paire->firstWhere('commission_id', $codes['B']);

            $aTrouve = (bool) $a?->scanne_le;
            $bTrouve = (bool) $b?->scanne_le;
            $attendu = $paire->contains(fn (LigneInventaire $l) => ! $l->hors_perimetre);

            $concordance = RapprochementLigne::calculerConcordance(
                $aTrouve, $a?->emplacement_constate_id, $a?->etat_constate,
                $bTrouve, $b?->emplacement_constate_id, $b?->etat_constate,
            );

            $decision = match ($concordance) {
                'concordant' => 'retrouve',
                'introuvable' => 'non_retrouve',
                default => null,
            };

            $rows[] = [
                'inventaire_id' => $this->id,
                'equipement_id' => $equipementId,
                'attendu' => $attendu,
                'a_trouve' => $aTrouve,
                'a_emplacement_id' => $a?->emplacement_constate_id,
                'a_etat' => $a?->etat_constate,
                'b_trouve' => $bTrouve,
                'b_emplacement_id' => $b?->emplacement_constate_id,
                'b_etat' => $b?->etat_constate,
                'concordance' => $concordance,
                'decision' => $decision,
                'emplacement_final_id' => $decision === 'retrouve' ? $a->emplacement_constate_id : null,
                'etat_final' => $decision === 'retrouve' ? $a->etat_constate : null,
                'created_at' => $maintenant,
                'updated_at' => $maintenant,
            ];
        }

        foreach (array_chunk($rows, 500) as $groupe) {
            RapprochementLigne::insert($groupe);
        }
    }

    /** Équipements pour lesquels les deux commissions ne sont pas d'accord (hors « introuvable des deux côtés »). */
    public function nombreEcarts(): int
    {
        return $this->rapprochements()->whereNotIn('concordance', ['concordant', 'introuvable'])->count();
    }

    /**
     * Tableau « الحصيلة الإجمالية » : colonnes A, B et « après rapprochement » (résultat consolidé :
     * un bien est retrouvé s'il l'a été par au moins une commission).
     *
     * @return array<string, array{a: int, b: int, final: int}>
     */
    public function bilan(): array
    {
        $r = $this->rapprochements()->get();
        $abime = fn (bool $trouve, ?string $etat) => $trouve && in_array($etat, ['degrade', 'hors_service'], true);
        $trouveUn = fn ($l) => $l->a_trouve || $l->b_trouve;

        $ecarts = $r->filter(fn ($l) => ! in_array($l->concordance, ['concordant', 'introuvable'], true))->count();
        $concordants = $r->where('concordance', 'concordant')->count();

        return [
            'inspectes' => [
                'a' => $r->where('a_trouve', true)->count(),
                'b' => $r->where('b_trouve', true)->count(),
                'final' => $r->filter($trouveUn)->count(),
            ],
            'concordants' => ['a' => $concordants, 'b' => $concordants, 'final' => $concordants],
            'ecarts' => ['a' => $ecarts, 'b' => $ecarts, 'final' => $ecarts],
            'introuvables' => [
                'a' => $r->where('attendu', true)->where('a_trouve', false)->count(),
                'b' => $r->where('attendu', true)->where('b_trouve', false)->count(),
                'final' => $r->where('attendu', true)->reject($trouveUn)->count(),
            ],
            'abimes' => [
                'a' => $r->filter(fn ($l) => $abime($l->a_trouve, $l->a_etat))->count(),
                'b' => $r->filter(fn ($l) => $abime($l->b_trouve, $l->b_etat))->count(),
                'final' => $r->filter(fn ($l) => $abime($l->a_trouve, $l->a_etat) || $abime($l->b_trouve, $l->b_etat))->count(),
            ],
            'non_enregistres' => [
                'a' => $r->where('attendu', false)->where('a_trouve', true)->count(),
                'b' => $r->where('attendu', false)->where('b_trouve', true)->count(),
                'final' => $r->where('attendu', false)->filter($trouveUn)->count(),
            ],
        ];
    }

    /**
     * Clôture : décision globale (résultat + détail des écarts). Si demandé, les biens constatés à l'identique par les
     * deux commissions prennent l'emplacement et l'état constatés ; les autres (écarts, non retrouvés) ne sont JAMAIS
     * modifiés automatiquement (réformé / perdu = décision humaine). Le visa du directeur se fait sur le PV papier.
     *
     * @throws RuntimeException
     */
    public function cloturer(array $data, bool $appliquer): void
    {
        DB::transaction(function () use ($data, $appliquer) {
            $inventaire = self::whereKey($this->getKey())->lockForUpdate()->firstOrFail();

            if ($inventaire->statut !== 'rapprochement') {
                throw new RuntimeException('Ce récolement n\'est pas en phase de rapprochement.');
            }

            if ($appliquer) {
                $inventaire->rapprochements()->where('decision', 'retrouve')->with('equipement')->get()
                    ->each(function (RapprochementLigne $l) {
                        $l->equipement->update(array_filter([
                            'emplacement_id' => $l->emplacement_final_id,
                            'etat' => $l->etat_final,
                        ]));
                    });
            }

            $inventaire->update([
                'statut' => 'cloture',
                'date_fin' => today(),
                'resultat_rapprochement' => $data['resultat_rapprochement'] ?? null,
                'details_rapprochement' => $data['details_rapprochement'] ?? null,
            ]);
        });

        $this->refresh();
    }
}