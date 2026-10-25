<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\DemandeConge;
use Illuminate\Auth\Access\HandlesAuthorization;

class DemandeCongePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return true;
    }
 
    public function view(AuthUser $authUser, DemandeConge $demande): bool
    {
        return $demande->employe_id === $authUser->id || $authUser->can('ViewAny:DemandeConge');
    }
 
    public function create(AuthUser $authUser): bool
    {
        return true;
    }
 
    /** Annuler : uniquement SA demande, tant qu'elle est en attente. */
    public function annuler(AuthUser $authUser, DemandeConge $demande): bool
    {
        return $demande->employe_id === $authUser->id && $demande->estEnAttente();
    }
 
    public function update(AuthUser $authUser, DemandeConge $demande): bool
    {
        return $authUser->can('Update:DemandeConge');
    }
 
    /**
     * Approuver / refuser : droit « Update », demande en attente,
     * et personne (sauf l'admin) ne traite sa propre demande.
     */
    public function traiter(AuthUser $authUser, DemandeConge $demande): bool
    {
        return $authUser->can('Update:DemandeConge')
            && $demande->estEnAttente()
            && ($demande->employe_id !== $authUser->id || $authUser->hasRole('admin'));
    }
 
    public function delete(AuthUser $authUser, DemandeConge $demande): bool
    {
        return $authUser->can('Delete:DemandeConge');
    }
 
    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:DemandeConge');
    }
 
    public function restore(AuthUser $authUser, DemandeConge $demande): bool
    {
        return $authUser->can('Restore:DemandeConge');
    }
 
    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:DemandeConge');
    }
 
    public function forceDelete(AuthUser $authUser, DemandeConge $demande): bool
    {
        return $authUser->can('ForceDelete:DemandeConge');
    }
 
    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:DemandeConge');
    }
 
    public function replicate(AuthUser $authUser, DemandeConge $demande): bool
    {
        return $authUser->can('Replicate:DemandeConge');
    }
 
    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:DemandeConge');
    }

}