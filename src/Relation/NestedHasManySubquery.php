<?php

declare(strict_types=1);

namespace Webbizi\ListQuery\Relation;

final class NestedHasManySubquery
{
    public static function build(NestedHasMany $relation, string $parentAlias): string
    {
        $jsonPairs = array_map(
            fn (string $column): string => "'{$column}', n.{$column}",
            $relation->columns,
        );

        $jsonObject = 'JSON_OBJECT('.implode(', ', $jsonPairs).')';

        return "(SELECT JSON_ARRAYAGG({$jsonObject}) FROM {$relation->table} n WHERE n.{$relation->foreignKey} = {$parentAlias}.id)";
    }
}
