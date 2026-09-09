<?php

namespace App\Enums;

enum FeatureEnum: string
{
    case TASKS = 'tasks';
    case GROCERY = 'grocery';

    public const TAG_SCOPES = [self::TASKS];

    public const GROUP_SCOPES = [self::TASKS, self::GROCERY];

    public static function tagScopeValues(): array
    {
        return array_map(fn (self $case) => $case->value, self::TAG_SCOPES);
    }

    public static function groupScopeValues(): array
    {
        return array_map(fn (self $case) => $case->value, self::GROUP_SCOPES);
    }
}
