<?php

use Webbizi\ListQuery\Sql\JsonArrayAggBuilder;

test('it wraps JSON_OBJECT in JSON_ARRAYAGG with NULL check', function (): void {
    $result = JsonArrayAggBuilder::build('q', ['id', 'text']);

    expect($result)->toBe("JSON_ARRAYAGG(IF(`q`.`id` IS NOT NULL, JSON_OBJECT('id', `q`.`id`, 'text', `q`.`text`), NULL))");
});

test('it includes nested fragments in the aggregation', function (): void {
    $result = JsonArrayAggBuilder::build('t', ['id', 'name'], [
        'integration' => "IF(i1.id IS NOT NULL, JSON_OBJECT('id', i1.id), NULL)",
    ]);

    expect($result)->toContain('JSON_ARRAYAGG(IF(`t`.`id` IS NOT NULL,');
    expect($result)->toContain("'integration', IF(i1.id IS NOT NULL, JSON_OBJECT('id', i1.id), NULL)");
});
