<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Document extends Model
{
    use HasFactory;

    // Disque privé : photos et pièces du dossier ne sont jamais accessibles par une URL publique
    public const DISK = 'local';

    // Dossier tmp
    public const DOSSIER_TEMPORAIRE = 'employes/tmp';

    public const TYPES = [
        'piece_identite' => "Pièce d'identité",
        'extrait_naissance' => 'Extrait de naissance',
        'certificat_residence' => 'Certificat de résidence',
        'fiche_familiale' => 'Fiche familiale',
        'contrat_travail' => 'Contrat de travail',
        'diplome' => 'Diplôme',
        'justificatif_retraite' => 'Justificatif de retraite',
        'acceptation_demission' => 'Acceptation de démission',
    ];

    protected $fillable = ['employe_id', 'type', 'file_path', 'file_name', 'file_size', 'file_extension'];

    protected static function booted(): void
    {
        static::saving(function (Document $document) {
            if (! $document->file_path || ! $document->isDirty('file_path')) {
                return;
            }

            $disk = Storage::disk(self::DISK);

            // Fichier remplacé : l'ancien est supprimé pour ne pas laisser d'orphelin
            $ancien = $document->getOriginal('file_path');
            if ($document->exists && $ancien && $ancien !== $document->file_path && $disk->exists($ancien)) {
                $disk->delete($ancien);
            }

            // Range le fichier : employes/{id}/{type}.ext
            $final = $document->cheminFinal();
            if ($final !== $document->file_path && $disk->exists($document->file_path)) {
                $disk->move($document->file_path, $final);
                $document->file_path = $final;
            }

            // Nom, extension et taille renseignés automatiquement
            $document->file_extension = pathinfo($document->file_path, PATHINFO_EXTENSION);
            $document->file_size = $disk->exists($document->file_path) ? $disk->size($document->file_path) : null;
            $document->file_name ??= basename($document->file_path);
        });
    }

    /** Chemin définitif : employes/{id}/<type>.<ext> (« -2 », « -3 »… si le même type existe déjà). */
    public function cheminFinal(): string
    {
        $dossier = 'employes/' . $this->employe_id;
        $base = Str::slug(self::TYPES[$this->type] ?? $this->type);   // « certificat-de-residence »
        $extension = pathinfo($this->file_path, PATHINFO_EXTENSION);
        $disk = Storage::disk(self::DISK);

        $nom = "{$base}.{$extension}";
        $numero = 2;
        while ($disk->exists("{$dossier}/{$nom}") && "{$dossier}/{$nom}" !== $this->file_path) {
            $nom = "{$base}-{$numero}.{$extension}";
            $numero++;
        }

        return "{$dossier}/{$nom}";
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'employe_id');
    }
}