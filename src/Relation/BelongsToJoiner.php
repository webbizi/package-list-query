<?php

declare(strict_types=1);

namespace Webbizi\ListQuery\Relation;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Webbizi\ListQuery\Sql\JsonObjectBuilder;
use Webbizi\ListQuery\Support\StringHelper;

final readonly class BelongsToJoiner
{
    public function join(
        Builder $query,
        BelongsTo $relation,
        string $parentAlias,
        AliasGenerator $aliasGenerator,
    ): void {
        $alias = $aliasGenerator->generate($relation->table);

        $query->leftJoin(
            "{$relation->table} as {$alias}",
            "{$parentAlias}.{$relation->foreignKey}",
            '=',
            "{$alias}.id",
        );

        $jsonObject = JsonObjectBuilder::build($alias, $relation->columns);
        $jsonColumnName = StringHelper::toSnakeCase($relation->name).'_json';

        $query->addSelect(DB::raw(
            "IF({$alias}.id IS NOT NULL, {$jsonObject}, NULL) as {$jsonColumnName}"
        ));
    }

    /**
     * @param  array<string>  $requestedRelations
     * @return array<string, string>
     */
    public function joinNested(
        Builder $query,
        HasMany $parentRelation,
        string $parentAlias,
        array $requestedRelations,
        AliasGenerator $aliasGenerator,
    ): array {
        $nestedJsonFragments = [];

        foreach ($parentRelation->nested as $nestedRelation) {
            $nestedFullName = "{$parentRelation->name}.{$nestedRelation->name}";

            if (! in_array($nestedFullName, $requestedRelations, true)) {
                $nestedJsonFragments[$nestedRelation->name] = 'NULL';

                continue;
            }

            $nestedAlias = $aliasGenerator->generate($nestedRelation->table);

            $query->leftJoin(
                "{$nestedRelation->table} as {$nestedAlias}",
                "{$parentAlias}.{$nestedRelation->foreignKey}",
                '=',
                "{$nestedAlias}.id",
            );

            $nestedJsonObject = JsonObjectBuilder::build($nestedAlias, $nestedRelation->columns);
            $nestedJsonFragments[$nestedRelation->name] = "IF({$nestedAlias}.id IS NOT NULL, {$nestedJsonObject}, NULL)";
        }

        return $nestedJsonFragments;
    }
}
