<?php

declare(strict_types=1);

namespace Webbizi\ListQuery\Filter;

enum FilterOperator: string
{
    case EQ = 'eq';
    case NEQ = 'neq';
    case GT = 'gt';
    case GTE = 'gte';
    case LT = 'lt';
    case LTE = 'lte';
    case STARTS_WITH = 'starts_with';
    case ENDS_WITH = 'ends_with';
    case CONTAINS = 'contains';
    case NULL = 'null';
    case NOT_NULL = 'not_null';

    public function isValueless(): bool
    {
        return in_array($this, [self::NULL, self::NOT_NULL], true);
    }
}
