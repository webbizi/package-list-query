<?php

declare(strict_types=1);

namespace Webbizi\ListQuery\Sql;

final class JsonArrayAggBuilder
{
    /**
     * @param  array<int, string>  $columns
     * @param  array<string, string>  $nestedJsonFragments
     */
    public static function build(string $alias, array $columns, array $nestedJsonFragments = []): string
    {
        $jsonObject = JsonObjectBuilder::build($alias, $columns, $nestedJsonFragments);

        return "JSON_ARRAYAGG(IF({$alias}.id IS NOT NULL, {$jsonObject}, NULL))";
    }
}
