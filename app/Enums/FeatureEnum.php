<?php

namespace App\Enums;

use Illuminate\Validation\Rule;

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

    public function groupConfigRules(): array
    {
        return match ($this) {
            self::TASKS => [
                'taskStrategy' => ['required', Rule::enum(FamilyTaskStrategyEnum::class)],
                'taskPoints' => ['nullable', 'array'],
            ],
            self::GROCERY => [],
        };
    }

    public function buildConfig(array $request, array $current): array
    {
        return match ($this) {
            self::TASKS => [
                'task_strategy' => FamilyTaskStrategyEnum::from($request['taskStrategy'])->value,
                'task_points' => array_key_exists('taskPoints', $request)
                    ? ($request['taskPoints'] ?? [])
                    : ($current['task_points'] ?? []),
            ],
            self::GROCERY => [],
        };
    }
}
