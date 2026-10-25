<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DemandeConge extends Model
{
    use HasFactory;

    public const TYPES = [
        'annuel' => 'Congé annuel',
        'maladie' => 'Congé maladie',
        'sans_solde' => 'Congé sans solde',
        'autre' => 'Autre',
    ];

    public const STATUTS = [
        'en_attente' => 'En attente',
        'approuve' => 'Approuvé',
        'refuse' => 'Refusé',
        'annule' => 'Annulé',
    ];

    /** Types qui consomment le solde de congés de l'employé. */
    public const TYPES_DECOMPTES = ['annuel'];

    /** Jours de week-end en Algérie : vendredi (5) et samedi (6). */
    public const JOURS_WEEKEND = [Carbon::FRIDAY, Carbon::SATURDAY];

    /** Nom réel de la colonne « solde » dans la table users. */
    public const COLONNE_SOLDE = 'joures_conges_restant';

    protected $fillable = [
        'employe_id', 
        'type',
        'date_debut', 
        'date_fin', 
        'nombre_jours',
        'commentaire',
        'statut',
        'traite_par',
        'date_traitement',
        'motif_refus',
    ];

    protected function casts(): array
    {
        return [
            'date_debut' => 'date',
            'date_fin' => 'date',
            'date_traitement' => 'datetime',
            'nombre_jours' => 'integer',
        ];
    }

    // ------------------------------------------------------------------ relations

    public function employe(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employe_id');
    }

    public function traitePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'traite_par');
    }

    // ------------------------------------------------------------------ outils

    /** Nombre de jours ouvrés entre deux dates incluses (vendredi et samedi exclus). */
    public static function compterJoursOuvres(Carbon|string $debut, Carbon|string $fin): int
    {
        $debut = Carbon::parse($debut)->startOfDay();
        $fin = Carbon::parse($fin)->startOfDay();

        $jours = 0;
        foreach (CarbonPeriod::create($debut, $fin) as $jour) {
            if (! in_array($jour->dayOfWeek, self::JOURS_WEEKEND, true)) {
                $jours++;
            }
        }

        return $jours;
    }

    public function decompteLeSolde(): bool
    {
        return in_array($this->type, self::TYPES_DECOMPTES, true);
    }

    public function estEnAttente(): bool
    {
        return $this->statut === 'en_attente';
    }

    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'en_attente');
    }

    // ------------------------------------------------------------------ décisions RH

    /**
     * Approuve la demande. Tout se passe dans une transaction avec verrou, pour qu'un double clic
     * ou deux RH simultanés ne décomptent pas le solde deux fois.
     *
     * @throws RuntimeException si la demande est déjà traitée ou si le solde est insuffisant
     */
    public function approuver(User $rh): void
    {
        DB::transaction(function () use ($rh) {
            $demande = self::whereKey($this->getKey())->lockForUpdate()->firstOrFail();

            if (! $demande->estEnAttente()) {
                throw new RuntimeException('Cette demande a déjà été traitée.');
            }

            if ($demande->decompteLeSolde()) {
                $employe = User::whereKey($demande->employe_id)->lockForUpdate()->firstOrFail();
                $solde = (int) $employe->getAttribute(self::COLONNE_SOLDE);

                if ($solde < $demande->nombre_jours) {
                    throw new RuntimeException(
                        "Solde insuffisant : {$solde} jour(s) restant(s) pour {$demande->nombre_jours} demandé(s)."
                    );
                }

                $employe->decrement(self::COLONNE_SOLDE, $demande->nombre_jours);
            }

            $demande->update([
                'statut' => 'approuve',
                'traite_par' => $rh->id,
                'date_traitement' => now(),
                'motif_refus' => null,
            ]);
        });

        $this->refresh();
    }

    /** @throws RuntimeException si la demande est déjà traitée */
    public function refuser(User $rh, string $motif): void
    {
        DB::transaction(function () use ($rh, $motif) {
            $demande = self::whereKey($this->getKey())->lockForUpdate()->firstOrFail();

            if (! $demande->estEnAttente()) {
                throw new RuntimeException('Cette demande a déjà été traitée.');
            }

            $demande->update([
                'statut' => 'refuse',
                'traite_par' => $rh->id,
                'date_traitement' => now(),
                'motif_refus' => $motif,
            ]);
        });

        $this->refresh();
    }
}