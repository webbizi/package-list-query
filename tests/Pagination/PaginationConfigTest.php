<?php

use Webbizi\ListQuery\Pagination\PaginationConfig;

test('it creates with defaults', function (): void {
    $config = new PaginationConfig;

    expect($config->page)->toBe(1);
    expect($config->perPage)->toBe(15);
});

test('it creates with custom values', function (): void {
    $config = new PaginationConfig(page: 3, perPage: 25);

    expect($config->page)->toBe(3);
    expect($config->perPage)->toBe(25);
});

test('it exposes default and max constants', function (): void {
    expect(PaginationConfig::DEFAULT_PER_PAGE)->toBe(15);
    expect(PaginationConfig::MAX_PER_PAGE)->toBe(100);
});
