<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Foundation\Auth\User as AuthUser;

/**
 * Policy générée par Shield, avec une protection supplémentaire :
 * seul un administrateur peut modifier ou supprimer un compte administrateur,
 * et personne ne supprime son propre compte.
 *
 * Gardez ce fichier : relancez shield:generate avec --ignore-existing-policies pour ne pas l'écraser.
 */
class UserPolicy
{
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:User');
    }

    public function view(AuthUser $authUser, User $user): bool
    {
        return $authUser->can('View:User');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:User');
    }

    public function update(AuthUser $authUser, User $user): bool
    {
        return $authUser->can('Update:User') && $this->peutToucher($authUser, $user);
    }

    public function delete(AuthUser $authUser, User $user): bool
    {
        return $authUser->can('Delete:User')
            && $authUser->id !== $user->id
            && $this->peutToucher($authUser, $user);
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:User');
    }

    public function restore(AuthUser $authUser, User $user): bool
    {
        return $authUser->can('Restore:User');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:User');
    }

    public function forceDelete(AuthUser $authUser, User $user): bool
    {
        return $authUser->can('ForceDelete:User');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:User');
    }

    public function replicate(AuthUser $authUser, User $user): bool
    {
        return $authUser->can('Replicate:User');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:User');
    }

    private function peutToucher(AuthUser $authUser, User $cible): bool
    {
        return ! $cible->hasRole('admin') || $authUser->hasRole('admin');
    }
}