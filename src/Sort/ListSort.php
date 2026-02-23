<?php

declare(strict_types=1);

namespace Webbizi\ListQuery\Sort;

final readonly class ListSort
{
    public const string DIRECTION_ASC = 'asc';

    public const string DIRECTION_DESC = 'desc';

    public function __construct(
        public string $field,
        public string $direction = self::DIRECTION_ASC,
    ) {}
}
