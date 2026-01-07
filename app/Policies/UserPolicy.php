<?php

namespace App\Policies;

use App\Models\User as AppUser;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    public function viewAny(AppUser $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('manager');
    }

    public function view(AppUser $user, AppUser $model): bool
    {
        return $model->company_id === $user->company_id;
    }

    public function create(AppUser $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(AppUser $user, AppUser $model): bool
    {
        return $model->company_id === $user->company_id && $user->hasRole('admin');
    }

    public function delete(AppUser $user, AppUser $model): bool
    {
        return $model->company_id === $user->company_id && 
               $user->hasRole('admin') && 
               $model->id !== $user->id; // Cannot delete yourself
    }
}