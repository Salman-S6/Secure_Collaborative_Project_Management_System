<?php

namespace App\Http\Controllers\Api\Team;

use App\Http\Controllers\Controller;
use App\Http\Requests\Team\StoreTeamMemberRequest;
use App\Http\Requests\Team\UpdateTeamMemberRequest;
use App\Http\Resources\User\UserResource;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeamMembershipController extends Controller
{
    /**
     * Display a listing of the team's members.
     */
    public function showTeamMembers(Request $request, Team $team)
    {
        $this->authorize('view', $team);

        $members = $team->members()->get();

        return UserResource::collection($members);
    }

    /**
     * Add a new member to the given team.
     */
    public function addMember(StoreTeamMemberRequest $request, Team $team): JsonResponse
    {
        $validated = $request->validated();

        $team->members()->attach($validated['user_id'], ['role' => $validated['role']]);
        $user = User::findOrFail($request['user_id']);

        return response()->json([
            'message' => 'User added to the team successfully.',
            'data' => ['team' => $team, 'added_user' => $user]
        ], 201);
    }

    /**
     * Update the role of a member in the given team.
     */
    public function updateMemberRole(UpdateTeamMemberRequest $request, Team $team, User $member): JsonResponse
    {
        $team->members()->updateExistingPivot($member->id, [
            'role' => $request->validated('role'),
        ]);

        return response()->json(['message' => "User's role updated successfully."]);
    }

    /**
     * Remove a member from the given team.
     */
    public function removeMember(Team $team, User $member): JsonResponse
    {
        $this->authorize('removeMember', [$team, $member]);

        if (!$team->members()->find($member->id)) {
            return response()->json(['message' => 'This user is not a member of this team.'], 404);
        }

        $team->members()->detach($member->id);

        return response()->json(['message' => 'User removed from the team successfully.']);
    }
}
