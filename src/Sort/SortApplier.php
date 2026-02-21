<?php

declare(strict_types=1);

namespace Webbizi\ListQuery\Sort;

use Illuminate\Database\Query\Builder;
use Webbizi\ListQuery\Config\QueryConfig;

final readonly class SortApplier
{
    public function apply(Builder $query, ?ListSort $sort, QueryConfig $config): void
    {
        if ($sort === null) {
            return;
        }

        if (! in_array($sort->field, $config->allowedSorts, true)) {
            return;
        }

        $query->orderBy("{$config->alias}.{$sort->field}", $sort->direction);
    }
}
