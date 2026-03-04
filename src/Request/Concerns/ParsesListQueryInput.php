<?php

declare(strict_types=1);

namespace Webbizi\ListQuery\Request\Concerns;

use Webbizi\ListQuery\Config\QueryConfigurable;
use Webbizi\ListQuery\Filter\InvalidFilterException;
use Webbizi\ListQuery\Filter\ListFilter;
use Webbizi\ListQuery\ListQueryConfigExtractor;
use Webbizi\ListQuery\Pagination\PaginationConfig;
use Webbizi\ListQuery\Sort\ListSort;

/**
 * @mixin \Illuminate\Foundation\Http\FormRequest
 */
trait ParsesListQueryInput
{
    /**
     * @return class-string<QueryConfigurable>
     */
    abstract protected function repositoryClass(): string;

    protected function prepareForValidation(): void
    {
        $with = $this->input('with');

        if (is_string($with)) {
            $this->merge([
                'with' => array_filter(explode(',', $with)),
            ]);
        }
    }

    /**
     * @return array{allowedFilters: array<string>, allowedSorts: array<string>, allowedRelations: array<string>}
     */
    protected function allowedConfig(): array
    {
        return ListQueryConfigExtractor::extract($this->repositoryClass());
    }

    /**
     * @return list<ListFilter>
     *
     * @throws InvalidFilterException
     */
    protected function parseFilters(): array
    {
        /** @var array<string, string> $filters */
        $filters = $this->input('filters', []);

        return array_values(
            collect($filters)
                ->map(fn (string $expression, string $field): ListFilter => ListFilter::fromString($field, $expression))
                ->all(),
        );
    }

    protected function parseSort(): ?ListSort
    {
        /** @var string|null $sort */
        $sort = $this->input('sort');

        if ($sort === null) {
            return null;
        }

        /** @var string $direction */
        $direction = $this->input('direction', ListSort::DIRECTION_ASC);

        return new ListSort($sort, $direction);
    }

    /**
     * @return list<string>
     */
    protected function parseRelations(): array
    {
        /** @var array<string> $relations */
        $relations = $this->input('with', []);

        return array_values($relations);
    }

    protected function parsePagination(): ?PaginationConfig
    {
        /** @var int|string|null $page */
        $page = $this->input('page');

        /** @var int|string|null $perPage */
        $perPage = $this->input('per_page');

        if ($page === null && $perPage === null) {
            return null;
        }

        return new PaginationConfig(
            page: $page !== null ? (int) $page : 1,
            perPage: $perPage !== null ? (int) $perPage : PaginationConfig::DEFAULT_PER_PAGE,
        );
    }
}
