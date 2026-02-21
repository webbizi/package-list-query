<?php

declare(strict_types=1);

namespace Webbizi\ListQuery\Relation;

final class AliasGenerator
{
    private int $counter = 0;

    public function generate(string $table): string
    {
        $this->counter++;
        $parts = explode('_', $table);
        $alias = implode('', array_map(fn (string $part): string => $part[0], $parts));

        return $alias.$this->counter;
    }
}
