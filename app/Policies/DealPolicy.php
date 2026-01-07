<?php

namespace App\Policies;

use App\Models\Deal;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DealPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('manager') || $user->hasRole('staff');
    }

    public function view(User $user, Deal $deal): bool
    {
        return $deal->company_id === $user->company_id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('manager');
    }

    public function update(User $user, Deal $deal): bool
    {
        return $deal->company_id === $user->company_id && 
               ($user->hasRole('admin') || $user->hasRole('manager') || 
                $deal->assigned_to === $user->id);
    }

    public function delete(User $user, Deal $deal): bool
    {
        return $deal->company_id === $user->company_id && $user->hasRole('admin');
    }
}