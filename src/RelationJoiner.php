<?php

declare(strict_types=1);

namespace Webbizi\ListQuery;

use Illuminate\Database\Query\Builder;
use Webbizi\ListQuery\Config\QueryConfig;
use Webbizi\ListQuery\Relation\AliasGenerator;
use Webbizi\ListQuery\Relation\BelongsToJoiner;
use Webbizi\ListQuery\Relation\HasManyJoiner;

final readonly class RelationJoiner
{
    public function __construct(
        private HasManyJoiner $hasManyJoiner,
        private BelongsToJoiner $belongsToJoiner,
    ) {}

    /**
     * @param  array<string>  $relations
     */
    public function applyRelations(Builder $query, array $relations, QueryConfig $config): void
    {
        $aliasGenerator = new AliasGenerator;

        foreach ($config->hasMany as $relation) {
            if (! in_array($relation->name, $relations, true)) {
                continue;
            }

            $this->hasManyJoiner->join(
                $query,
                $relation,
                $config->table,
                $config->alias,
                $relations,
                $aliasGenerator,
            );
        }

        foreach ($config->belongsTo as $relation) {
            if (! in_array($relation->name, $relations, true)) {
                continue;
            }

            $this->belongsToJoiner->join($query, $relation, $config->alias, $aliasGenerator);
        }
    }
}
