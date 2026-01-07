<?php

namespace App\Policies;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class LeadPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('manager') || $user->hasRole('staff');
    }

    public function view(User $user, Lead $lead): bool
    {
        return $lead->company_id === $user->company_id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('manager') || $user->hasRole('staff');
    }

    public function update(User $user, Lead $lead): bool
    {
        return $lead->company_id === $user->company_id && 
               ($user->hasRole('admin') || $user->hasRole('manager') || 
                $lead->assigned_to === $user->id);
    }

    public function delete(User $user, Lead $lead): bool
    {
        return $lead->company_id === $user->company_id && $user->hasRole('admin');
    }
}