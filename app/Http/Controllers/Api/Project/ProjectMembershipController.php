<?php

namespace App\Http\Controllers\Api\Project;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\StoreProjectMemberRequest;
use App\Http\Resources\User\UserResource;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectMembershipController extends Controller
{
    /**
     * Display a listing of the project's members.
     */
    public function showProjectMembers(Request $request, Project $project)
    {
        $this->authorize('view', $project);

        return UserResource::collection($project->members()->get());
    }

    /**
     * Add a new member to the given project.
     */
    public function addMember(StoreProjectMemberRequest $request, Project $project): JsonResponse
    {
        $user = User::findOrFail($request['user_id']);

        $project->members()->attach($request->validated('user_id'));

        return response()->json([
            'message' => 'User added to the project successfully.',
            'data' => $project,
            'user' => $user
        ], 201);
    }

    /**
     * Remove a member from the given project.
     */
    public function removeMember(Project $project, User $member)
    {
        $this->authorize('removeMember', $project);

        if (!$project->members()->find($member->id)) {
            return response()->json(['message' => 'This user is not a member of this project.'], 404);
        }

        $project->members()->detach($member->id);

        return response()->noContent();
    }
}
