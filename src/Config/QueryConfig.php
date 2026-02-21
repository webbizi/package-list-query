<?php

declare(strict_types=1);

namespace Webbizi\ListQuery\Config;

use Webbizi\ListQuery\Relation\BelongsTo;
use Webbizi\ListQuery\Relation\HasMany;

final readonly class QueryConfig
{
    public string $alias;

    /**
     * @param  list<string>  $columns
     * @param  list<string>  $allowedFilters
     * @param  list<string>  $allowedSorts
     * @param  list<HasMany>  $hasMany
     * @param  list<BelongsTo>  $belongsTo
     */
    public function __construct(
        public string $table,
        public array $columns,
        public array $allowedFilters = [],
        public array $allowedSorts = [],
        public array $hasMany = [],
        public array $belongsTo = [],
        ?string $alias = null,
    ) {
        $this->alias = $alias ?? self::generateAlias($table);
    }

    private static function generateAlias(string $table): string
    {
        $parts = explode('_', $table);

        return implode('', array_map(fn (string $part): string => $part[0], $parts));
    }
}
