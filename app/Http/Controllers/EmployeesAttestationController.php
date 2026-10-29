<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\Algerie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

/**
 * Attestation de travail (en arabe, selon le modèle de la Chambre).
 * mPDF est utilisé à la place de dompdf : dompdf ne sait ni relier les lettres arabes ni écrire de droite à gauche.
 *
 * GET /employees/{user}/attestation[?numero=02]
 */
class EmployeesAttestationController extends Controller
{
    public function __invoke(Request $request, User $user)
    {
        // Même droit que la consultation de la fiche employé
        Gate::authorize('view', $user);

        // Une attestation de travail ne se délivre qu'à un employé en poste
        abort_unless($user->is_active, 403, "Cet employé n'est plus en poste.");

        $html = view('employees.attestation', [
            'user' => $user,
            'feminin' => $user->sexe === 'feminin',
            'nomComplet' => trim(($user->nom_ar ?: $user->nom) . ' ' . ($user->prenom_ar ?: $user->prenom)),
            'commune' => Algerie::communeAr($user->wilaya_naissance, $user->commune_naissance) ?? $user->commune_naissance,
            'wilaya' => Algerie::wilayaAr($user->wilaya_naissance) ?? $user->wilaya_naissance,
            'numero' => $request->query('numero'),   // numéro d'ordre saisi par la RH (facultatif)
            'annee' => now()->year,
            'date' => now()->format('d/m/Y'),
            'entete' => public_path('images/attestation/entete.jpeg'),
            'pied' => public_path('images/attestation/pied.jpeg'),
        ])->render();

        $dossier = storage_path('app/mpdf');
        if (! is_dir($dossier)) {
            mkdir($dossier, 0775, true);
        }

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P',
            'margin_top' => 8,
            'margin_left' => 20,
            'margin_right' => 20,
            'margin_bottom' => 32,
            'margin_footer' => 6,
            'default_font' => 'dejavusans',
            'tempDir' => $dossier,
        ]);

        $mpdf->SetDirectionality('rtl');
        $mpdf->autoScriptToLang = true;
        $mpdf->autoLangToFont = true;
        $mpdf->SetTitle('Attestation de travail');
        $mpdf->WriteHTML($html);

        return response($mpdf->Output('', Destination::STRING_RETURN), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="attestation-travail-' . $user->id . '.pdf"',
        ]);
    }
}