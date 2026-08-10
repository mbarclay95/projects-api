<?php

namespace App\Enums;

enum DraftStatus: string
{
    case SIGNUP = 'signup';
    case LOCKED = 'locked';
    case IN_PROGRESS = 'in_progress';
    case COMPLETE = 'complete';
}
