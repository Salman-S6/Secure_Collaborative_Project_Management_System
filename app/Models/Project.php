<?php

namespace App\Models;

use App\Enums\ProjectStatus;
use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Mews\Purifier\Facades\Purifier;

class Project extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'team_id',
        'name',
        'description',
        'status',
        'due_date',
        'created_by_user_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'due_date' => 'date',
        'status' => ProjectStatus::class,
    ];

    /**
     * Get the team that owns the project.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the user who created the project.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * The members that belong to the project.
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_user');
    }

    /**
     * Get the tasks for the project.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Get all of the project's comments.
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * Get all of the project's attachments.
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     * Determine if the project is overdue.
     */
    protected function isOverdue(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->due_date?->isPast() && !in_array($this->status, [ProjectStatus::COMPLETED, ProjectStatus::CANCELLED]),
        );
    }

    /**
     * Scope a query to only include projects for a specific user.
     */
    public function scopeForUser(Builder $query, User $user): void
    {
        if ($user->role === \App\Enums\Roles\SystemRole::ADMIN) {
            return;
        }
        $teamIds = $user->teams()->pluck('id');
        $query->whereIn('team_id', $teamIds);
    }

    /**
     * Scope a query to only include active projects.
     */
    public function scopeActive(Builder $query): void
    {
        $query->whereNotIn('status', [ProjectStatus::COMPLETED, ProjectStatus::CANCELLED]);
    }

    /**
     * Get the count of completed tasks for the project, using a cache.
     */
    protected function completedTasksCount(): Attribute
    {
        return Attribute::make(
            get: function () {
                $cacheKey = "project.{$this->id}.completed_tasks_count";
                return Cache::remember($cacheKey, now()->addHour(), function () {
                    return $this->tasks()->where('status', TaskStatus::COMPLETED)->count();
                });
            }
        );
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            set: fn(string $value) => ucfirst(($value)),
        );
    }

    protected function description(): Attribute
    {
        return Attribute::make(
            set: fn(?string $value) => $value ? Purifier::clean($value) : null,
        );
    }

    protected function createdAt(): Attribute
    {
        return Attribute::make(
            get: fn($value) => Carbon::parse($value)->format('d/m/Y g:i A'),
        );
    }

    protected function updatedAt(): Attribute
    {
        return Attribute::make(
            get: fn($value) => Carbon::parse($value)->format('d/m/Y g:i A'),
        );
    }
}
