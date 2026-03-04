<?php

declare(strict_types=1);

namespace Webbizi\ListQuery;

use Closure;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use stdClass;
use Webbizi\ListQuery\Config\QueryConfig;
use Webbizi\ListQuery\Config\QueryConfigurable;
use Webbizi\ListQuery\Dto\ExistsQueryDto;
use Webbizi\ListQuery\Dto\FindQueryDto;
use Webbizi\ListQuery\Dto\ListQueryDto;
use Webbizi\ListQuery\Filter\FilterApplier;
use Webbizi\ListQuery\Pagination\PaginatedResult;
use Webbizi\ListQuery\Pagination\PaginationConfig;
use Webbizi\ListQuery\Sort\SortApplier;
use Webbizi\ListQuery\Support\RelationExpander;

final readonly class ListQueryBuilder
{
    public function __construct(
        private RelationJoiner $relationJoiner,
        private FilterApplier $filterApplier,
        private SortApplier $sortApplier,
    ) {}

    /**
     * @param  class-string<QueryConfigurable>  $repositoryClass
     */
    public function query(string $repositoryClass, ListQueryDto $dto, string $connection = 'tenant'): Builder
    {
        $config = $this->resolveConfig($repositoryClass);
        $query = $this->buildBaseQuery($config, $dto->relations, $connection);

        $this->filterApplier->apply($query, $dto->filters, $config);
        $this->sortApplier->apply($query, $dto->sort, $config);

        return $query;
    }

    /**
     * @template T
     *
     * @param  class-string<QueryConfigurable>  $repositoryClass
     * @param  Closure(stdClass): T  $hydrator
     * @return list<T>
     */
    public function list(string $repositoryClass, ListQueryDto $dto, Closure $hydrator, string $connection = 'tenant'): array
    {
        return array_values(
            $this->query($repositoryClass, $dto, $connection)
                ->cursor()
                ->map($hydrator)
                ->all(),
        );
    }

    /**
     * @template T
     *
     * @param  class-string<QueryConfigurable>  $repositoryClass
     * @param  Closure(stdClass): T  $hydrator
     * @return PaginatedResult<T>
     */
    public function paginate(string $repositoryClass, ListQueryDto $dto, Closure $hydrator, string $connection = 'tenant'): PaginatedResult
    {
        $config = $this->resolveConfig($repositoryClass);
        $query = $this->buildBaseQuery($config, $dto->relations, $connection);

        $this->filterApplier->apply($query, $dto->filters, $config);
        $this->sortApplier->apply($query, $dto->sort, $config);

        $pagination = $dto->pagination ?? new PaginationConfig;
        $total = $query->getCountForPagination();

        /** @var list<stdClass> $items */
        $items = $query->forPage($pagination->page, $pagination->perPage)->get()->all();

        return new PaginatedResult(
            items: array_map($hydrator, $items),
            total: $total,
            perPage: $pagination->perPage,
            currentPage: $pagination->page,
            lastPage: $total > 0 ? (int) ceil($total / $pagination->perPage) : 1,
        );
    }

    /**
     * @template T
     *
     * @param  class-string<QueryConfigurable>  $repositoryClass
     * @param  Closure(stdClass): T  $hydrator
     * @return T|null
     */
    public function find(string $repositoryClass, int|string $id, FindQueryDto $dto, Closure $hydrator, string $connection = 'tenant'): mixed
    {
        $config = $this->resolveConfig($repositoryClass);
        $query = $this->buildBaseQuery($config, $dto->relations, $connection);

        $row = $query->where("{$config->alias}.id", $id)->first();

        return $row === null ? null : $hydrator($row);
    }

    /**
     * Filters come from internal code (repositories), not from user input, so column names are trusted.
     *
     * @param  class-string<QueryConfigurable>  $repositoryClass
     */
    public function exists(string $repositoryClass, ExistsQueryDto $dto, string $connection = 'tenant'): bool
    {
        $config = $this->resolveConfig($repositoryClass);
        $query = DB::connection($connection)->table($config->table);

        if ($config->softDeletes) {
            $query->whereNull('deleted_at');
        }

        foreach ($dto->filters as $column => $value) {
            $query->where($column, $value);
        }

        return $query->exists();
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
        $relations = RelationExpander::expand($relations);
        $columns = array_map(
            fn (string $column): string => "{$config->alias}.{$column}",
            $config->columns,
        );

        $query = DB::connection($connection)
            ->table("{$config->table} as {$config->alias}")
            ->select($columns)
            ->groupBy($columns);

        if ($config->softDeletes) {
            $query->whereNull("{$config->alias}.deleted_at");
        }

        $this->relationJoiner->applyRelations($query, $relations, $config);

        return $query;
    }
}
