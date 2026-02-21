<?php

declare(strict_types=1);

namespace Webbizi\ListQuery\Relation;

use Webbizi\ListQuery\Support\StringHelper;

/**
 * Defines a HasMany relation for list queries.
 *
 * Used to describe how to JOIN and select data for a hasMany relation,
 * generating JSON_ARRAYAGG with JSON_OBJECT for aggregation.
 *
 * Table and foreignKey are auto-generated from name if not provided.
 */
final readonly class HasMany
{
    public string $table;

    public string $foreignKey;

    /**
     * @param  string  $name  Relation name (e.g., 'questions', 'transformationTreatments')
     * @param  array<int, string>  $columns  Columns to include in JSON_OBJECT
     * @param  array<int, BelongsTo>  $nested  Nested BelongsTo relations
     * @param  array<int, NestedHasMany>  $nestedHasMany  Nested HasMany relations (e.g., responses.files)
     * @param  string|null  $table  Database table name (auto-generated from name if null)
     * @param  string|null  $foreignKey  Foreign key (resolved at query time from parent table if null)
     */
    public function __construct(
        public string $name,
        public array $columns,
        public array $nested = [],
        public array $nestedHasMany = [],
        ?string $table = null,
        ?string $foreignKey = null,
    ) {
        $this->table = $table ?? StringHelper::toSnakeCase($name);
        $this->foreignKey = $foreignKey ?? '';  // Resolved at query time from parent table
    }

    /**
     * Resolve foreign key from parent table name.
     * e.g., "questionnaires" -> "questionnaire_id"
     */
    public function resolveForeignKey(string $parentTable): string
    {
        if ($this->foreignKey !== '') {
            return $this->foreignKey;
        }

        return StringHelper::toSingular($parentTable).'_id';
    }
}
