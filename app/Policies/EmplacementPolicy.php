<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Emplacement;
use Illuminate\Auth\Access\HandlesAuthorization;

class EmplacementPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Emplacement');
    }

    public function view(AuthUser $authUser, Emplacement $emplacement): bool
    {
        return $authUser->can('View:Emplacement');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Emplacement');
    }

    public function update(AuthUser $authUser, Emplacement $emplacement): bool
    {
        return $authUser->can('Update:Emplacement');
    }

    public function delete(AuthUser $authUser, Emplacement $emplacement): bool
    {
        return $authUser->can('Delete:Emplacement');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Emplacement');
    }

    public function restore(AuthUser $authUser, Emplacement $emplacement): bool
    {
        return $authUser->can('Restore:Emplacement');
    }

    public function forceDelete(AuthUser $authUser, Emplacement $emplacement): bool
    {
        return $authUser->can('ForceDelete:Emplacement');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Emplacement');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Emplacement');
    }

    public function replicate(AuthUser $authUser, Emplacement $emplacement): bool
    {
        return $authUser->can('Replicate:Emplacement');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Emplacement');
    }

}