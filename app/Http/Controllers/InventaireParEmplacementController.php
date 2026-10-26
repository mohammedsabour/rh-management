<?php

namespace App\Http\Controllers;

use App\Models\Emplacement;
use App\Models\Equipement;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class InventaireParEmplacementController extends Controller
{
    // GET /inventaire/par-emplacement[?emplacement=ID]  →  PDF paysage groupé par emplacement
   public function __invoke(Request $request)
    {
        Gate::authorize('viewAny', Equipement::class);
 
        $requete = Equipement::with(['categorie', 'emplacement.parent.parent']);
        $filtre = null;
 
        // Un emplacement choisi = lui + tous ses sous-emplacements (ex. un bâtiment entier)
        if ($request->filled('emplacement')) {
            $filtre = Emplacement::findOrFail((int) $request->query('emplacement'));
            $requete->whereIn('emplacement_id', $filtre->idsAvecDescendants());
        }
 
        $groupes = $requete->orderBy('numero_inventaire')->get()
            ->groupBy(fn (Equipement $e) => $e->emplacement_id ?? 0)
            ->map(function ($equipements) {
                /** @var Equipement $premier */
                $premier = $equipements->first();
 
                return [
                    'lieu' => $premier->emplacement?->chemin_complet ?? 'Sans emplacement',
                    'lignes' => $equipements->map(fn (Equipement $e) => [
                        'code' => $e->numero_inventaire,
                        'designation' => $e->designation,
                        'etat' => mb_strtoupper(Equipement::ETATS[$e->etat] ?? (string) $e->etat),
                    ])->values(),
                ];
            })
            ->sortBy('lieu', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
 
        $pdf = Pdf::loadView('inventaire.par-emplacement', [
            'groupes' => $groupes,
            'total' => $groupes->sum(fn ($g) => $g['lignes']->count()),
            'filtre' => $filtre?->chemin_complet,
            'entite' => config('app.name'),
            'date' => now()->format('d/m/Y'),
        ])->setPaper('a4', 'landscape');
 
        return $pdf->stream('equipements-par-emplacement-' . now()->format('Y-m-d') . '.pdf');
    }
}
