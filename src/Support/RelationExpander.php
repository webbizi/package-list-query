<?php

declare(strict_types=1);

namespace Webbizi\ListQuery\Support;

final readonly class RelationExpander
{
    /**
     * @param  array<string>  $relations
     * @return array<string>
     */
    public static function expand(array $relations): array
    {
        $expanded = $relations;

        foreach ($relations as $relation) {
            $dotPosition = strpos($relation, '.');

            if ($dotPosition !== false) {
                $parent = substr($relation, 0, $dotPosition);

                if (! in_array($parent, $expanded, true)) {
                    $expanded[] = $parent;
                }
            }
        }

        return $expanded;
    }
}
