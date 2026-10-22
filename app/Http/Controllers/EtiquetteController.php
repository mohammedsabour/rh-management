<?php

namespace App\Http\Controllers;

use App\Models\Equipement;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use Picqer\Barcode\BarcodeGeneratorSVG;

class EtiquetteController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Equipement::class);

        $ids = collect(explode(',', (string) $request->query('ids')))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique();
 
        abort_if($ids->isEmpty(), 404);
 
        $equipements = Equipement::whereIn('id', $ids)
            ->whereNotNull('code_barre')
            ->orderBy('id')
            ->get();
 
        $generator = new BarcodeGeneratorSVG();
 
        $etiquettes = $equipements->map(fn (Equipement $e) => [
            'code'  => $e->code_barre,
            'image' => 'data:image/svg+xml;base64,' . base64_encode(
                $generator->getBarcode($e->code_barre, $generator::TYPE_CODE_128, 2, 60)
            ),
        ]);
 
        return view('inventaire.etiquettes', [
            'etiquettes' => $etiquettes,
            'societe'    => config('app.name'),
            'format'     => $request->query('format') === 'a4' ? 'a4' : 'rouleau',
        ]);
    }
}
