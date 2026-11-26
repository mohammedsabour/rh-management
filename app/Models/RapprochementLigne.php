<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Comparaison A / B pour un équipement + décision de la direction.
 */
class RapprochementLigne extends Model
{
    protected $table = 'rapprochement_lignes';

    public const CONCORDANCES = [
        'concordant' => 'Concordant',
        'ecart_emplacement' => 'Emplacements différents',
        'ecart_etat' => 'États différents',
        'ecart_les_deux' => 'Emplacement et état différents',
        'un_seul' => 'Trouvé par une seule commission',
        'introuvable' => 'Introuvable (A et B)',
    ];

    public const DECISIONS = [
        'retrouve' => 'Retrouvé',
        'non_retrouve' => 'Non retrouvé',
    ];

    protected $fillable = [
        'inventaire_id', 'equipement_id', 'attendu',
        'a_trouve', 'a_emplacement_id', 'a_etat',
        'b_trouve', 'b_emplacement_id', 'b_etat',
        'concordance', 'decision', 'emplacement_final_id', 'etat_final', 'action', 'decide_par',
    ];

    protected function casts(): array
    {
        return [
            'attendu' => 'boolean',
            'a_trouve' => 'boolean',
            'b_trouve' => 'boolean',
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

    public function aEmplacement(): BelongsTo
    {
        return $this->belongsTo(Emplacement::class, 'a_emplacement_id');
    }

    public function bEmplacement(): BelongsTo
    {
        return $this->belongsTo(Emplacement::class, 'b_emplacement_id');
    }

    public function emplacementFinal(): BelongsTo
    {
        return $this->belongsTo(Emplacement::class, 'emplacement_final_id');
    }

    public function getATrancherAttribute(): bool
    {
        return $this->decision === null;
    }

    /** Concordance calculée à partir des deux constats. */
    public static function calculerConcordance(bool $aTrouve, ?int $aEmpl, ?string $aEtat, bool $bTrouve, ?int $bEmpl, ?string $bEtat): string
    {
        if (! $aTrouve && ! $bTrouve) {
            return 'introuvable';
        }

        if ($aTrouve xor $bTrouve) {
            return 'un_seul';
        }

        $emplDiff = $aEmpl !== $bEmpl;
        $etatDiff = $aEtat !== $bEtat;

        return match (true) {
            $emplDiff && $etatDiff => 'ecart_les_deux',
            $emplDiff => 'ecart_emplacement',
            $etatDiff => 'ecart_etat',
            default => 'concordant',
        };
    }
}
