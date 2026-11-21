<?php

namespace App\Http\Controllers;

use App\Models\Inventaire;
use App\Models\RapprochementLigne;
use App\Support\PvRecolementPdf;
use Illuminate\Support\Facades\Gate;

/**
 * « المحضر النهائي الموحد للجرد ومطابقة نتائج اللجنتين »
 * GET /recolement/{inventaire}/pv-final   (admin ou créateur du récolement).
 * Récolement clôturé : PV officiel. En phase de rapprochement : aperçu avec filigrane « brouillon ».
 */
class PvFinalController extends Controller
{
    public function __invoke(Inventaire $inventaire)
    {
        Gate::authorize('rapprocher', $inventaire);
        abort_if($inventaire->en_comptage, 422, 'Les deux commissions n\'ont pas encore terminé leur comptage.');

        $inventaire->load('commissions.membres.user');

        $etatsAr = config('recolement.etats_ar');
        $concordanceAr = config('recolement.concordance_ar');

        $resultat = fn (bool $trouve, $empl, ?string $etat) => $trouve
            ? trim(($empl?->chemin_complet ?? '') . ' – ' . ($etatsAr[$etat] ?? ''), ' –')
            : 'غير موجود';

        $lignes = $inventaire->rapprochements()
            ->with(['equipement', 'aEmplacement.parent.parent', 'bEmplacement.parent.parent'])
            ->get()
            ->sortBy(fn (RapprochementLigne $l) => $l->equipement->numero_inventaire, SORT_NATURAL)
            ->values();

        $rows = $lignes->map(function (RapprochementLigne $l, int $i) use ($resultat, $concordanceAr) {
            return [
                'n' => str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT),
                'designation' => $l->equipement->designation,
                'inventaire' => $l->equipement->numero_inventaire,
                'a' => $resultat($l->a_trouve, $l->aEmplacement, $l->a_etat),
                'b' => $resultat($l->b_trouve, $l->bEmplacement, $l->b_etat),
                'concordance' => $concordanceAr[$l->concordance] ?? '',
            ];
        });

        $membres = fn (string $code) => $inventaire->commission($code)->membres
            ->sortBy(fn ($m) => $m->role === 'president' ? 0 : 1)
            ->map(fn ($m) => PvRecolementPdf::nomAr($m->user))
            ->values()->all();

        $a = $inventaire->commission('A');
        $b = $inventaire->commission('B');
        $reunion = collect([$a->termine_le, $b->termine_le])->filter()->max();

        return PvRecolementPdf::rendre('recolement.pv-final', [
            'inventaire' => $inventaire,
            'membresA' => $membres('A'),
            'membresB' => $membres('B'),
            'rows' => $rows,
            'bilan' => $inventaire->bilan(),
            'dateReunion' => PvRecolementPdf::date($reunion),
            'heure' => $reunion?->format('H:i') ?? '……',
            'dateDecision' => PvRecolementPdf::date($inventaire->decision_date),
            'dateRedaction' => PvRecolementPdf::date($inventaire->date_fin ?? $reunion),
            'directeur' => config('recolement.directeur'),
            'chambre' => config('recolement.chambre'),
        ], $inventaire->est_cloture ? 'pv-final-recolement.pdf' : 'apercu-pv-final-recolement.pdf',
            'المحضر النهائي الموحد للجرد',
            $inventaire->est_cloture ? null : 'مسودة - غير معتمد');
    }
}