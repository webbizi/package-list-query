<?php

use Webbizi\ListQuery\Relation\AliasGenerator;

test('it generates unique aliases from table names', function (): void {
    $generator = new AliasGenerator;

    $alias1 = $generator->generate('users');
    $alias2 = $generator->generate('users');
    $alias3 = $generator->generate('questionnaire_sessions');

    expect($alias1)->toBe('u1');
    expect($alias2)->toBe('u2');
    expect($alias3)->toBe('qs3');
});

test('it generates alias from multi-word table name', function (): void {
    $generator = new AliasGenerator;

    $alias = $generator->generate('treatment_execution_logs');

    expect($alias)->toBe('tel1');
});

test('it increments counter across all calls', function (): void {
    $generator = new AliasGenerator;

    $generator->generate('users');
    $generator->generate('posts');
    $alias = $generator->generate('comments');

    expect($alias)->toBe('c3');
});
