<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

trait HasApiModel
{

    public static function buildFromAttributes(string $attributeKey, Model $model)
    {
        return $model->$attributeKey;
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
            $apiModels[] = static::toApiModel($model, $hideItem);
        }

        return $apiModels;
    }

    /**
     * @param Model|null $model
     * @param array $hideItem
     * @return array|string|null
     */
    public static function toApiModel(?Model $model, array $hideItem = []): array|null|string
    {
        if (!$model) {
            return null;
        }

        $attributes = static::$apiModelAttributes ?? [];
        $entities = static::$apiModelEntities ?? [];
        $arrayEntities = static::$apiModelArrayEntities ?? [];
        $returnArray = [];

        foreach ($attributes as $attribute) {
            if (!in_array($attribute, $hideItem)) {
                $returnArray[Str::camel($attribute)] = static::buildFromAttributes($attribute, $model);
            }
        }

        $hideItemsExploded = [];
        foreach ($hideItem as $item) {
            $temp = explode('.', $item);
            if (count($temp) > 1) {
                $hideItemsExploded[] = $temp;
            }
        }

        foreach ($entities as $entity => $class) {
            if (!in_array($entity, $hideItem)) {
                $entityHideItems = static::pullOutEntitiesHideItems($hideItemsExploded, $entity);
                $returnArray[$entity] = $class::toApiModel($model->$entity, $entityHideItems);
            }
        }

        foreach ($arrayEntities as $arrayEntity => $class) {
            if (!in_array($arrayEntity, $hideItem)) {
                $entityHideItems = static::pullOutEntitiesHideItems($hideItemsExploded, $arrayEntity);
                $returnArray[$arrayEntity] = $class::toApiModels($model->$arrayEntity, $entityHideItems);
            }
        }

        return $returnArray;
    }

    private static function pullOutEntitiesHideItems($hideItemsExploded, $entityName): array
    {
        $entityHideItems = [];
        foreach ($hideItemsExploded as $item) {
            if ($entityName == $item[0]) {
                array_shift($item);
                $entityHideItems[] = implode('.', $item);
            }
        }

        return $entityHideItems;
    }
}
