<?php

use Webbizi\ListQuery\Relation\NestedHasMany;

test('it auto-generates table name from camelCase name', function (): void {
    $relation = new NestedHasMany(name: 'sessionFiles', columns: ['id'], foreignKey: 'session_id');

    expect($relation->table)->toBe('session_files');
});

test('it auto-generates table name from simple name', function (): void {
    $relation = new NestedHasMany(name: 'files', columns: ['id'], foreignKey: 'response_id');

    expect($relation->table)->toBe('files');
});

test('it uses custom table when provided', function (): void {
    $relation = new NestedHasMany(name: 'files', columns: ['id'], foreignKey: 'response_id', table: 'uploaded_files');

    expect($relation->table)->toBe('uploaded_files');
});

test('it stores all properties', function (): void {
    $relation = new NestedHasMany(
        name: 'logs',
        columns: ['id', 'message', 'level'],
        foreignKey: 'execution_id',
    );

    expect($relation->name)->toBe('logs');
    expect($relation->columns)->toBe(['id', 'message', 'level']);
    expect($relation->foreignKey)->toBe('execution_id');
});
