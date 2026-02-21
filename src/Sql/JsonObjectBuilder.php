<?php

declare(strict_types=1);

namespace Webbizi\ListQuery\Sql;

final class JsonObjectBuilder
{
    /**
     * @param  array<int, string>  $columns
     * @param  array<string, string>  $nestedJsonFragments
     */
    public static function build(string $alias, array $columns, array $nestedJsonFragments = []): string
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
}
