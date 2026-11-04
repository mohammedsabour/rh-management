<?php

namespace App\Models\Concerns;

use App\Models\Document;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

/**
 * Dossier de stockage d'un employé : employes/{id}/  (disque privé).
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait DossierEmploye
{
    public function dossierStockage(): string
    {
        return 'employes/' . $this->getKey();
    }

    public static function bootDossierEmploye(): void
    {
        User::saving(function (User $user) {
            if (! $user->exists || ! $user->isDirty('photo')) {
                return;
            }

            $ancien = $user->getOriginal('photo');
            $disque = Storage::disk(Document::DISK);

            if ($ancien && $ancien !== $user->photo && str_starts_with($ancien, 'employes/') && $disque->exists($ancien)) {
                $disque->delete($ancien);
            }
        });

        User::saved(function (User $user) {
            $photo = $user->photo;
            $disque = Storage::disk(Document::DISK);
            $dossier = $user->dossierStockage();

            if (! $photo || str_starts_with($photo, $dossier . '/') || ! $disque->exists($photo)) {
                return;
            }

            $final = $dossier . '/photo.' . pathinfo($photo, PATHINFO_EXTENSION);

            if ($disque->exists($final)) {
                $disque->delete($final);
            }

            $disque->move($photo, $final);
            $user->forceFill(['photo' => $final])->saveQuietly();
        });
    }
}