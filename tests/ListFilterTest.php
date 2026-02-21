<?php

use Webbizi\ListQuery\FilterOperator;
use Webbizi\ListQuery\InvalidFilterException;
use Webbizi\ListQuery\ListFilter;

test('it parses eq filter from string', function (): void {
    $filter = ListFilter::fromString('name', 'eq:John');

    expect($filter->field)->toBe('name');
    expect($filter->operator)->toBe(FilterOperator::EQ);
    expect($filter->value)->toBe('John');
});

test('it parses contains filter from string', function (): void {
    $filter = ListFilter::fromString('email', 'contains:@gmail');

    expect($filter->field)->toBe('email');
    expect($filter->operator)->toBe(FilterOperator::CONTAINS);
    expect($filter->value)->toBe('@gmail');
});

test('it parses valueless null operator', function (): void {
    $filter = ListFilter::fromString('deleted_at', 'null');

    expect($filter->field)->toBe('deleted_at');
    expect($filter->operator)->toBe(FilterOperator::NULL);
    expect($filter->value)->toBeNull();
});

test('it parses valueless not_null operator', function (): void {
    $filter = ListFilter::fromString('verified_at', 'not_null');

    expect($filter->field)->toBe('verified_at');
    expect($filter->operator)->toBe(FilterOperator::NOT_NULL);
    expect($filter->value)->toBeNull();
});

test('it preserves value with colons', function (): void {
    $filter = ListFilter::fromString('timestamp', 'gte:2024-01-01 10:30:00');

    expect($filter->operator)->toBe(FilterOperator::GTE);
    expect($filter->value)->toBe('2024-01-01 10:30:00');
});

test('it throws on invalid operator', function (): void {
    ListFilter::fromString('name', 'invalid:value');
})->throws(InvalidFilterException::class, 'Invalid operator: invalid');

test('it throws when value is missing for non-valueless operator', function (): void {
    ListFilter::fromString('name', 'eq');
})->throws(InvalidFilterException::class, 'Operator eq requires a value');

test('it can be constructed directly', function (): void {
    $filter = new ListFilter('status', FilterOperator::EQ, 'active');

    expect($filter->field)->toBe('status');
    expect($filter->operator)->toBe(FilterOperator::EQ);
    expect($filter->value)->toBe('active');
});
