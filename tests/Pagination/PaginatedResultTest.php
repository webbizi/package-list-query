<?php

use Webbizi\ListQuery\Pagination\PaginatedResult;

test('it stores all pagination data', function (): void {
    $items = [(object) ['id' => 1], (object) ['id' => 2]];

    $result = new PaginatedResult(
        items: $items,
        total: 50,
        perPage: 15,
        currentPage: 2,
        lastPage: 4,
    );

    expect($result->items)->toHaveCount(2);
    expect($result->total)->toBe(50);
    expect($result->perPage)->toBe(15);
    expect($result->currentPage)->toBe(2);
    expect($result->lastPage)->toBe(4);
});

test('it handles empty items', function (): void {
    $result = new PaginatedResult(
        items: [],
        total: 0,
        perPage: 15,
        currentPage: 1,
        lastPage: 1,
    );

    expect($result->items)->toBe([]);
    expect($result->total)->toBe(0);
});

test('map transforms items and preserves metadata', function (): void {
    $result = new PaginatedResult(
        items: [(object) ['id' => 1, 'name' => 'Alice'], (object) ['id' => 2, 'name' => 'Bob']],
        total: 50,
        perPage: 15,
        currentPage: 2,
        lastPage: 4,
    );

    $mapped = $result->map(fn (stdClass $row): string => $row->name);

    expect($mapped->items)->toBe(['Alice', 'Bob']);
    expect($mapped->total)->toBe(50);
    expect($mapped->perPage)->toBe(15);
    expect($mapped->currentPage)->toBe(2);
    expect($mapped->lastPage)->toBe(4);
});

test('map on empty result returns empty result', function (): void {
    $result = new PaginatedResult(
        items: [],
        total: 0,
        perPage: 15,
        currentPage: 1,
        lastPage: 1,
    );

    $mapped = $result->map(fn (stdClass $row): string => $row->name);

    expect($mapped->items)->toBe([]);
    expect($mapped->total)->toBe(0);
    expect($mapped->perPage)->toBe(15);
    expect($mapped->currentPage)->toBe(1);
    expect($mapped->lastPage)->toBe(1);
});
