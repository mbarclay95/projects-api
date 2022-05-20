<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

trait HasApiModel
{
    /**
     * @param Model|null $model
     * @param array $hideItem
     * @return array|null
     */
    public static function toApiModel(?Model $model, array $hideItem = []): ?array
    {
        if (!$model) {
            return null;
        }

        $attributes = self::$apiModelAttributes ?? [];
        $entities = self::$apiModelEntities ?? [];
        $arrayEntities = self::$apiModelArrayEntities ?? [];
        $returnArray = [];

        foreach ($attributes as $attribute) {
            if (!in_array($attribute, $hideItem)) {
                $returnArray[Str::camel($attribute)] = $model->$attribute;
            }
        }

        foreach ($entities as $entity => $class) {
            if (!in_array($entity, $hideItem)) {
                $returnArray[$entity] = $class::toApiModel($model->$entity);
            }
        }

        foreach ($arrayEntities as $arrayEntity => $class) {
            if (!in_array($arrayEntity, $hideItem)) {
                $returnArray[$arrayEntity] = $class::toApiModels($model->$arrayEntity);
            }
        }

        return $returnArray;
    }

    /**
     * @param Collection|Model[] $models
     * @param array $hideItem
     * @return array
     */
    public static function toApiModels(array|Collection $models, array $hideItem = []): array
    {
        $apiModels = [];

        foreach ($models as $model) {
            $apiModels[] = self::toApiModel($model, $hideItem);
        }

        return $apiModels;
    }
}
