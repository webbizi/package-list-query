<?php

declare(strict_types=1);

namespace Webbizi\ListQuery;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use stdClass;
use Webbizi\ListQuery\Config\QueryConfig;
use Webbizi\ListQuery\Config\QueryConfigurable;
use Webbizi\ListQuery\Dto\ExistsQueryDto;
use Webbizi\ListQuery\Dto\FindQueryDto;
use Webbizi\ListQuery\Dto\ListQueryDto;

final readonly class RawQueryApplier
{
    public function __construct(
        private RelationJoiner $relationJoiner,
        private FilterSortApplier $filterSortApplier,
    ) {}

    /**
     * @param  class-string<QueryConfigurable>  $repositoryClass
     * @return array{allowedFilters: array<string>, allowedSorts: array<string>, allowedRelations: array<string>}
     */
    public static function allowedConfig(string $repositoryClass): array
    {
        $config = $repositoryClass::queryConfig();

        return [
            'allowedFilters' => $config->allowedFilters,
            'allowedSorts' => $config->allowedSorts,
            'allowedRelations' => self::getAllowedRelations($config),
        ];
    }

    /**
     * @param  class-string<QueryConfigurable>  $repositoryClass
     */
    public function list(string $repositoryClass, ListQueryDto $dto, string $connection = 'tenant'): Builder
    {
        $config = $this->resolveConfig($repositoryClass);
        $query = $this->buildBaseQuery($config, $dto->relations, $connection);

        $this->filterSortApplier->applyFilters($query, $dto->filters, $config);
        $this->filterSortApplier->applySort($query, $dto->sort, $config);

        return $query;
    }

    /**
     * @param  class-string<QueryConfigurable>  $repositoryClass
     */
    public function find(string $repositoryClass, int|string $id, FindQueryDto $dto, string $connection = 'tenant'): ?stdClass
    {
        $config = $this->resolveConfig($repositoryClass);
        $query = $this->buildBaseQuery($config, $dto->relations, $connection);

        return $query->where("{$config->alias}.id", $id)->first();
    }

    /**
     * @param  class-string<QueryConfigurable>  $repositoryClass
     */
    public function exists(string $repositoryClass, ExistsQueryDto $dto, string $connection = 'tenant'): bool
    {
        $config = $this->resolveConfig($repositoryClass);
        $query = DB::connection($connection)->table($config->table);

        foreach ($dto->filters as $column => $value) {
            $query->where($column, $value);
        }

        return $query->exists();
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

    /**
     * @param  class-string<QueryConfigurable>  $repositoryClass
     */
    private function resolveConfig(string $repositoryClass): QueryConfig
    {
        return $repositoryClass::queryConfig();
    }

    /**
     * @param  array<string>  $relations
     */
    private function buildBaseQuery(QueryConfig $config, array $relations, string $connection): Builder
    {
        $relations = SqlHelper::expandParentRelations($relations);
        $columns = array_map(
            fn (string $column): string => "{$config->alias}.{$column}",
            $config->columns,
        );

        $query = DB::connection($connection)
            ->table("{$config->table} as {$config->alias}")
            ->select($columns)
            ->groupBy($columns);

        $this->relationJoiner->applyRelations($query, $relations, $config);

        return $query;
    }
}
