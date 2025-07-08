<?php

namespace App\Services\Task;

use App\Jobs\SendNewTaskEmail;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Collection;

class TaskService
{
    /**
     * Fetch the task list for a specific project.
     *
     * @param Project $project
     * @return Collection
     */
    public function getTasksForProject(Project $project): Collection
    {
        return $project->tasks()->with(['project', 'assignedTo'])->get();
    }

    /**
     * create new task.
     *
     * @param array $data
     * @return Task
     */
    public function createTask(array $data): Task
    {
        return Task::create($data);
    }

    /**
     * Bringing specific important details with the relationships..
     *
     * @param Task $task
     * @return Task
     */
    public function getTaskDetails(Task $task): Task
    {
        return $task->load(['project', 'assignedTo']);
    }

    /**
     * update existing task.
     *
     * @param Task $task
     * @param array $validatedData
     * @return Task
     */
    public function updateTask(Task $task, array $validatedData): Task
    {
        $oldAssignedUserId = $task->assigned_to_user_id;

        $task->update($validatedData);

        $newAssignedUserId = $validatedData['assigned_to_user_id'] ?? null;
        if ($newAssignedUserId && $newAssignedUserId !== $oldAssignedUserId) {
            $assignedUser = User::find($newAssignedUserId);
            if ($assignedUser) {
                SendNewTaskEmail::dispatch($task, $assignedUser);
            }
        }

        return $task->load(['project', 'assignedTo']);
    }

    /**
     * delete task.
     *
     * @param Task $task
     * @return void
     */
    public function deleteTask(Task $task): void
    {
        $task->delete();
    }
}
