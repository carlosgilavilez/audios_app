<?php

namespace App\Policies;

use App\Models\Serie;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SeriePolicy
{
    public function before(User $user, string $ability): bool|null
    {
        if ($user->role === 'admin') {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === 'editor';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Serie $serie): bool
    {
        return $user->role === 'editor';
    }

    public function create(User $user)
    {
        return in_array($user->role, ['admin', 'editor']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Serie $serie)
    {
        return in_array($user->role, ['admin', 'editor']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Serie $serie)
    {
        return in_array($user->role, ['admin', 'editor']);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Serie $serie): bool
    {
        return $user->role === 'editor';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Serie $serie): bool
    {
        return $user->role === 'editor';
    }
}
