<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Document extends Model
{
    use HasFactory;

    public const DISK = 'local';
 
    public const TYPES = [
        'piece_identite' => "Pièce d'identité",
        'extrait_naissance' => 'Extrait de naissance',
        'certificat_residence' => 'Certificat de résidence',
        'fiche_familiale' => 'Fiche familiale',
        'contrat_travail' => 'Contrat de travail',
        'diplome' => 'Diplôme',
    ];
    protected $fillable = ['employe_id','type', 'file_path','file_name', 'file_size', 'file_extension'];

    protected static function booted(): void
    {
        // Renseigne automatiquement nom, extension et taille à partir du fichier envoyé
        static::saving(function (Document $document) {
            if ($document->file_path && $document->isDirty('file_path')) {
                $disk = Storage::disk(self::DISK);
 
                $document->file_extension = pathinfo($document->file_path, PATHINFO_EXTENSION);
                $document->file_size = $disk->exists($document->file_path) ? $disk->size($document->file_path) : null;
                $document->file_name ??= basename($document->file_path);
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'employe_id');
    }
}
