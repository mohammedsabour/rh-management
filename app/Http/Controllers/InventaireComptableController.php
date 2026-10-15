<?php

namespace App\Http\Controllers;

use App\Models\Equipement;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class InventaireComptableController extends Controller
{
    public function __invoke()
    {
        abort_unless(in_array(Auth::user()?->role, ['admin', 'rh'], true), 403);

        $equipements = Equipement::with(['categorie', 'emplacement'])
            ->orderBy('date_acquisition')
            ->orderBy('categorie_id')
            ->orderBy('id')
            ->get();

        // Regroupement par exercice (année d'acquisition) ; les biens sans date sont à la fin
        $exercices = $equipements
            ->groupBy(fn (Equipement $e) => $e->date_acquisition?->year ?? 0)
            ->sortKeysUsing(fn ($a, $b) => ($a ?: PHP_INT_MAX) <=> ($b ?: PHP_INT_MAX))
            ->map(function ($groupe, $annee) {
                $lignes = $groupe->map(fn (Equipement $e) => [
                    // Code article : n° d'inventaire (obligatoire)
                    'code' => $e->numero_inventaire,
                    'compte' => $e->categorie?->numero_compte ?? '—',
                    'designation' => $e->designation,
                    'date' => $e->date_acquisition,
                    'valeur' => $e->prix_acquisition !== null ? (float) $e->prix_acquisition : null,
                    'etat' => Equipement::ETATS[$e->etat] ?? $e->etat,
                    'lieu' => $this->lieu($e),
                ])->values();

                return [
                    'annee' => $annee,
                    'lignes' => $lignes,
                    'valeur' => $lignes->sum('valeur'),
                ];
            })
            ->values();

        $pdf = Pdf::loadView('inventaire.comptable', [
            'exercices' => $exercices,
            'valeurTotale' => $exercices->sum('valeur'),
            'entite' => config('app.name'),
            'periode' => ucfirst(now()->locale('fr')->translatedFormat('F Y')),
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('inventaire-comptable-' . now()->format('Y-m-d') . '.pdf');
    }

    // Lieu d'affectation ; pour un bien sorti du patrimoine on affiche son statut (ex. « Réformé »)
    private function lieu(Equipement $e): string
    {
        if (in_array($e->statut, ['reforme', 'perdu'], true)) {
            return Equipement::STATUTS[$e->statut];
        }

        return $e->emplacement?->nom ?? '—';
    }
}