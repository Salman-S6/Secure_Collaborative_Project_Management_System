<?php

namespace App\Services\Team;

use App\Enums\Roles\SystemRole;
use App\Enums\Roles\TeamRole;
use App\Exceptions\InvalidTeamOwnerException;
use App\Models\Team;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TeamService
{
    /**
     * Fetch user groups with cache support and permissions.
     */
    public function getTeamsForUser(User $user, int $page = 1): LengthAwarePaginator
    {
        $cacheKey = "teams.user.{$user->id}.page.{$page}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($user) {
            $query = ($user->role === SystemRole::ADMIN)
                ? Team::query()
                : $user->teams();

            return $query->with(['owner', 'members'])->latest()->paginate();
        });
    }

    /**
     * create new team.
     */
    public function createTeam(array $data): Team
    {
        $team = DB::transaction(function () use ($data) {
            $createdTeam = Team::create($data);
            $ownerId = $data['owner_id'];
            $createdTeam->members()->attach($ownerId, ['role' => TeamRole::OWNER->value]);
            return $createdTeam;
        });

        Cache::flush();
        return $team->load(['owner', 'members']);
    }

    /**
     * Bring details of a specific team.
     */
    public function getTeamDetails(Team $team): Team
    {
        return $team->load(['owner', 'members', 'projects']);
    }

    /**
     * update existing team.
     *
     * @throws InvalidTeamOwnerException
     */
    public function updateTeam(Team $team, array $data): Team
    {
        $team->fill($data);

        if ($team->isDirty('owner_id')) {
            $newOwner = User::findOrFail($team->owner_id);
            if (!$team->members->contains($newOwner)) {
                throw new InvalidTeamOwnerException('New owner must be a member of the team first.');
            }

            $oldOwnerId = $team->getOriginal('owner_id');

            DB::transaction(function () use ($team, $newOwner, $oldOwnerId) {
                $team->members()->updateExistingPivot($newOwner->id, ['role' => TeamRole::OWNER->value]);
                if ($oldOwnerId) {
                    $team->members()->updateExistingPivot($oldOwnerId, ['role' => TeamRole::MEMBER->value]);
                }
            });
        }

        $team->save();
        Cache::flush();
        return $team->load(['owner', 'members']);
    }

    /**
     * delete team.
     */
    public function deleteTeam(Team $team): void
    {
        $team->delete();
        Cache::flush();
    }
}
