<?php

namespace App\Policies;

use App\Models\Autor;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AutorPolicy
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
    public function view(User $user, Autor $autor): bool
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
    public function update(User $user, Autor $autor): bool
    {
        return $user->role === 'editor';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Autor $autor)
    {
        return in_array($user->role, ['admin', 'editor']);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Autor $autor): bool
    {
        return $user->role === 'editor';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Autor $autor): bool
    {
        return $user->role === 'editor';
    }
}
