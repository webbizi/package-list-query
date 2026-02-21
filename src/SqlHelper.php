<?php

declare(strict_types=1);

namespace Webbizi\ListQuery;

final class SqlHelper
{
    /**
     * @param  array<int, string>  $columns
     * @param  array<string, string>  $nestedJsonFragments
     */
    public static function buildJsonObject(string $alias, array $columns, array $nestedJsonFragments): string
    {
        $pairs = [];

        foreach ($columns as $column) {
            $pairs[] = "'{$column}', {$alias}.{$column}";
        }

        foreach ($nestedJsonFragments as $nestedName => $jsonFragment) {
            $pairs[] = "'{$nestedName}', {$jsonFragment}";
        }

        return 'JSON_OBJECT('.implode(', ', $pairs).')';
    }

    public static function toSnakeCase(string $camelCase): string
    {
        return strtolower((string) preg_replace('/[A-Z]/', '_$0', lcfirst($camelCase)));
    }

    /**
     * @param  array<string>  $relations
     * @return array<string>
     */
    public static function expandParentRelations(array $relations): array
    {
        $expanded = $relations;

        foreach ($relations as $relation) {
            $dotPosition = strpos($relation, '.');

            if ($dotPosition !== false) {
                $parent = substr($relation, 0, $dotPosition);

                if (! in_array($parent, $expanded, true)) {
                    $expanded[] = $parent;
                }
            }
        }

        return $expanded;
    }

    public static function generateAlias(string $table, int &$counter): string
    {
        $counter++;
        $parts = explode('_', $table);
        $alias = implode('', array_map(fn (string $part): string => $part[0], $parts));

        return $alias.$counter;
    }
}
