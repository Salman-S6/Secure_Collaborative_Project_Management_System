<?php

namespace App\Services\Project;

use App\Enums\TaskStatus;
use App\Models\Project;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class ProjectService
{
    /**
     * Fetch the list of projects for the user with support for caching and filtering..
     *
     * @param User $user
     * @param array $filters
     * @param int $page
     * @return LengthAwarePaginator
     */
    public function getUserProjects(User $user, array $filters = [], int $page = 1): LengthAwarePaginator
    {
        $hasOverdueTasks = (bool) ($filters['has_overdue_tasks'] ?? false);
        $cacheKey = "projects.user.{$user->id}.page.{$page}.overdue.{$hasOverdueTasks}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($user, $hasOverdueTasks) {
            $query = Project::forUser($user)->withCount('tasks');

            if ($hasOverdueTasks) {
                $query->whereHas('tasks', function ($query) {
                    $query->where('status', '!=', TaskStatus::COMPLETED)
                        ->where('due_date', '<', now());
                });
            }

            return $query->latest()->paginate();
        });
    }

    /**
     * create new project.
     *
     * @param array $data
     * @param User $user
     * @return Project
     */
    public function createProject(array $data, User $user): Project
    {
        $project = Project::create(
            ['created_by_user_id' => $user->id] + $data
        );

        $project->members()->attach($user->id);

        Cache::flush();

        return $project;
    }

    /**
     * Bring the project details with all the required relations..
     *
     * @param Project $project
     * @return Project
     */
    public function getProjectDetails(Project $project): Project
    {
        return $project->load(['team', 'creator', 'members', 'tasks', 'comments', 'attachments']);
    }

    /**
     * update existing project.
     *
     * @param Project $project
     * @param array $data
     * @return Project
     */
    public function updateProject(Project $project, array $data): Project
    {
        $project->fill($data);

        if ($project->isDirty('status')) {
            Log::info("Project status changed for project {$project->id}");
        }

        $project->save();

        Cache::flush();

        return $project->load(['team', 'creator', 'members']);
    }

    /**
     * delete project.
     *
     * @param Project $project
     * @return void
     */
    public function deleteProject(Project $project): void
    {
        $project->delete();

        Cache::flush();
    }
}
