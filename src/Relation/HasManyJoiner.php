<?php

declare(strict_types=1);

namespace Webbizi\ListQuery\Relation;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Webbizi\ListQuery\Sql\JsonObjectBuilder;
use Webbizi\ListQuery\Support\StringHelper;

final readonly class HasManyJoiner
{
    public function __construct(
        private BelongsToJoiner $belongsToJoiner,
    ) {}

    /**
     * @param  array<string>  $requestedRelations
     */
    public function join(
        Builder $query,
        HasMany $relation,
        string $parentTable,
        string $parentAlias,
        array $requestedRelations,
        AliasGenerator $aliasGenerator,
    ): void {
        $alias = $aliasGenerator->generate($relation->table);
        $foreignKey = $relation->resolveForeignKey($parentTable);

        $query->leftJoin(
            "{$relation->table} as {$alias}",
            "{$parentAlias}.id",
            '=',
            "{$alias}.{$foreignKey}",
        );

        $nestedJsonFragments = $this->processNestedRelations(
            $query,
            $relation,
            $alias,
            $requestedRelations,
            $aliasGenerator,
        );

        $jsonObject = JsonObjectBuilder::build($alias, $relation->columns, $nestedJsonFragments);
        $jsonColumnName = StringHelper::toSnakeCase($relation->name).'_json';

        $query->addSelect(DB::raw(
            "JSON_ARRAYAGG(IF({$alias}.id IS NOT NULL, {$jsonObject}, NULL)) as {$jsonColumnName}"
        ));
    }

    /**
     * @param  array<string>  $requestedRelations
     * @return array<string, string>
     */
    private function processNestedRelations(
        Builder $query,
        HasMany $relation,
        string $parentAlias,
        array $requestedRelations,
        AliasGenerator $aliasGenerator,
    ): array {
        $nestedJsonFragments = $this->belongsToJoiner->joinNested(
            $query,
            $relation,
            $parentAlias,
            $requestedRelations,
            $aliasGenerator,
        );

        foreach ($relation->nestedHasMany as $nestedHasMany) {
            $nestedFullName = "{$relation->name}.{$nestedHasMany->name}";

            if (! in_array($nestedFullName, $requestedRelations, true)) {
                $nestedJsonFragments[$nestedHasMany->name] = 'NULL';

                continue;
            }

            $nestedJsonFragments[$nestedHasMany->name] = NestedHasManySubquery::build(
                $nestedHasMany,
                $parentAlias,
            );
        }

        return $nestedJsonFragments;
    }
}
