<?php

declare(strict_types=1);

namespace Webbizi\ListQuery\Dto;

final readonly class FindQueryDto
{
    /**
     * @param  array<string>  $relations
     */
    public function __construct(
        public array $relations = [],
    ) {}
}
