<?php

declare(strict_types=1);

namespace Webbizi\ListQuery\Pagination;

use stdClass;

final readonly class PaginatedResult
{
    /**
     * @param  list<stdClass>  $items
     */
    public function __construct(
        public array $items,
        public int $total,
        public int $perPage,
        public int $currentPage,
        public int $lastPage,
    ) {}
}
