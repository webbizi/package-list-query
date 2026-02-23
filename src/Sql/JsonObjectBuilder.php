<?php

declare(strict_types=1);

namespace Webbizi\ListQuery\Sql;

use Webbizi\ListQuery\Support\StringHelper;

final readonly class JsonObjectBuilder
{
    /**
     * @param  array<int, string>  $columns
     * @param  array<string, string>  $nestedJsonFragments
     */
    public static function build(string $alias, array $columns, array $nestedJsonFragments = []): string
    {
        $escapedAlias = StringHelper::escapeIdentifier($alias);
        $pairs = [];

        foreach ($columns as $column) {
            $escapedColumn = StringHelper::escapeIdentifier($column);
            $pairs[] = "'{$column}', {$escapedAlias}.{$escapedColumn}";
        }

        foreach ($nestedJsonFragments as $nestedName => $jsonFragment) {
            $pairs[] = "'{$nestedName}', {$jsonFragment}";
        }

        return 'JSON_OBJECT('.implode(', ', $pairs).')';
    }
}
