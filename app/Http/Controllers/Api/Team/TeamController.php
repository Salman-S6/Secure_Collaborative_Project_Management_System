<?php

namespace App\Http\Controllers\Api\Team;

use App\Enums\Roles\SystemRole;
use App\Enums\Roles\TeamRole;
use App\Exceptions\InvalidTeamOwnerException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Team\StoreTeamRequest;
use App\Http\Requests\Team\UpdateTeamRequest;
use App\Http\Resources\Team\TeamResource;
use App\Models\Team;
use App\Models\User;
use App\Services\Team\TeamService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class TeamController extends Controller
{
    private TeamService $teamService;

    public function __construct(TeamService $teamService)
    {
        $this->teamService = $teamService;
    }


    /**
     * Display a listing of the user's teams.
     */
    public function index(Request $request)
    {
        $teams = $this->teamService->getTeamsForUser(
            $request->user(),
            $request->get('page', 1)
        );

        return TeamResource::collection($teams);
    }

    /**
     * Store a newly created team in storage.
     */
    public function store(StoreTeamRequest $request)
    {
        $team = $this->teamService->createTeam($request->validated());

        return new TeamResource($team);
    }

    /**
     * Display the specified team.
     */
    public function show(Team $team)
    {
        $this->authorize('view', $team);
        $team = $this->teamService->getTeamDetails($team);

        return new TeamResource($team);
    }

    /**
     * Update the specified team in storage.
     */
    public function update(UpdateTeamRequest $request, Team $team)
    {
        try {
            $team = $this->teamService->updateTeam($team, $request->validated());

            return new TeamResource(resource: $team);

        } catch (InvalidTeamOwnerException $e) {
            return $e->render($request);
        }
    }


    /**
     * Remove the specified team from storage.
     */
    public function destroy(Team $team): Response
    {
        $this->authorize('delete', $team);
        $this->teamService->deleteTeam($team);
        return response()->noContent();
    }

}
