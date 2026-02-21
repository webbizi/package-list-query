<?php

use Webbizi\ListQuery\SqlHelper;

test('it converts camelCase to snake_case', function (): void {
    expect(SqlHelper::toSnakeCase('camelCase'))->toBe('camel_case');
    expect(SqlHelper::toSnakeCase('transformationTreatments'))->toBe('transformation_treatments');
    expect(SqlHelper::toSnakeCase('simple'))->toBe('simple');
    expect(SqlHelper::toSnakeCase('HTMLParser'))->toBe('h_t_m_l_parser');
});

test('it builds JSON_OBJECT SQL expression', function (): void {
    $result = SqlHelper::buildJsonObject('u', ['id', 'name', 'email'], []);

    expect($result)->toBe("JSON_OBJECT('id', u.id, 'name', u.name, 'email', u.email)");
});

test('it builds JSON_OBJECT with nested fragments', function (): void {
    $result = SqlHelper::buildJsonObject('p', ['id', 'title'], [
        'author' => "IF(a1.id IS NOT NULL, JSON_OBJECT('id', a1.id), NULL)",
    ]);

    expect($result)->toBe("JSON_OBJECT('id', p.id, 'title', p.title, 'author', IF(a1.id IS NOT NULL, JSON_OBJECT('id', a1.id), NULL))");
});

test('it builds JSON_OBJECT with empty columns and only nested fragments', function (): void {
    $result = SqlHelper::buildJsonObject('t', [], [
        'items' => 'NULL',
    ]);

    expect($result)->toBe("JSON_OBJECT('items', NULL)");
});

test('it expands parent relations from dotted names', function (): void {
    $relations = ['posts.comments', 'posts.author'];

    $expanded = SqlHelper::expandParentRelations($relations);

    expect($expanded)->toContain('posts');
    expect($expanded)->toContain('posts.comments');
    expect($expanded)->toContain('posts.author');
});

test('it does not duplicate already present parent relations', function (): void {
    $relations = ['posts', 'posts.comments'];

    $expanded = SqlHelper::expandParentRelations($relations);

    expect(array_count_values($expanded)['posts'])->toBe(1);
});

test('it handles relations without dots', function (): void {
    $relations = ['posts', 'tags'];

    $expanded = SqlHelper::expandParentRelations($relations);

    expect($expanded)->toBe(['posts', 'tags']);
});

test('it handles empty relations array', function (): void {
    $expanded = SqlHelper::expandParentRelations([]);

    expect($expanded)->toBe([]);
});

test('it generates unique aliases from table names', function (): void {
    $counter = 0;

    $alias1 = SqlHelper::generateAlias('users', $counter);
    $alias2 = SqlHelper::generateAlias('users', $counter);
    $alias3 = SqlHelper::generateAlias('questionnaire_sessions', $counter);

    expect($alias1)->toBe('u1');
    expect($alias2)->toBe('u2');
    expect($alias3)->toBe('qs3');
    expect($counter)->toBe(3);
});

test('it generates alias from multi-word table name', function (): void {
    $counter = 0;

    $alias = SqlHelper::generateAlias('treatment_execution_logs', $counter);

    expect($alias)->toBe('tel1');
});
