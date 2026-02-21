<?php

declare(strict_types=1);

namespace Webbizi\ListQuery\Filter;

use Illuminate\Database\Query\Builder;
use Webbizi\ListQuery\Config\QueryConfig;

final readonly class FilterApplier
{
    /**
     * @param  array<ListFilter>  $filters
     */
    public function apply(Builder $query, array $filters, QueryConfig $config): void
    {
        foreach ($filters as $filter) {
            if (! in_array($filter->field, $config->allowedFilters, true)) {
                continue;
            }

            $this->applyFilter($query, $filter, $config->alias);
        }
    }

    private function applyFilter(Builder $query, ListFilter $filter, string $tableAlias): void
    {
        $field = "{$tableAlias}.{$filter->field}";

        match ($filter->operator) {
            FilterOperator::EQ => $query->where($field, '=', $filter->value),
            FilterOperator::NEQ => $query->where($field, '!=', $filter->value),
            FilterOperator::GT => $query->where($field, '>', $filter->value),
            FilterOperator::GTE => $query->where($field, '>=', $filter->value),
            FilterOperator::LT => $query->where($field, '<', $filter->value),
            FilterOperator::LTE => $query->where($field, '<=', $filter->value),
            FilterOperator::STARTS_WITH => $query->where($field, 'LIKE', LikeWildcardEscaper::escape($filter->value ?? '').'%'),
            FilterOperator::ENDS_WITH => $query->where($field, 'LIKE', '%'.LikeWildcardEscaper::escape($filter->value ?? '')),
            FilterOperator::CONTAINS => $query->where($field, 'LIKE', '%'.LikeWildcardEscaper::escape($filter->value ?? '').'%'),
            FilterOperator::NULL => $query->whereNull($field),
            FilterOperator::NOT_NULL => $query->whereNotNull($field),
        };
    }
}
