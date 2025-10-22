<?php

namespace App\Policies;

use App\Models\ArmyList;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ArmyListPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('manage-army-lists') 
            || $user->hasPermissionTo('validate-army-lists');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ArmyList $armyList): bool
    {
        // Propriétaire, admin ou liste validée (publique)
        return $armyList->user_id === $user->id
            || $user->hasPermissionTo('manage-army-lists')
            || $user->hasPermissionTo('validate-army-lists')
            || $armyList->status === 'validated';
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('upload-army-list');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ArmyList $armyList): bool
    {
        // Propriétaire si draft, admin toujours
        return ($armyList->user_id === $user->id && $armyList->status === 'draft')
            || $user->hasPermissionTo('manage-army-lists');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ArmyList $armyList): bool
    {
        // Propriétaire ou admin
        return $armyList->user_id === $user->id
            || $user->hasPermissionTo('manage-army-lists');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ArmyList $armyList): bool
    {
        return $user->hasPermissionTo('manage-army-lists');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ArmyList $armyList): bool
    {
        return $user->hasPermissionTo('manage-army-lists');
    }

    /**
     * Determine whether the user can validate the model.
     */
    public function validate(User $user, ArmyList $armyList): bool
    {
        return $user->hasPermissionTo('validate-army-lists')
            && $armyList->status === 'pending';
    }

    /**
     * Determine whether the user can reject the model.
     */
    public function reject(User $user, ArmyList $armyList): bool
    {
        return $user->hasPermissionTo('validate-army-lists')
            && $armyList->status === 'pending';
    }
}
