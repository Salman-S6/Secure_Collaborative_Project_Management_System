<?php

namespace App\Policies\Comments;

use App\Enums\Roles\SystemRole;
use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

class CommentPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->role === SystemRole::ADMIN ? true : null;
    }

    public function view(User $user, Comment $comment): bool
{
    return $user->can('view', $comment->commentable);
}

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Comment $comment): bool
    {
        return $user->id === $comment->user_id;
    }

    public function delete(User $user, Comment $comment): bool
    {
        return $user->id === $comment->user_id;
    }
}
