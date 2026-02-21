<?php

declare(strict_types=1);

namespace Webbizi\ListQuery;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Webbizi\ListQuery\Config\QueryConfig;
use Webbizi\ListQuery\Relation\BelongsTo;
use Webbizi\ListQuery\Relation\HasMany;
use Webbizi\ListQuery\Relation\NestedHasMany;

final readonly class RelationJoiner
{
    private const int ALIAS_COUNTER_START = 0;

    private const int BELONGS_TO_ALIAS_OFFSET = 100;

    /**
     * @param  array<string>  $relations
     */
    public function applyRelations(Builder $query, array $relations, QueryConfig $config): void
    {
        $hasManyRelations = [];

        foreach ($config->hasMany as $relation) {
            $hasManyRelations[$relation->name] = $relation;
        }

        $belongsToRelations = [];

        foreach ($config->belongsTo as $relation) {
            $belongsToRelations[$relation->name] = $relation;
        }

        $this->applyHasManyRelations($query, $relations, $hasManyRelations, $config->table, $config->alias);
        $this->applyBelongsToRelations($query, $relations, $belongsToRelations, $config->alias);
    }

    /**
     * @param  array<string>  $requestedRelations
     * @param  array<string, HasMany>  $availableRelations
     */
    private function applyHasManyRelations(
        Builder $query,
        array $requestedRelations,
        array $availableRelations,
        string $parentTable,
        string $parentAlias,
    ): void {
        $aliasCounter = self::ALIAS_COUNTER_START;

        foreach ($availableRelations as $relationName => $relation) {
            if (! in_array($relationName, $requestedRelations, true)) {
                continue;
            }

            $this->applyHasManyRelation(
                $query,
                $relation,
                $parentTable,
                $parentAlias,
                $requestedRelations,
                $aliasCounter,
            );
        }
    }

    /**
     * @param  array<string>  $requestedRelations
     * @param  array<string, BelongsTo>  $availableRelations
     */
    private function applyBelongsToRelations(
        Builder $query,
        array $requestedRelations,
        array $availableRelations,
        string $parentAlias,
    ): void {
        $aliasCounter = self::BELONGS_TO_ALIAS_OFFSET;

        foreach ($availableRelations as $relationName => $relation) {
            if (! in_array($relationName, $requestedRelations, true)) {
                continue;
            }

            $this->applyBelongsToRelation($query, $relation, $parentAlias, $aliasCounter);
        }
    }

    private function applyBelongsToRelation(
        Builder $query,
        BelongsTo $relation,
        string $parentAlias,
        int &$aliasCounter,
    ): void {
        $alias = SqlHelper::generateAlias($relation->table, $aliasCounter);

        $query->leftJoin(
            "{$relation->table} as {$alias}",
            "{$parentAlias}.{$relation->foreignKey}",
            '=',
            "{$alias}.id",
        );

        $jsonObject = SqlHelper::buildJsonObject($alias, $relation->columns, []);
        $jsonColumnName = SqlHelper::toSnakeCase($relation->name).'_json';

        $query->addSelect(DB::raw(
            "IF({$alias}.id IS NOT NULL, {$jsonObject}, NULL) as {$jsonColumnName}"
        ));
    }

    /**
     * @param  array<string>  $requestedRelations
     */
    private function applyHasManyRelation(
        Builder $query,
        HasMany $relation,
        string $parentTable,
        string $parentAlias,
        array $requestedRelations,
        int &$aliasCounter,
    ): void {
        $alias = SqlHelper::generateAlias($relation->table, $aliasCounter);
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
            $aliasCounter,
        );

        $jsonObject = SqlHelper::buildJsonObject($alias, $relation->columns, $nestedJsonFragments);
        $jsonColumnName = SqlHelper::toSnakeCase($relation->name).'_json';

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
        int &$aliasCounter,
    ): array {
        $nestedJsonFragments = [];

        foreach ($relation->nested as $nestedRelation) {
            $nestedFullName = "{$relation->name}.{$nestedRelation->name}";

            if (! in_array($nestedFullName, $requestedRelations, true)) {
                $nestedJsonFragments[$nestedRelation->name] = 'NULL';

                continue;
            }

            $nestedAlias = SqlHelper::generateAlias($nestedRelation->table, $aliasCounter);

            $query->leftJoin(
                "{$nestedRelation->table} as {$nestedAlias}",
                "{$parentAlias}.{$nestedRelation->foreignKey}",
                '=',
                "{$nestedAlias}.id",
            );

            $nestedJsonObject = SqlHelper::buildJsonObject($nestedAlias, $nestedRelation->columns, []);
            $nestedJsonFragments[$nestedRelation->name] = "IF({$nestedAlias}.id IS NOT NULL, {$nestedJsonObject}, NULL)";
        }

        foreach ($relation->nestedHasMany as $nestedHasMany) {
            $nestedFullName = "{$relation->name}.{$nestedHasMany->name}";

            if (! in_array($nestedFullName, $requestedRelations, true)) {
                $nestedJsonFragments[$nestedHasMany->name] = 'NULL';

                continue;
            }

            $nestedJsonFragments[$nestedHasMany->name] = $this->buildNestedHasManySubquery(
                $nestedHasMany,
                $parentAlias,
            );
        }

        return $nestedJsonFragments;
    }

    private function buildNestedHasManySubquery(NestedHasMany $relation, string $parentAlias): string
    {
        $jsonPairs = array_map(
            fn (string $column): string => "'{$column}', n.{$column}",
            $relation->columns,
        );

        $jsonObject = 'JSON_OBJECT('.implode(', ', $jsonPairs).')';

        return "(SELECT JSON_ARRAYAGG({$jsonObject}) FROM {$relation->table} n WHERE n.{$relation->foreignKey} = {$parentAlias}.id)";
    }
}
