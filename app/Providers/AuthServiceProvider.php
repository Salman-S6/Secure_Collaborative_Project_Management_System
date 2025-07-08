<?php

namespace App\Providers;

use App\Models\Team;
use App\Policies\Teams\TeamPolicy;
use App\Models\Project;
use App\Policies\Projects\ProjectPolicy;
use App\Models\Task;
use App\Policies\Tasks\TaskPolicy;
use App\Models\Comment;
use App\Policies\Comments\CommentPolicy;
use App\Models\Attachment;
use App\Policies\Attachments\AttachmentPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Team::class => TeamPolicy::class,
        Project::class => ProjectPolicy::class,
        Task::class => TaskPolicy::class,
        Comment::class => CommentPolicy::class,
        Attachment::class => AttachmentPolicy::class
    ];

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
