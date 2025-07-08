<?php

namespace App\Policies\Tasks;

use App\Enums\Roles\SystemRole;
use App\Enums\Roles\TeamRole;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->role === SystemRole::ADMIN ? true : null;
    }

    public function view(User $user, Task $task): bool
{
    return $user->can('view', $task->project);
}


    public function create(User $user, Project $project): bool
    {
        return $project->members->contains($user);
    }

    public function update(User $user, Task $task): bool
    {
        if ($user->id === $task->project->team->owner_id) {
            return true;
        }

        $membership = $user->teams()->find($task->project->team_id);

        return $membership && $membership->pivot->role === TeamRole::PROJECT_MANAGER->value;
    }

    public function delete(User $user, Task $task): bool
    {
        return $this->update($user, $task);
    }
}
