<?php

namespace App\Policies\Teams;

use App\Enums\Roles\SystemRole;
use App\Models\Team;
use App\Models\User;

class TeamPolicy
{
    /**
     * Grant all abilities to system admins.
     */
    public function before(User $user, string $ability): ?bool
    {
        return $user->role === SystemRole::ADMIN ? true : null;
    }

    /**
     * Determine whether the user can create new teams.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the team.
     */
    public function view(User $user, Team $team): bool
    {
        if ($user->role === SystemRole::ADMIN) {
            return true;
        }
        return $team->members->contains($user);
    }

    /**
     * Determine whether the user can update the team.
     */
    public function update(User $user, Team $team): bool
    {
        if ($user->role === SystemRole::ADMIN) {
            return true;
        }
        return $user->id === $team->owner_id;
    }

    /**
     * Determine whether the user can delete the team.
     */
    public function delete(User $user, Team $team): bool
    {
        return $this->update($user, $team);
    }

    /**
     * Determine whether the user can add a team member.
     */
    public function addMember(User $user, Team $team): bool
    {
        if ($user->role === SystemRole::ADMIN) {
            return true;
        }
        return $user->id === $team->owner_id;
    }

    /**
     * Determine whether the user can update a team member's role.
     */
    public function updateMember(User $user, Team $team): bool
    {
        return $this->addMember($user, $team);
    }

    /**
     * Determine whether the user can remove a team member.
     */
    public function removeMember(User $user, Team $team, User $memberToRemove): bool
    {
        if ($memberToRemove->id === $team->owner_id) {
            return false;
        }

        if ($user->id === $team->owner_id) {
            return true;
        }

        if ($user->id === $memberToRemove->id) {
            return true;
        }

        return false;
    }
}
