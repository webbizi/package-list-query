<?php

use Webbizi\ListQuery\Hydration\HydratesToScalar;

// Anonymous class using the trait for testing
function createHydrator(): object
{
    return new class
    {
        use HydratesToScalar;

        public function publicToArray(\stdClass $row): array
        {
            return $this->toArray($row);
        }

        public function publicToInt(mixed $value): int
        {
            return $this->toInt($value);
        }

        public function publicToString(mixed $value): string
        {
            return $this->toString($value);
        }

        public function publicToNullableString(mixed $value): ?string
        {
            return $this->toNullableString($value);
        }

        public function publicToNullableInt(mixed $value): ?int
        {
            return $this->toNullableInt($value);
        }

        public function publicParseJson(mixed $value): ?array
        {
            return $this->parseJson($value);
        }

        /** @param  array<string>  $relations */
        public function publicExtractHasMany(\stdClass $row, array $relations, string $relationName): ?array
        {
            return $this->extractHasMany($row, $relations, $relationName);
        }

        /** @param  array<string>  $relations */
        public function publicExtractBelongsTo(\stdClass $row, array $relations, string $relationName): ?array
        {
            return $this->extractBelongsTo($row, $relations, $relationName);
        }

        /** @param  array<string>  $relations */
        public function publicHasNestedRelation(array $relations, string $parent, string $child): bool
        {
            return $this->hasNestedRelation($relations, $parent, $child);
        }

        public function publicParseJsonArrayAgg(string $json): array
        {
            return $this->parseJsonArrayAgg($json);
        }
    };
}

test('toArray converts stdClass to array', function (): void {
    $row = (object) ['id' => 1, 'name' => 'John'];

    expect(createHydrator()->publicToArray($row))->toBe(['id' => 1, 'name' => 'John']);
});

test('toInt casts int values', function (): void {
    $hydrator = createHydrator();

    expect($hydrator->publicToInt(42))->toBe(42);
    expect($hydrator->publicToInt('42'))->toBe(42);
    expect($hydrator->publicToInt(42.9))->toBe(42);
});

test('toInt throws on non-castable types', function (): void {
    createHydrator()->publicToInt(null);
})->throws(RuntimeException::class);

test('toString casts string values', function (): void {
    $hydrator = createHydrator();

    expect($hydrator->publicToString('hello'))->toBe('hello');
    expect($hydrator->publicToString(42))->toBe('42');
    expect($hydrator->publicToString(3.14))->toBe('3.14');
});

test('toString throws on non-castable types', function (): void {
    createHydrator()->publicToString(null);
})->throws(RuntimeException::class);

test('toNullableString handles null', function (): void {
    $hydrator = createHydrator();

    expect($hydrator->publicToNullableString(null))->toBeNull();
    expect($hydrator->publicToNullableString('hello'))->toBe('hello');
});

test('toNullableInt handles null', function (): void {
    $hydrator = createHydrator();

    expect($hydrator->publicToNullableInt(null))->toBeNull();
    expect($hydrator->publicToNullableInt(42))->toBe(42);
});

test('parseJson parses JSON string', function (): void {
    $hydrator = createHydrator();

    expect($hydrator->publicParseJson('{"name":"John"}'))->toBe(['name' => 'John']);
    expect($hydrator->publicParseJson(null))->toBeNull();
    expect($hydrator->publicParseJson(['already' => 'array']))->toBe(['already' => 'array']);
});

test('extractHasMany returns null when relation not requested', function (): void {
    $row = (object) ['posts_json' => '[{"id":1}]'];

    expect(createHydrator()->publicExtractHasMany($row, [], 'posts'))->toBeNull();
});

test('extractHasMany returns parsed data when relation requested', function (): void {
    $row = (object) ['posts_json' => '[{"id":1,"title":"Post 1"}]'];

    $result = createHydrator()->publicExtractHasMany($row, ['posts'], 'posts');

    expect($result)->toBe([['id' => 1, 'title' => 'Post 1']]);
});

test('extractBelongsTo returns null when relation not requested', function (): void {
    $row = (object) ['role_json' => '{"id":1}'];

    expect(createHydrator()->publicExtractBelongsTo($row, [], 'role'))->toBeNull();
});

test('extractBelongsTo returns parsed data when relation requested', function (): void {
    $row = (object) ['role_json' => '{"id":1,"name":"Admin"}'];

    $result = createHydrator()->publicExtractBelongsTo($row, ['role'], 'role');

    expect($result)->toBe(['id' => 1, 'name' => 'Admin']);
});

test('hasNestedRelation checks parent.child format', function (): void {
    $hydrator = createHydrator();

    expect($hydrator->publicHasNestedRelation(['posts.comments'], 'posts', 'comments'))->toBeTrue();
    expect($hydrator->publicHasNestedRelation(['posts'], 'posts', 'comments'))->toBeFalse();
});

test('parseJsonArrayAgg deduplicates by id', function (): void {
    $json = '[{"id":1,"name":"A"},{"id":1,"name":"A"},{"id":2,"name":"B"}]';

    $result = createHydrator()->publicParseJsonArrayAgg($json);

    expect($result)->toHaveCount(2);
    expect($result[0]['id'])->toBe(1);
    expect($result[1]['id'])->toBe(2);
});

test('parseJsonArrayAgg filters null entries', function (): void {
    $json = '[{"id":1},null,{"id":2}]';

    $result = createHydrator()->publicParseJsonArrayAgg($json);

    expect($result)->toHaveCount(2);
});
