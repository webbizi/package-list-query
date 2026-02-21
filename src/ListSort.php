<?php

declare(strict_types=1);

namespace Webbizi\ListQuery;

final readonly class ListSort
{
    public function __construct(
        public string $field,
        public string $direction = 'asc',
    ) {}
}
