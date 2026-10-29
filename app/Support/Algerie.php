<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Wilayas (69) et communes d'Algérie, noms latins + arabes, lus depuis database/data/algeria-communes.json.
 *
 * En base on stocke le NOM LATIN (lisible partout : tableau, fiche, exports) ;
 * le nom arabe se retrouve par ce nom, sans tenir compte des accents ni de la casse
 * (« Béjaïa » et « Bejaia » donnent le même résultat).
 */
class Algerie
{
    private static ?array $lignes = null;

    private static function lignes(): array
    {
        return self::$lignes ??= array_map(fn (array $r) => [
            'code' => trim($r['wilaya_code']),
            'wilaya' => trim($r['wilaya_name_ascii']),
            'wilaya_ar' => trim($r['wilaya_name']),
            'commune' => trim($r['commune_name_ascii']),
            'commune_ar' => trim($r['commune_name']),
        ], json_decode(file_get_contents(database_path('data/algeria-communes.json')), true, 512, JSON_THROW_ON_ERROR));
    }

    private static function cle(?string $texte): string
    {
        return preg_replace('/[^a-z0-9]/', '', strtolower(Str::ascii((string) $texte)));
    }

    /** Options pour un Select : [nom latin => « 27 - Mostaganem »], triées par code. */
    public static function wilayas(): array
    {
        $liste = [];
        foreach (self::lignes() as $r) {
            $liste[$r['wilaya']] = $r['code'] . ' - ' . $r['wilaya'];
        }
        asort($liste);

        return $liste;
    }

    /** Communes d'une wilaya : [nom latin => nom latin], triées par nom. Vide si aucune wilaya. */
    public static function communes(?string $wilaya): array
    {
        if (! $wilaya) {
            return [];
        }

        $cle = self::cle($wilaya);
        $liste = [];
        foreach (self::lignes() as $r) {
            if (self::cle($r['wilaya']) === $cle) {
                $liste[$r['commune']] = $r['commune'];
            }
        }
        ksort($liste);

        return $liste;
    }

    public static function wilayaAr(?string $wilaya): ?string
    {
        $cle = self::cle($wilaya);
        foreach (self::lignes() as $r) {
            if ($cle !== '' && self::cle($r['wilaya']) === $cle) {
                return $r['wilaya_ar'];
            }
        }

        return null;
    }

    public static function communeAr(?string $wilaya, ?string $commune): ?string
    {
        $w = self::cle($wilaya);
        $c = self::cle($commune);
        foreach (self::lignes() as $r) {
            if ($c !== '' && self::cle($r['wilaya']) === $w && self::cle($r['commune']) === $c) {
                return $r['commune_ar'];
            }
        }

        return null;
    }
}
