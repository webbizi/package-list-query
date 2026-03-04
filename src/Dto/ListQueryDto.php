<?php

declare(strict_types=1);

namespace Webbizi\ListQuery\Dto;

use Webbizi\ListQuery\Filter\ListFilter;
use Webbizi\ListQuery\Pagination\PaginationConfig;
use Webbizi\ListQuery\Sort\ListSort;

final readonly class ListQueryDto
{
    /**
     * @param  list<ListFilter>  $filters
     * @param  list<string>  $relations
     */
    public function __construct(
        public array $filters = [],
        public ?ListSort $sort = null,
        public array $relations = [],
        public ?PaginationConfig $pagination = null,
    ) {}

    public function withFilter(ListFilter $filter): self
    {
        return new self(
            filters: [...$this->filters, $filter],
            sort: $this->sort,
            relations: $this->relations,
            pagination: $this->pagination,
        );
    }
}
