<?php

namespace App\Http\Controllers\Api\Project;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Http\Resources\Project\ProjectResource;
use App\Models\Project;
use App\Services\Project\ProjectService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProjectController extends Controller
{
    private ProjectService $projectService;

    public function __construct(ProjectService $projectService)
    {
        $this->projectService = $projectService;
    }

    public function index(Request $request)
    {
        $projects = $this->projectService->getUserProjects(
            $request->user(),
            $request->only('has_overdue_tasks'),
            $request->get('page', 1)
        );

        return ProjectResource::collection($projects);
    }

    public function store(StoreProjectRequest $request)
    {
        $project = $this->projectService->createProject($request->validated(), $request->user());

        return new ProjectResource($project);
    }

    public function show(Project $project)
    {
        $this->authorize('view', $project);
        $project = $this->projectService->getProjectDetails($project);

        return new ProjectResource($project);
    }

    public function update(UpdateProjectRequest $request, Project $project)
    {
        $project->update($request->validated());

        $project = $this->projectService->updateProject($project, $request->validated());

        return new ProjectResource($project);
    }

    public function destroy(Project $project): Response
    {
        $this->authorize('delete', $project);
        $this->projectService->deleteProject($project);
        return response()->noContent();
    }
}
