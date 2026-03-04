<?php

use Webbizi\ListQuery\Relation\NestedHasMany;
use Webbizi\ListQuery\Relation\NestedHasManySubquery;

test('it builds correlated subquery with JSON_ARRAYAGG', function (): void {
    $relation = new NestedHasMany(
        name: 'files',
        columns: ['id', 'name', 'path'],
        foreignKey: 'response_id',
    );

    $result = NestedHasManySubquery::build($relation, 'r1');

    expect($result)->toBe(
        "(SELECT JSON_ARRAYAGG(JSON_OBJECT('id', `n`.`id`, 'name', `n`.`name`, 'path', `n`.`path`)) FROM `files` `n` WHERE `n`.`response_id` = `r1`.`id`)"
    );
});

test('it uses custom table name', function (): void {
    $relation = new NestedHasMany(
        name: 'attachments',
        columns: ['id', 'url'],
        foreignKey: 'message_id',
        table: 'message_attachments',
    );

    $result = NestedHasManySubquery::build($relation, 'msg1');

    expect($result)->toContain('FROM `message_attachments` `n`');
    expect($result)->toContain('`n`.`message_id` = `msg1`.`id`');
});
