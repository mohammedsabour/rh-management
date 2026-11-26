<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Commission de récolement (A ou B) : un président + deux membres, tous employés de l'application.
 * Le comptage se fait « à l'aveugle » : la commission ne voit jamais ce qui est attendu ni le travail de l'autre.
 */
class CommissionInventaire extends Model
{
    protected $table = 'commissions_inventaire';

    protected $fillable = [
        'inventaire_id', 'code', 'statut', 'debut_le', 'termine_le', 'nb_non_numerotes', 'observations',
    ];

    protected function casts(): array
    {
        return [
            'debut_le' => 'datetime',
            'termine_le' => 'datetime',
        ];
    }

    public function inventaire(): BelongsTo
    {
        return $this->belongsTo(Inventaire::class);
    }

    public function membres(): HasMany
    {
        return $this->hasMany(CommissionMembre::class, 'commission_id');
    }

    public function lignes(): HasMany
    {
        return $this->hasMany(LigneInventaire::class, 'commission_id');
    }

    public function scopePourUtilisateur(Builder $query, int $userId): Builder
    {
        return $query->whereHas('membres', fn ($q) => $q->where('user_id', $userId));
    }

    public function getEstEnCoursAttribute(): bool
    {
        return $this->statut === 'en_cours' && $this->inventaire->statut === 'comptage';
    }

    public function getEstTermineeAttribute(): bool
    {
        return $this->statut === 'terminee';
    }

    public function getLibelleAttribute(): string
    {
        return 'Commission ' . $this->code;
    }

    public function getLettreArAttribute(): string
    {
        return $this->code === 'A' ? 'أ' : 'ب';
    }

    public function estMembre(int $userId): bool
    {
        return $this->membres->contains('user_id', $userId);
    }

    public function estPresident(int $userId): bool
    {
        return $this->membres->contains(fn (CommissionMembre $m) => $m->user_id === $userId && $m->role === 'president');
    }

    /**
     * Pointage « à l'aveugle » : le retour est identique que le bien soit attendu, déplacé ou hors périmètre.
     * L'emplacement et l'état constatés sont obligatoires (on ne se rabat jamais sur les valeurs enregistrées).
     *
     * @return array{type: string, message: string, designation: ?string, code: string, ligne_id: ?int}
     *   type : ok | deja | inconnu | ferme | vide | incomplet
     */
    public function pointer(string $code, ?int $emplacementId, ?string $etat, int $userId): array
    {
        $code = trim($code);
        $retour = fn (string $type, string $message, ?Equipement $e = null, ?int $ligneId = null) => [
            'type' => $type,
            'message' => $message,
            'designation' => $e?->designation,
            'code' => $code,
            'ligne_id' => $ligneId,
        ];

        if ($code === '') {
            return $retour('vide', 'Aucun code lu.');
        }

        if (! $this->est_en_cours) {
            return $retour('ferme', 'Le comptage de cette commission est terminé.');
        }

        if (! $emplacementId || ! $etat || ! isset(Equipement::ETATS[$etat])) {
            return $retour('incomplet', "Indiquez d'abord l'emplacement où vous êtes et l'état constaté du bien.");
        }

        $equipement = Equipement::where('code_barre', $code)->orWhere('numero_inventaire', $code)->first();

        if (! $equipement) {
            return $retour('inconnu', "Code inconnu : « {$code} ».");
        }

        return DB::transaction(function () use ($equipement, $emplacementId, $etat, $userId, $retour) {
            $ligne = LigneInventaire::where('commission_id', $this->id)
                ->where('equipement_id', $equipement->id)
                ->lockForUpdate()
                ->first();

            if ($ligne?->scanne_le) {
                return $retour('deja', 'Déjà enregistré à ' . $ligne->scanne_le->format('H:i') . '.', $equipement, $ligne->id);
            }

            // Absent de la liste attendue : on l'enregistre quand même, sans le dire à la commission
            $ligne ??= new LigneInventaire([
                'inventaire_id' => $this->inventaire_id,
                'commission_id' => $this->id,
                'equipement_id' => $equipement->id,
                'hors_perimetre' => true,
            ]);

            $ligne->fill([
                'emplacement_constate_id' => $emplacementId,
                'etat_constate' => $etat,
                'scanne_le' => now(),
                'scanne_par' => $userId,
            ])->save();

            if ($this->debut_le === null) {
                $this->forceFill(['debut_le' => now()])->save();
            }

            return $retour('ok', 'Enregistré.', $equipement, $ligne->id);
        });
    }

    /** Annule un pointage fait par erreur (tant que le comptage est en cours). */
    public function annuler(int $ligneId): void
    {
        if (! $this->est_en_cours) {
            return;
        }

        $ligne = $this->lignes()->whereKey($ligneId)->first();

        if (! $ligne) {
            return;
        }

        // Un bien hors périmètre n'existe que par son scan : on supprime la ligne
        if ($ligne->hors_perimetre) {
            $ligne->delete();

            return;
        }

        $ligne->update([
            'scanne_le' => null, 'scanne_par' => null,
            'emplacement_constate_id' => null, 'etat_constate' => null,
        ]);
    }

    /** Nombre de biens enregistrés par la commission. */
    public function nombreEnregistres(): int
    {
        return $this->lignes()->whereNotNull('scanne_le')->count();
    }

    /**
     * Statistiques du comptage de CETTE commission (jamais l'emplacement ni l'état enregistrés).
     * « Retrouvés » compte tous les pointages, y compris un éventuel bien hors périmètre : ainsi rien ne
     * trahit qu'un bien n'était pas attendu (le « restant » ne descend pas sous zéro).
     *
     * @return array{a_controler: int, retrouves: int, restant: int, avancement: int, non_retrouves: int, endommages: int}
     */
    public function statistiques(): array
    {
        $lignes = $this->lignes()->get(['hors_perimetre', 'scanne_le', 'etat_constate']);

        $attendus = $lignes->where('hors_perimetre', false);
        $pointees = $lignes->whereNotNull('scanne_le');

        $aControler = $attendus->count();
        $retrouves = $pointees->count();

        return [
            'a_controler' => $aControler,
            'retrouves' => $retrouves,
            'restant' => max(0, $aControler - $retrouves),
            'avancement' => $aControler > 0 ? min(100, (int) round($retrouves / $aControler * 100)) : 0,
            'non_retrouves' => $attendus->whereNull('scanne_le')->count(),
            'endommages' => $pointees->whereIn('etat_constate', ['degrade', 'hors_service'])->count(),
        ];
    }

    /**
     * Le président déclare le comptage terminé. Quand les deux commissions ont terminé,
     * le récolement passe automatiquement en phase de rapprochement.
     */
    public function terminer(array $data): void
    {
        DB::transaction(function () use ($data) {
            $commission = self::whereKey($this->getKey())->lockForUpdate()->firstOrFail();

            if ($commission->statut !== 'en_cours') {
                throw new RuntimeException('Le comptage de cette commission est déjà terminé.');
            }

            $commission->update([
                'statut' => 'terminee',
                'termine_le' => now(),
                'nb_non_numerotes' => $data['nb_non_numerotes'] ?? null,
                'observations' => $data['observations'] ?? null,
            ]);

            $commission->inventaire->verifierFinComptage();
        });

        $this->refresh();
    }
}