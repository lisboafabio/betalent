<?php

namespace App\Policies;

use App\Domain\User\Enums\UserRoleEnum;
use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [UserRoleEnum::ADMIN->value, UserRoleEnum::MANAGER->value]);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        if (in_array($user->role, [UserRoleEnum::ADMIN->value, UserRoleEnum::MANAGER->value])) {
            return true;
        }

        return $user->id === $model->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, [UserRoleEnum::ADMIN->value, UserRoleEnum::MANAGER->value]);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        if (in_array($user->role, [UserRoleEnum::ADMIN->value, UserRoleEnum::MANAGER->value])) {
            return true;
        }

        return $user->id === $model->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return false;
        }

        if ($user->role === UserRoleEnum::ADMIN->value) {
            return true;
        }

        if ($user->role === UserRoleEnum::MANAGER->value) {
            return $model->role !== UserRoleEnum::ADMIN->value;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return false;
    }
}
