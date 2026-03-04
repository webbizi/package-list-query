<?php

use Webbizi\ListQuery\Config\QueryConfig;
use Webbizi\ListQuery\Relation\BelongsTo;
use Webbizi\ListQuery\Relation\HasMany;

test('it auto-generates alias from table name', function (): void {
    $config = new QueryConfig(
        table: 'users',
        columns: ['id', 'name'],
    );

    expect($config->alias)->toBe('u');
});

test('it auto-generates alias from multi-word table name', function (): void {
    $config = new QueryConfig(
        table: 'questionnaire_sessions',
        columns: ['id'],
    );

    expect($config->alias)->toBe('qs');
});

test('it uses custom alias when provided', function (): void {
    $config = new QueryConfig(
        table: 'users',
        columns: ['id'],
        alias: 'custom',
    );

    expect($config->alias)->toBe('custom');
});

test('it stores all configuration', function (): void {
    $hasMany = [new HasMany(name: 'posts', columns: ['id', 'title'])];
    $belongsTo = [new BelongsTo(name: 'role', columns: ['id', 'name'])];

    $config = new QueryConfig(
        table: 'users',
        columns: ['id', 'name', 'email'],
        allowedFilters: ['name', 'email'],
        allowedSorts: ['name', 'created_at'],
        hasMany: $hasMany,
        belongsTo: $belongsTo,
    );

    expect($config->table)->toBe('users');
    expect($config->columns)->toBe(['id', 'name', 'email']);
    expect($config->allowedFilters)->toBe(['name', 'email']);
    expect($config->allowedSorts)->toBe(['name', 'created_at']);
    expect($config->hasMany)->toBe($hasMany);
    expect($config->belongsTo)->toBe($belongsTo);
});

test('it has empty defaults for optional parameters', function (): void {
    $config = new QueryConfig(
        table: 'items',
        columns: ['id'],
    );

    expect($config->allowedFilters)->toBe([]);
    expect($config->allowedSorts)->toBe([]);
    expect($config->hasMany)->toBe([]);
    expect($config->belongsTo)->toBe([]);
    expect($config->softDeletes)->toBeFalse();
});

test('it enables soft deletes', function (): void {
    $config = new QueryConfig(
        table: 'users',
        columns: ['id'],
        softDeletes: true,
    );

    expect($config->softDeletes)->toBeTrue();
});

test('soft deletes works with custom alias', function (): void {
    $config = new QueryConfig(
        table: 'users',
        columns: ['id'],
        softDeletes: true,
        alias: 'u',
    );

    expect($config->softDeletes)->toBeTrue();
    expect($config->alias)->toBe('u');
});
