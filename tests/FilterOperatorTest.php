<?php

use Webbizi\ListQuery\FilterOperator;

test('it has correct string values', function (): void {
    expect(FilterOperator::EQ->value)->toBe('eq');
    expect(FilterOperator::NEQ->value)->toBe('neq');
    expect(FilterOperator::GT->value)->toBe('gt');
    expect(FilterOperator::GTE->value)->toBe('gte');
    expect(FilterOperator::LT->value)->toBe('lt');
    expect(FilterOperator::LTE->value)->toBe('lte');
    expect(FilterOperator::STARTS_WITH->value)->toBe('starts_with');
    expect(FilterOperator::ENDS_WITH->value)->toBe('ends_with');
    expect(FilterOperator::CONTAINS->value)->toBe('contains');
    expect(FilterOperator::NULL->value)->toBe('null');
    expect(FilterOperator::NOT_NULL->value)->toBe('not_null');
});

test('null and not_null are valueless', function (): void {
    expect(FilterOperator::NULL->isValueless())->toBeTrue();
    expect(FilterOperator::NOT_NULL->isValueless())->toBeTrue();
});

test('comparison operators are not valueless', function (FilterOperator $operator): void {
    expect($operator->isValueless())->toBeFalse();
})->with([
    FilterOperator::EQ,
    FilterOperator::NEQ,
    FilterOperator::GT,
    FilterOperator::GTE,
    FilterOperator::LT,
    FilterOperator::LTE,
    FilterOperator::STARTS_WITH,
    FilterOperator::ENDS_WITH,
    FilterOperator::CONTAINS,
]);

test('it can be created from string', function (): void {
    expect(FilterOperator::from('eq'))->toBe(FilterOperator::EQ);
    expect(FilterOperator::from('contains'))->toBe(FilterOperator::CONTAINS);
});

test('tryFrom returns null for invalid operator', function (): void {
    expect(FilterOperator::tryFrom('invalid'))->toBeNull();
});
