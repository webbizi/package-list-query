<?php

declare(strict_types=1);

namespace Webbizi\ListQuery\Pagination;

final readonly class PaginationConfig
{
    public const int DEFAULT_PER_PAGE = 15;

    public const int MAX_PER_PAGE = 100;

    public function __construct(
        public int $page = 1,
        public int $perPage = self::DEFAULT_PER_PAGE,
    ) {}
}
