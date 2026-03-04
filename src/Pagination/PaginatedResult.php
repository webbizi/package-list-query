<?php

declare(strict_types=1);

namespace Webbizi\ListQuery\Pagination;

use Closure;

/**
 * @template T
 */
final readonly class PaginatedResult
{
    /**
     * @param  list<T>  $items
     */
    public function __construct(
        public array $items,
        public int $total,
        public int $perPage,
        public int $currentPage,
        public int $lastPage,
    ) {}

    /**
     * @template U
     *
     * @param  Closure(T): U  $callback
     * @return PaginatedResult<U>
     */
    public function map(Closure $callback): self
    {
        return new self(
            items: array_map($callback, $this->items),
            total: $this->total,
            perPage: $this->perPage,
            currentPage: $this->currentPage,
            lastPage: $this->lastPage,
        );
    }
}
