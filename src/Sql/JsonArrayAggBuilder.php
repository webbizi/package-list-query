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

        $escapedId = StringHelper::escapeIdentifier('id');

        return "JSON_ARRAYAGG(IF({$escapedAlias}.{$escapedId} IS NOT NULL, {$jsonObject}, NULL))";
    }
}
