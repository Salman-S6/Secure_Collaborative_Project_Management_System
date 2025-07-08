<?php

namespace App\Policies\Attachments;

use App\Enums\Roles\SystemRole;
use App\Models\Attachment;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AttachmentPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->role === SystemRole::ADMIN ? true : null;
    }

    /**
     * Determine whether the user can view/download the attachment.
     */
    public function view(User $user, Attachment $attachment): bool
{
    return $user->can('view', $attachment->attachable);
}


    /**
     * Determine whether the user can delete the attachment.
     */
    public function delete(User $user, Attachment $attachment): bool
    {
        return $user->can('update', $attachment->attachable) || $user->id === $attachment->user_id;
    }
}
