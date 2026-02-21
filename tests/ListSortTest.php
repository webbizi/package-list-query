<?php

use Webbizi\ListQuery\ListSort;

test('it creates sort with default ascending direction', function (): void {
    $sort = new ListSort('name');

    expect($sort->field)->toBe('name');
    expect($sort->direction)->toBe('asc');
});

test('it creates sort with custom direction', function (): void {
    $sort = new ListSort('created_at', 'desc');

    expect($sort->field)->toBe('created_at');
    expect($sort->direction)->toBe('desc');
});
