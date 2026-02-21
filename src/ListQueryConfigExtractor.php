<?php

declare(strict_types=1);

namespace Webbizi\ListQuery;

use Webbizi\ListQuery\Config\QueryConfig;
use Webbizi\ListQuery\Config\QueryConfigurable;

final class ListQueryConfigExtractor
{
    /**
     * @param  class-string<QueryConfigurable>  $repositoryClass
     * @return array{allowedFilters: array<string>, allowedSorts: array<string>, allowedRelations: array<string>}
     */
    public static function extract(string $repositoryClass): array
    {
        $config = $repositoryClass::queryConfig();

        return [
            'allowedFilters' => $config->allowedFilters,
            'allowedSorts' => $config->allowedSorts,
            'allowedRelations' => self::getAllowedRelations($config),
        ];
    }

    /**
     * @return array<string>
     */
    private static function getAllowedRelations(QueryConfig $config): array
    {
        $allowedRelations = [];

        foreach ($config->hasMany as $relation) {
            $allowedRelations[] = $relation->name;

            foreach ($relation->nested as $nested) {
                $allowedRelations[] = "{$relation->name}.{$nested->name}";
            }

            foreach ($relation->nestedHasMany as $nestedHasMany) {
                $allowedRelations[] = "{$relation->name}.{$nestedHasMany->name}";
            }
        }

        foreach ($config->belongsTo as $relation) {
            $allowedRelations[] = $relation->name;
        }

        return $allowedRelations;
    }
}
