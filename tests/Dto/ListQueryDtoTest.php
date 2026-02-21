<?php

use Webbizi\ListQuery\Dto\ListQueryDto;
use Webbizi\ListQuery\FilterOperator;
use Webbizi\ListQuery\ListFilter;
use Webbizi\ListQuery\ListSort;

test('it creates with defaults', function (): void {
    $dto = new ListQueryDto;

    expect($dto->filters)->toBe([]);
    expect($dto->sort)->toBeNull();
    expect($dto->relations)->toBe([]);
});

test('it creates with all parameters', function (): void {
    $filter = new ListFilter('name', FilterOperator::EQ, 'John');
    $sort = new ListSort('created_at', 'desc');

    $dto = new ListQueryDto(
        filters: [$filter],
        sort: $sort,
        relations: ['posts', 'role'],
    );

    expect($dto->filters)->toHaveCount(1);
    expect($dto->filters[0])->toBe($filter);
    expect($dto->sort)->toBe($sort);
    expect($dto->relations)->toBe(['posts', 'role']);
});

test('withFilter returns new instance with added filter', function (): void {
    $original = new ListQueryDto(
        filters: [new ListFilter('name', FilterOperator::EQ, 'John')],
        sort: new ListSort('name'),
        relations: ['posts'],
    );

    $newFilter = new ListFilter('status', FilterOperator::EQ, 'active');
    $updated = $original->withFilter($newFilter);

    expect($updated)->not->toBe($original);
    expect($updated->filters)->toHaveCount(2);
    expect($updated->filters[1])->toBe($newFilter);
    expect($updated->sort)->toBe($original->sort);
    expect($updated->relations)->toBe($original->relations);
    expect($original->filters)->toHaveCount(1);
});
