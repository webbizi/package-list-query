<?php

use Webbizi\ListQuery\Dto\ExistsQueryDto;

test('it creates with defaults', function (): void {
    $dto = new ExistsQueryDto;

    expect($dto->filters)->toBe([]);
});

test('it creates with filters', function (): void {
    $dto = new ExistsQueryDto(filters: ['email' => 'john@example.com', 'status' => 'active']);

    expect($dto->filters)->toBe(['email' => 'john@example.com', 'status' => 'active']);
});
