<?php

declare(strict_types=1);

namespace Webbizi\ListQuery\Relation;

use Webbizi\ListQuery\Support\StringHelper;

/**
 * Defines a nested HasMany relation for list queries.
 *
 * Used to describe how to load nested HasMany relations (e.g., responses.files)
 * using a correlated subquery with JSON_ARRAYAGG.
 */
final readonly class NestedHasMany
{
    public string $table;

    /**
     * @param  string  $name  Relation name (e.g., 'files', 'logs')
     * @param  array<int, string>  $columns  Columns to include in JSON_OBJECT
     * @param  string  $foreignKey  Foreign key in the nested table (e.g., 'response_id')
     * @param  string|null  $table  Database table name (auto-generated from name if null)
     */
    public function __construct(
        public string $name,
        public array $columns,
        public string $foreignKey,
        ?string $table = null,
    ) {
        $this->table = $table ?? StringHelper::toSnakeCase($name);
    }
}
