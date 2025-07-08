<?php

namespace App\Enums\Roles;

enum SystemRole: string
{
    case ADMIN = 'admin';
    case MEMBER = 'member';
}
