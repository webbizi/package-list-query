<?php

use Webbizi\ListQuery\Sql\JsonObjectBuilder;

test('it builds JSON_OBJECT SQL expression', function (): void {
    $result = JsonObjectBuilder::build('u', ['id', 'name', 'email']);

    expect($result)->toBe("JSON_OBJECT('id', `u`.`id`, 'name', `u`.`name`, 'email', `u`.`email`)");
});

test('it builds JSON_OBJECT with nested fragments', function (): void {
    $result = JsonObjectBuilder::build('p', ['id', 'title'], [
        'author' => "IF(a1.id IS NOT NULL, JSON_OBJECT('id', a1.id), NULL)",
    ]);

    expect($result)->toBe("JSON_OBJECT('id', `p`.`id`, 'title', `p`.`title`, 'author', IF(a1.id IS NOT NULL, JSON_OBJECT('id', a1.id), NULL))");
});

test('it builds JSON_OBJECT with empty columns and only nested fragments', function (): void {
    $result = JsonObjectBuilder::build('t', [], [
        'items' => 'NULL',
    ]);

    expect($result)->toBe("JSON_OBJECT('items', NULL)");
});

test('it builds JSON_OBJECT with single column', function (): void {
    $result = JsonObjectBuilder::build('u', ['id']);

    expect($result)->toBe("JSON_OBJECT('id', `u`.`id`)");
});
