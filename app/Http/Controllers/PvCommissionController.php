<?php

namespace App\Http\Controllers;

use App\Models\CommissionInventaire;
use App\Models\Equipement;
use App\Models\LigneInventaire;
use App\Support\PvRecolementPdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

/**
 * « محضر الجرد المادي الأولي – اللجنة (أ / ب) »
 * GET /recolement/commissions/{commission}/pv
 *
 * Accès : membre de la commission (une fois le comptage terminé), admin, ou rapprocheur (une fois les deux terminées).
 */
class PvCommissionController extends Controller
{
    public function __invoke(CommissionInventaire $commission)
    {
        $user = Auth::user();
        $inventaire = $commission->inventaire;

        $autorise = $user->hasRole('admin')
            || ($commission->estPresident($user->id) && $commission->est_terminee)
            || (! $inventaire->en_comptage && Gate::allows('rapprocher', $inventaire));

        abort_unless($autorise, 403);

        $commission->load(['membres.user']);

        $lignes = $commission->lignes()
            ->with(['equipement.categorie', 'emplacementConstate.parent.parent'])
            ->get()
            ->sortBy(fn (LigneInventaire $l) => $l->equipement->numero_inventaire, SORT_NATURAL)
            ->values();

        $etatsAr = config('recolement.etats_ar');
        $abime = fn (?string $e) => in_array($e, ['degrade', 'hors_service'], true);

        $rows = $lignes->map(fn (LigneInventaire $l, int $i) => [
            'n' => str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT),
            'designation' => $l->equipement->designation,
            'inventaire' => $l->equipement->numero_inventaire,
            'quantite' => 1,
            'etat' => $l->retrouve ? ($etatsAr[$l->etat_constate] ?? Equipement::ETATS[$l->etat_constate] ?? '') : 'غير موجود',
            'lieu' => $l->retrouve ? ($l->emplacementConstate?->chemin_complet ?? '') : '—',
            'remarque' => $l->hors_perimetre ? 'خارج المحيط المحدد' : '',
        ]);

        $retrouvees = $lignes->filter->retrouve;

        $membres = $commission->membres
            ->sortBy(fn ($m) => $m->role === 'president' ? 0 : 1)
            ->map(fn ($m) => PvRecolementPdf::nomAr($m->user))
            ->values();

        return PvRecolementPdf::rendre('recolement.pv-commission', [
            'commission' => $commission,
            'inventaire' => $inventaire,
            'membres' => $membres,
            'rows' => $rows,
            'resume' => [
                'inspectes' => $retrouvees->count(),
                'introuvables' => $lignes->where('hors_perimetre', false)->filter(fn ($l) => ! $l->retrouve)->count(),
                'non_numerotes' => $commission->nb_non_numerotes,
                'abimes' => $retrouvees->filter(fn ($l) => $abime($l->etat_constate))->count(),
            ],
            'dateReunion' => PvRecolementPdf::date($commission->debut_le),
            'heure' => $commission->debut_le?->format('H:i') ?? '……',
            'dateDecision' => PvRecolementPdf::date($inventaire->decision_date),
            'dateRedaction' => PvRecolementPdf::date($commission->termine_le),
            'chambre' => config('recolement.chambre'),
        ], 'pv-commission-' . strtolower($commission->code) . '.pdf', 'محضر الجرد المادي الأولي');
    }
}