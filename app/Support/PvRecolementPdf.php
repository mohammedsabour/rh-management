<?php

namespace App\Support;

use App\Models\User;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

/** Rendu mPDF (arabe, de droite à gauche) des procès-verbaux de récolement. */
class PvRecolementPdf
{
    public static function nomAr(?User $u): string
    {
        return $u ? trim(($u->nom_ar ?: $u->nom) . ' ' . ($u->prenom_ar ?: $u->prenom)) : '';
    }

    public static function date(?\DateTimeInterface $d): string
    {
        return $d ? $d->format('Y/m/d') : '……/……/……';
    }

    public static function rendre(string $vue, array $data, string $fichier, string $titre, ?string $filigrane = null)
    {
        $html = view($vue, $data)->render();

        $dossier = storage_path('app/mpdf');
        if (! is_dir($dossier)) {
            mkdir($dossier, 0775, true);
        }

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P',
            'margin_top' => 12,
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_bottom' => 15,
            'default_font' => 'dejavusans',
            'tempDir' => $dossier,
        ]);

        $mpdf->SetDirectionality('rtl');
        $mpdf->autoScriptToLang = true;
        $mpdf->autoLangToFont = true;
        $mpdf->SetTitle($titre);
        if ($filigrane) {
            $mpdf->SetWatermarkText($filigrane, 0.12);
            $mpdf->showWatermarkText = true;
        }

        $mpdf->WriteHTML($html);

        return response($mpdf->Output('', Destination::STRING_RETURN), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $fichier . '"',
        ]);
    }
}