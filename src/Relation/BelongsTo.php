<?php

declare(strict_types=1);

namespace Webbizi\ListQuery\Relation;

use Webbizi\ListQuery\Support\StringHelper;

/**
 * Defines a BelongsTo relation for list queries (nested relation).
 *
 * Used to describe how to JOIN and select data for a belongsTo relation
 * that is nested inside a HasMany relation.
 *
 * Table and foreignKey are auto-generated from name if not provided.
 */
final readonly class BelongsTo
{
    public string $table;

    public string $foreignKey;

    /**
     * @param  string  $name  Relation name (e.g., 'integration')
     * @param  array<int, string>  $columns  Columns to include in JSON_OBJECT
     * @param  string|null  $table  Database table name (auto-generated from name if null)
     * @param  string|null  $foreignKey  Foreign key (auto-generated from name if null)
     */
    public function __construct(
        public string $name,
        public array $columns,
        ?string $table = null,
        ?string $foreignKey = null,
    ) {
        $this->table = $table ?? StringHelper::toPlural($name);
        $this->foreignKey = $foreignKey ?? $name.'_id';
    }
}
