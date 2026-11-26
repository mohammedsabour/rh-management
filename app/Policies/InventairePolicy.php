<?php

namespace App\Policies;

use App\Models\Inventaire;
use Illuminate\Foundation\Auth\User as AuthUser;

/**
 * Droits du récolement (format Shield : « Action:Inventaire »).
 *
 * - Un membre d'une commission n'a aucun accès au récolement parent (il ne doit pas voir le travail de l'autre
 *   commission) : il passe uniquement par « Mes comptages ». L'admin n'est pas concerné (Gate::before).
 * - rapprocher : comparer A et B, clôturer (décision globale) et imprimer le PV final. Réservé à l'admin (Gate::before)
 *   et au CRÉATEUR du récolement, sauf s'il siège lui-même dans une commission de ce récolement.
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
        return $authUser->can('View:Inventaire') && ! $inventaire->aPourMembre($authUser->id);
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Inventaire');
    }

    public function update(AuthUser $authUser, Inventaire $inventaire): bool
    {
        return $authUser->can('Update:Inventaire') && ! $inventaire->aPourMembre($authUser->id);
    }

    public function delete(AuthUser $authUser, Inventaire $inventaire): bool
    {
        return $authUser->can('Delete:Inventaire') && ! $inventaire->aPourMembre($authUser->id);
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Inventaire');
    }

    public function rapprocher(AuthUser $authUser, Inventaire $inventaire): bool
    {
        return $inventaire->cree_par === $authUser->id && ! $inventaire->aPourMembre($authUser->id);
    }
}