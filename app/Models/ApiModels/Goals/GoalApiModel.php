<?php

namespace App\Models\ApiModels\Goals;

use App\Models\Goals\Goal;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use JetBrains\PhpStorm\Pure;

class GoalApiModel
{
    public int $id;
    public Carbon $createdAt;
    public string $title;
    public int $expectedAmount;
    public string $unit;
    public string $lengthOfTime;
    public string $equality;
    public string $verb;

    /**
     * @param Goal $entity
     * @return GoalApiModel
     */
    #[Pure] static function fromEntity(Goal $entity): GoalApiModel
    {
        $apiModel = new GoalApiModel();

        $apiModel->id = $entity->id;
        $apiModel->createdAt = $entity->created_at;
        $apiModel->title = $entity->title;
        $apiModel->expectedAmount = $entity->expected_amount;
        $apiModel->unit = $entity->unit;
        $apiModel->lengthOfTime = $entity->length_of_time;
        $apiModel->equality = $entity->equality;
        $apiModel->verb = $entity->verb;

        return $apiModel;
    }

    /**
     * @param Goal[] $entities
     * @return GoalApiModel[]
     */
    #[Pure] static function fromEntities($entities): array
    {
        $entries = [];

        foreach ($entities as $entity) {
            $entries[] = self::fromEntity($entity);
        }

        return $entries;
    }
}
