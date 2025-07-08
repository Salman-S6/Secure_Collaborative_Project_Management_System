<?php

namespace App\Enums\Roles;

enum TeamRole: string
{
    case OWNER = 'owner';
    case PROJECT_MANAGER = 'project_manager';
    case MEMBER = 'member';
}
