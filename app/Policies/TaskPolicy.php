<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('manager') || $user->hasRole('staff');
    }

    public function view(User $user, Task $task): bool
    {
        return $task->company_id === $user->company_id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('manager') || $user->hasRole('staff');
    }

    public function update(User $user, Task $task): bool
    {
        return $task->company_id === $user->company_id && 
               ($user->hasRole('admin') || $user->hasRole('manager') || 
                $task->assigned_to === $user->id);
    }

    public function delete(User $user, Task $task): bool
    {
        return $task->company_id === $user->company_id && 
               ($user->hasRole('admin') || $task->assigned_to === $user->id);
    }
}