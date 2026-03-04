<?php

use Webbizi\ListQuery\Dto\ListQueryDto;
use Webbizi\ListQuery\Filter\FilterOperator;
use Webbizi\ListQuery\Filter\ListFilter;
use Webbizi\ListQuery\Pagination\PaginationConfig;
use Webbizi\ListQuery\Sort\ListSort;

test('it creates with defaults', function (): void {
    $dto = new ListQueryDto;

    expect($dto->filters)->toBe([]);
    expect($dto->sort)->toBeNull();
    expect($dto->relations)->toBe([]);
    expect($dto->pagination)->toBeNull();
});

test('it creates with all parameters', function (): void {
    $filter = new ListFilter('name', FilterOperator::EQ, 'John');
    $sort = new ListSort('created_at', 'desc');
    $pagination = new PaginationConfig(page: 2, perPage: 25);

    $dto = new ListQueryDto(
        filters: [$filter],
        sort: $sort,
        relations: ['posts', 'role'],
        pagination: $pagination,
    );

    expect($dto->filters)->toHaveCount(1);
    expect($dto->filters[0])->toBe($filter);
    expect($dto->sort)->toBe($sort);
    expect($dto->relations)->toBe(['posts', 'role']);
    expect($dto->pagination)->toBe($pagination);
});

test('withFilter returns new instance with added filter', function (): void {
    $pagination = new PaginationConfig(page: 3, perPage: 10);
    $original = new ListQueryDto(
        filters: [new ListFilter('name', FilterOperator::EQ, 'John')],
        sort: new ListSort('name'),
        relations: ['posts'],
        pagination: $pagination,
    );

    $newFilter = new ListFilter('status', FilterOperator::EQ, 'active');
    $updated = $original->withFilter($newFilter);

    expect($updated)->not->toBe($original);
    expect($updated->filters)->toHaveCount(2);
    expect($updated->filters[1])->toBe($newFilter);
    expect($updated->sort)->toBe($original->sort);
    expect($updated->relations)->toBe($original->relations);
    expect($updated->pagination)->toBe($original->pagination);
    expect($original->filters)->toHaveCount(1);
});

test('where adds an EQ filter', function (): void {
    $dto = new ListQueryDto;

    $updated = $dto->where('status', 'active');

    expect($updated->filters)->toHaveCount(1);
    expect($updated->filters[0]->field)->toBe('status');
    expect($updated->filters[0]->operator)->toBe(FilterOperator::EQ);
    expect($updated->filters[0]->value)->toBe('active');
});

test('where is chainable with existing filters', function (): void {
    $dto = new ListQueryDto(
        filters: [new ListFilter('name', FilterOperator::CONTAINS, 'John')],
    );

    $updated = $dto->where('status', 'active')->where('role', 'admin');

    expect($updated->filters)->toHaveCount(3);
    expect($updated->filters[0]->field)->toBe('name');
    expect($updated->filters[1]->field)->toBe('status');
    expect($updated->filters[2]->field)->toBe('role');
});

test('where casts int and float to string', function (): void {
    $dto = new ListQueryDto;

    $withInt = $dto->where('user_id', 42);
    $withFloat = $dto->where('price', 19.99);

    expect($withInt->filters[0]->value)->toBe('42');
    expect($withFloat->filters[0]->value)->toBe('19.99');
});
