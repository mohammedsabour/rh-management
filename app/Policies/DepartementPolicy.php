<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Departement;
use Illuminate\Auth\Access\HandlesAuthorization;

class DepartementPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Departement');
    }

    public function view(AuthUser $authUser, Departement $departement): bool
    {
        return $authUser->can('View:Departement');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Departement');
    }

    public function update(AuthUser $authUser, Departement $departement): bool
    {
        return $authUser->can('Update:Departement');
    }

    public function delete(AuthUser $authUser, Departement $departement): bool
    {
        return $authUser->can('Delete:Departement');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Departement');
    }

    public function restore(AuthUser $authUser, Departement $departement): bool
    {
        return $authUser->can('Restore:Departement');
    }

    public function forceDelete(AuthUser $authUser, Departement $departement): bool
    {
        return $authUser->can('ForceDelete:Departement');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Departement');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Departement');
    }

    public function replicate(AuthUser $authUser, Departement $departement): bool
    {
        return $authUser->can('Replicate:Departement');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Departement');
    }

}