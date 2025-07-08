<?php

namespace App\Policies\Projects;

use App\Enums\Roles\SystemRole;
use App\Enums\Roles\TeamRole;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;

class ProjectPolicy
{
    /**
     * Grant all abilities to system admins.
     */
    public function before(User $user, string $ability): bool|null
    {
        return $user->role === SystemRole::ADMIN ? true : null;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Project $project): bool
    {
        return $user->teams->contains($project->team);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Team $team): bool
    {
        if ($user->id === $team->owner_id) {
            return true;
        }
        $teamMembership = $user->teams()->find($team->id);
        return $teamMembership && $teamMembership->pivot->role === 'project_manager';
    }

    public function addMember(User $user, Project $project): bool
    {
        return $this->update($user, $project);
    }

    /**
     * Determine whether the user can remove a member from the project.
     */
    public function removeMember(User $user, Project $project): bool
    {
        // Reuse the update permission. Admins, Team Owners, and Project Managers can remove members.
        return $this->update($user, $project);
    }


    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Project $project): bool
    {
        $team = $project->team;

        if ($user->id === $team->owner_id) {
            return true;
        }

        $membership = $user->teams()->find($team->id);
        return $membership && $membership->pivot->role === TeamRole::PROJECT_MANAGER->value;
    }


    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Project $project): bool
    {
        return $this->update($user, $project);
    }
}
