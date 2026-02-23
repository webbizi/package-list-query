<?php

declare(strict_types=1);

namespace Webbizi\ListQuery\Sql;

use Webbizi\ListQuery\Support\StringHelper;

final readonly class JsonArrayAggBuilder
{
    /**
     * @param  array<int, string>  $columns
     * @param  array<string, string>  $nestedJsonFragments
     */
    public static function build(string $alias, array $columns, array $nestedJsonFragments = []): string
    {
        $escapedAlias = StringHelper::escapeIdentifier($alias);
        $jsonObject = JsonObjectBuilder::build($alias, $columns, $nestedJsonFragments);

        return "JSON_ARRAYAGG(IF({$escapedAlias}.`id` IS NOT NULL, {$jsonObject}, NULL))";
    }
}
