<?php

namespace App\Policies;

use App\Models\Inventaire;
use Illuminate\Foundation\Auth\User as AuthUser;

/**
 * Droits du récolement (format Shield : « Action:Inventaire »).
 * « Update » couvre aussi le pointage et la clôture.
 * Gardez ce fichier : shield:generate --ignore-existing-policies ne l'écrase pas.
 */
class InventairePolicy
{
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Inventaire');
    }

    public function view(AuthUser $authUser, Inventaire $inventaire): bool
    {
        return $authUser->can('View:Inventaire');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Inventaire');
    }

    public function update(AuthUser $authUser, Inventaire $inventaire): bool
    {
        return $authUser->can('Update:Inventaire');
    }

    public function delete(AuthUser $authUser, Inventaire $inventaire): bool
    {
        return $authUser->can('Delete:Inventaire');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Inventaire');
    }
}
