<?php

declare(strict_types=1);

namespace Webbizi\ListQuery\Hydration;

use Illuminate\Support\Str;
use stdClass;

/**
 * Provides type-safe scalar hydration methods for repository readers.
 *
 * These methods handle the conversion of mixed database values (typically from stdClass rows)
 * to their expected PHP types in a safe manner.
 */
trait HydratesToScalar
{
    /**
     * Convert a stdClass to an associative array with proper typing.
     *
     * @return array<string, mixed>
     */
    protected function toArray(stdClass $row): array
    {
        /** @var array<string, mixed> $data */
        $data = (array) $row;

        return $data;
    }

    /**
     * Cast mixed value to int in a type-safe way.
     */
    protected function toInt(mixed $value): int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_string($value) || is_float($value)) {
            return (int) $value;
        }

        throw new \RuntimeException('Cannot cast '.get_debug_type($value).' to int');
    }

    /**
     * Cast mixed value to string in a type-safe way.
     */
    protected function toString(mixed $value): string
    {
        if (is_string($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        throw new \RuntimeException('Cannot cast '.get_debug_type($value).' to string');
    }

    /**
     * Cast mixed value to nullable string in a type-safe way.
     */
    protected function toNullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return $this->toString($value);
    }

    /**
     * Cast mixed value to nullable int in a type-safe way.
     */
    protected function toNullableInt(mixed $value): ?int
    {
        if ($value === null) {
            return null;
        }

        return $this->toInt($value);
    }

    /**
     * Parse a JSON string or array to an associative array.
     *
     * @return array<string, mixed>|null
     */
    protected function parseJson(mixed $value): ?array
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            /** @var array<string, mixed>|null $decoded */
            $decoded = json_decode($value, true);

            return $decoded;
        }

        if (is_array($value)) {
            /** @var array<string, mixed> $value */
            return $value;
        }

        return null;
    }

    /**
     * Extract HasMany relation data if requested.
     *
     * @param  array<string>  $relations
     * @return array<int, array<string, mixed>>|null
     */
    protected function extractHasMany(stdClass $row, array $relations, string $relationName): ?array
    {
        $jsonProperty = Str::snake($relationName).'_json';

        if (! in_array($relationName, $relations, true) || ! isset($row->$jsonProperty)) {
            return null;
        }

        return $this->parseJsonArrayAgg($this->toString($row->$jsonProperty));
    }

    /**
     * Extract BelongsTo relation data if requested.
     *
     * @param  array<string>  $relations
     * @return array<string, mixed>|null
     */
    protected function extractBelongsTo(stdClass $row, array $relations, string $relationName): ?array
    {
        $jsonProperty = Str::snake($relationName).'_json';

        if (! in_array($relationName, $relations, true) || ! isset($row->$jsonProperty)) {
            return null;
        }

        return $this->parseJsonObject($this->toString($row->$jsonProperty));
    }

    /**
     * Check if a nested relation is requested.
     *
     * @param  array<string>  $relations
     */
    protected function hasNestedRelation(array $relations, string $parent, string $child): bool
    {
        return in_array($parent.'.'.$child, $relations, true);
    }

    /**
     * Parse a JSON string to an associative array (for BelongsTo relations).
     *
     * @return array<string, mixed>|null
     */
    protected function parseJsonObject(string $json): ?array
    {
        /** @var array<string, mixed>|null $data */
        $data = json_decode($json, true);

        return $data;
    }

    /**
     * Parse JSON array aggregation, filter nulls, and deduplicate by id.
     *
     * Multiple LEFT JOINs on HasMany relations create cartesian products,
     * so deduplication by id is required.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function parseJsonArrayAgg(string $json): array
    {
        /** @var array<int, array<string, mixed>|null>|null $data */
        $data = json_decode($json, true);

        if ($data === null) {
            return [];
        }

        /** @var array<int|string, true> $seen */
        $seen = [];
        /** @var array<int, array<string, mixed>> $result */
        $result = [];

        foreach ($data as $item) {
            if ($item === null) {
                continue;
            }

            if (isset($item['id'])) {
                /** @var int|string $id */
                $id = $item['id'];

                if (isset($seen[$id])) {
                    continue;
                }

                $seen[$id] = true;
            }

            $result[] = $item;
        }

        return $result;
    }
}
