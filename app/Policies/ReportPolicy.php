<?php

namespace App\Policies;

use App\Models\Report;
use App\Models\User;

class ReportPolicy
{
    public function viewAll(User $user): bool
    {
        //return true;
        return in_array($user->role->title , ['admin']);
    }

    public function viewAny(User $user): bool
    {
        return in_array($user->role->title , ['supervisor']);
    }

    public function viewMine(User $user): bool
    {
        return in_array($user->role->title , ['salesman']);
    }

    public function view(User $user): bool
    {   
        return in_array($user->role->title , ['admin', 'supervisor']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return in_array($user->role->title , ['salesman']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Report $report): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Report $report): bool
    {
        return $user->role->title === 'admin';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Report $report): bool
    {
        return $user->role->title === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Report $report): bool
    {
        return $user->role->title === 'admin';
    }
}
