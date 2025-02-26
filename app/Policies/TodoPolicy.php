<?php

namespace App\Policies;

use App\Models\Todo;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TodoPolicy
{

    public function viewAny(User $user): bool
    {
        return true;
    }


    public function view(User $user, Todo $todo): bool
    {
        return $user->id === $todo->user_id;
    }


    public function create(User $user): bool
    {
        return true;
    }


    public function update(User $user, Todo $todo): bool
    {
        return $user->id === $todo->user_id;
    }


    public function delete(User $user, Todo $todo): bool
    {
        return $user->id === $todo->user_id;
    }


    public function restore(User $user, Todo $todo): bool
    {
        return false;
    }


    public function forceDelete(User $user, Todo $todo): bool
    {
        return false;
    }
}
