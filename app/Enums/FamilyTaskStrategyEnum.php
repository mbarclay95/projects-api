<?php

namespace App\Enums;

enum FamilyTaskStrategyEnum: string
{
    case PER_TASK = 'per task';
    case PER_TASK_POINT = 'per task point';
}
