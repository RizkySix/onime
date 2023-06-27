<?php

namespace App\Policies;

use App\Models\Pricing;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PricingPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
       return true;
    }

    /**
     * Determine whether the user non admin view the models.
     */
    public function viewAdmin(User $user): Response
    {
        return $user->admin == true
        ? Response::allow()
        : Response::denyAsNotFound();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Pricing $pricing): Response
    {
        return $user->admin == true
        ? Response::allow()
        : Response::denyAsNotFound();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): Response
    {
        return $user->admin == true
        ? Response::allow()
        : Response::denyAsNotFound();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Pricing $pricing): Response
    {
        return $user->admin == true
        ? Response::allow()
        : Response::denyAsNotFound();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Pricing $pricing): Response
    {
        return $user->admin == true
        ? Response::allow()
        : Response::denyAsNotFound();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user): Response
    {
        return $user->admin == true
        ? Response::allow()
        : Response::denyAsNotFound();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user): Response
    {
        return $user->admin == true
        ? Response::allow()
        : Response::denyAsNotFound();
    }
}
