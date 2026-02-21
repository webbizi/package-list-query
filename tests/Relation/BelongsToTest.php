<?php

use Webbizi\ListQuery\Relation\BelongsTo;

test('it auto-generates table name from singular name', function (): void {
    $relation = new BelongsTo(name: 'integration', columns: ['id', 'name']);

    expect($relation->table)->toBe('integrations');
});

test('it auto-generates foreign key from name', function (): void {
    $relation = new BelongsTo(name: 'integration', columns: ['id']);

    expect($relation->foreignKey)->toBe('integration_id');
});

test('it uses custom table when provided', function (): void {
    $relation = new BelongsTo(name: 'author', columns: ['id'], table: 'users');

    expect($relation->table)->toBe('users');
});

test('it uses custom foreign key when provided', function (): void {
    $relation = new BelongsTo(name: 'author', columns: ['id'], foreignKey: 'created_by');

    expect($relation->foreignKey)->toBe('created_by');
});

test('it pluralizes words ending in y', function (): void {
    $relation = new BelongsTo(name: 'company', columns: ['id']);

    expect($relation->table)->toBe('companies');
});

test('it pluralizes words ending in s', function (): void {
    $relation = new BelongsTo(name: 'status', columns: ['id']);

    expect($relation->table)->toBe('statuses');
});

test('it stores columns', function (): void {
    $relation = new BelongsTo(name: 'role', columns: ['id', 'name', 'slug']);

    expect($relation->columns)->toBe(['id', 'name', 'slug']);
});
