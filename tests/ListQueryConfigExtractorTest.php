<?php

use Webbizi\ListQuery\Config\QueryConfig;
use Webbizi\ListQuery\Config\QueryConfigurable;
use Webbizi\ListQuery\ListQueryConfigExtractor;
use Webbizi\ListQuery\Relation\BelongsTo;
use Webbizi\ListQuery\Relation\HasMany;
use Webbizi\ListQuery\Relation\NestedHasMany;

test('it extracts allowed filters and sorts', function (): void {
    $repository = new class implements QueryConfigurable
    {
        public static function queryConfig(): QueryConfig
        {
            return new QueryConfig(
                table: 'users',
                columns: ['id', 'name'],
                allowedFilters: ['name', 'email'],
                allowedSorts: ['name', 'created_at'],
            );
        }
    };

    $config = ListQueryConfigExtractor::extract($repository::class);

    expect($config['allowedFilters'])->toBe(['name', 'email']);
    expect($config['allowedSorts'])->toBe(['name', 'created_at']);
    expect($config['allowedRelations'])->toBe([]);
});

test('it extracts hasMany relations', function (): void {
    $repository = new class implements QueryConfigurable
    {
        public static function queryConfig(): QueryConfig
        {
            return new QueryConfig(
                table: 'questionnaires',
                columns: ['id'],
                hasMany: [
                    new HasMany(name: 'questions', columns: ['id', 'text']),
                ],
            );
        }
    };

    $config = ListQueryConfigExtractor::extract($repository::class);

    expect($config['allowedRelations'])->toBe(['questions']);
});

test('it extracts nested belongsTo relations', function (): void {
    $repository = new class implements QueryConfigurable
    {
        public static function queryConfig(): QueryConfig
        {
            return new QueryConfig(
                table: 'questionnaires',
                columns: ['id'],
                hasMany: [
                    new HasMany(
                        name: 'treatments',
                        columns: ['id'],
                        nested: [new BelongsTo(name: 'integration', columns: ['id', 'name'])],
                    ),
                ],
            );
        }
    };

    $config = ListQueryConfigExtractor::extract($repository::class);

    expect($config['allowedRelations'])->toBe(['treatments', 'treatments.integration']);
});

test('it extracts nested hasMany relations', function (): void {
    $repository = new class implements QueryConfigurable
    {
        public static function queryConfig(): QueryConfig
        {
            return new QueryConfig(
                table: 'sessions',
                columns: ['id'],
                hasMany: [
                    new HasMany(
                        name: 'responses',
                        columns: ['id'],
                        nestedHasMany: [new NestedHasMany(name: 'files', columns: ['id'], foreignKey: 'response_id')],
                    ),
                ],
            );
        }
    };

    $config = ListQueryConfigExtractor::extract($repository::class);

    expect($config['allowedRelations'])->toBe(['responses', 'responses.files']);
});

test('it extracts top-level belongsTo relations', function (): void {
    $repository = new class implements QueryConfigurable
    {
        public static function queryConfig(): QueryConfig
        {
            return new QueryConfig(
                table: 'treatments',
                columns: ['id'],
                belongsTo: [
                    new BelongsTo(name: 'integration', columns: ['id', 'name']),
                ],
            );
        }
    };

    $config = ListQueryConfigExtractor::extract($repository::class);

    expect($config['allowedRelations'])->toBe(['integration']);
});
