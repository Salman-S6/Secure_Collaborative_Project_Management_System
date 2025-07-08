<?php

namespace App\Http\Controllers\Api\Task;

use App\Http\Controllers\Controller;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Http\Resources\Task\TaskResource;
use App\Models\Project;
use App\Models\Task;
use App\Services\Task\TaskService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TaskController extends Controller
{
    private TaskService $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    /**
     * Display a listing of tasks for a specific project.
     */
    public function index(Project $project)
    {
        $this->authorize('view', $project);
        $tasks = $this->taskService->getTasksForProject($project);
        return TaskResource::collection($tasks);
    }

    /**
     * Store a newly created task in storage.
     */
    public function store(StoreTaskRequest $request)
    {
        $task = $this->taskService->createTask($request->validated());

        return new TaskResource($task);
    }

    /**
     * Display the specified task.
     */
    public function show(Task $task)
    {
        $this->authorize('view', $task);
        $task = $this->taskService->getTaskDetails($task);

        return new TaskResource($task);
    }

    /**
     * Update the specified task in storage.
     */
    public function update(UpdateTaskRequest $request, Task $task)
    {
        $task = $this->taskService->updateTask($task, $request->validated());

        return new TaskResource($task);
    }

    /**
     * Remove the specified task from storage.
     */
    public function destroy(Task $task): Response
    {
        $this->authorize('delete', $task);
        $task->taskService->deleteTask($task);
        return response()->noContent();
    }
}
