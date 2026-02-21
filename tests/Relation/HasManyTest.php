<?php

use Webbizi\ListQuery\Relation\BelongsTo;
use Webbizi\ListQuery\Relation\HasMany;
use Webbizi\ListQuery\Relation\NestedHasMany;

test('it auto-generates table name from camelCase name', function (): void {
    $relation = new HasMany(name: 'transformationTreatments', columns: ['id']);

    expect($relation->table)->toBe('transformation_treatments');
});

test('it auto-generates table name from simple name', function (): void {
    $relation = new HasMany(name: 'questions', columns: ['id']);

    expect($relation->table)->toBe('questions');
});

test('it uses custom table when provided', function (): void {
    $relation = new HasMany(name: 'items', columns: ['id'], table: 'custom_items');

    expect($relation->table)->toBe('custom_items');
});

test('it resolves foreign key from parent table', function (): void {
    $relation = new HasMany(name: 'questions', columns: ['id']);

    expect($relation->resolveForeignKey('questionnaires'))->toBe('questionnaire_id');
});

test('it resolves foreign key from plural parent table', function (): void {
    $relation = new HasMany(name: 'posts', columns: ['id']);

    expect($relation->resolveForeignKey('users'))->toBe('user_id');
});

test('it resolves foreign key from parent table ending in ies', function (): void {
    $relation = new HasMany(name: 'items', columns: ['id']);

    expect($relation->resolveForeignKey('companies'))->toBe('company_id');
});

test('it uses custom foreign key when provided', function (): void {
    $relation = new HasMany(name: 'posts', columns: ['id'], foreignKey: 'author_id');

    expect($relation->resolveForeignKey('users'))->toBe('author_id');
});

test('it stores nested belongsTo relations', function (): void {
    $nested = [new BelongsTo(name: 'author', columns: ['id', 'name'])];
    $relation = new HasMany(name: 'posts', columns: ['id'], nested: $nested);

    expect($relation->nested)->toHaveCount(1);
    expect($relation->nested[0]->name)->toBe('author');
});

test('it stores nested hasMany relations', function (): void {
    $nestedHasMany = [new NestedHasMany(name: 'files', columns: ['id', 'path'], foreignKey: 'response_id')];
    $relation = new HasMany(name: 'responses', columns: ['id'], nestedHasMany: $nestedHasMany);

    expect($relation->nestedHasMany)->toHaveCount(1);
    expect($relation->nestedHasMany[0]->name)->toBe('files');
});

test('it has empty nested relations by default', function (): void {
    $relation = new HasMany(name: 'posts', columns: ['id']);

    expect($relation->nested)->toBe([]);
    expect($relation->nestedHasMany)->toBe([]);
});
