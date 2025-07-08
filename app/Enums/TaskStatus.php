<?php
namespace App\Enums;

enum TaskStatus: string
{
    case PENDING = 'pending';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case IN_REVIEW = 'in_review';
    case CANCELLED = 'cancelled';
}
