<?php

declare(strict_types=1);

namespace Webbizi\ListQuery\Dto;

final readonly class ExistsQueryDto
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function __construct(
        public array $filters = [],
    ) {}
}
