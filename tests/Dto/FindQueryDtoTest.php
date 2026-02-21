<?php

use Webbizi\ListQuery\Dto\FindQueryDto;

test('it creates with defaults', function (): void {
    $dto = new FindQueryDto;

    expect($dto->relations)->toBe([]);
});

test('it creates with relations', function (): void {
    $dto = new FindQueryDto(relations: ['posts', 'role']);

    expect($dto->relations)->toBe(['posts', 'role']);
});
