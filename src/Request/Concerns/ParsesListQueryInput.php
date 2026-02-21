<?php

declare(strict_types=1);

namespace Webbizi\ListQuery\Request\Concerns;

use Webbizi\ListQuery\Config\QueryConfigurable;
use Webbizi\ListQuery\Filter\ListFilter;
use Webbizi\ListQuery\ListQueryConfigExtractor;
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
     * @return array<ListFilter>
     */
    protected function parseFilters(): array
    {
        /** @var array<string, string> $filters */
        $filters = $this->input('filters', []);

        return collect($filters)
            ->map(fn (string $expression, string $field): ListFilter => ListFilter::fromString($field, $expression))
            ->all();
    }

    protected function parseSort(): ?ListSort
    {
        /** @var string|null $sort */
        $sort = $this->input('sort');

        if ($sort === null) {
            return null;
        }

        /** @var string $direction */
        $direction = $this->input('direction', 'asc');

        return new ListSort($sort, $direction);
    }

    /**
     * @return array<string>
     */
    protected function parseRelations(): array
    {
        /** @var array<string> $relations */
        $relations = $this->input('with', []);

        return $relations;
    }
}
