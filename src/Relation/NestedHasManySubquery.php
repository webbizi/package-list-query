<?php

declare(strict_types=1);

namespace Webbizi\ListQuery\Relation;

use Webbizi\ListQuery\Support\StringHelper;

final readonly class NestedHasManySubquery
{
    public static function build(NestedHasMany $relation, string $parentAlias): string
    {
        $escapedParentAlias = StringHelper::escapeIdentifier($parentAlias);
        $escapedTable = StringHelper::escapeIdentifier($relation->table);
        $escapedForeignKey = StringHelper::escapeIdentifier($relation->foreignKey);
        $escapedId = StringHelper::escapeIdentifier('id');
        $escapedSubAlias = StringHelper::escapeIdentifier('n');

        $jsonPairs = array_map(
            fn (string $column): string => "'{$column}', {$escapedSubAlias}.".StringHelper::escapeIdentifier($column),
            $relation->columns,
        );

        $jsonObject = 'JSON_OBJECT('.implode(', ', $jsonPairs).')';

        return "(SELECT JSON_ARRAYAGG({$jsonObject}) FROM {$escapedTable} {$escapedSubAlias} WHERE {$escapedSubAlias}.{$escapedForeignKey} = {$escapedParentAlias}.{$escapedId})";
    }
}
