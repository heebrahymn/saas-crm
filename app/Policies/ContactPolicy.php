<?php

namespace App\Policies;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ContactPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('manager') || $user->hasRole('staff');
    }

    public function view(User $user, Contact $contact): bool
    {
        return $contact->company_id === $user->company_id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('manager') || $user->hasRole('staff');
    }

    public function update(User $user, Contact $contact): bool
    {
        return $contact->company_id === $user->company_id;
    }

    public function delete(User $user, Contact $contact): bool
    {
        return $contact->company_id === $user->company_id && $user->hasRole('admin');
    }
}
