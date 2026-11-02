<?php

namespace App\Enums;

enum BoardGamePlayStatusEnum: string
{
    case IN_PROGRESS = 'in_progress';
    case WON = 'won';
    case LOST = 'lost';
}
