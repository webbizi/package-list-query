<?php

declare(strict_types=1);

namespace Webbizi\ListQuery\Dto;

use Webbizi\ListQuery\Filter\ListFilter;
use Webbizi\ListQuery\Sort\ListSort;

final readonly class ListQueryDto
{
    /**
     * @param  array<ListFilter>  $filters
     * @param  array<string>  $relations
     */
    public function __construct(
        public array $filters = [],
        public ?ListSort $sort = null,
        public array $relations = [],
    ) {}

    public function withFilter(ListFilter $filter): self
    {
        return new self(
            filters: [...$this->filters, $filter],
            sort: $this->sort,
            relations: $this->relations,
        );
    }
}
