<?php

use Webbizi\ListQuery\Support\RelationExpander;

test('it expands parent relations from dotted names', function (): void {
    $relations = ['posts.comments', 'posts.author'];

    $expanded = RelationExpander::expand($relations);

    expect($expanded)->toContain('posts');
    expect($expanded)->toContain('posts.comments');
    expect($expanded)->toContain('posts.author');
});

test('it does not duplicate already present parent relations', function (): void {
    $relations = ['posts', 'posts.comments'];

    $expanded = RelationExpander::expand($relations);

    expect(array_count_values($expanded)['posts'])->toBe(1);
});

test('it handles relations without dots', function (): void {
    $relations = ['posts', 'tags'];

    $expanded = RelationExpander::expand($relations);

    expect($expanded)->toBe(['posts', 'tags']);
});

test('it handles empty relations array', function (): void {
    $expanded = RelationExpander::expand([]);

    expect($expanded)->toBe([]);
});
