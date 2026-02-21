<?php

use Webbizi\ListQuery\Support\StringHelper;

test('it converts camelCase to snake_case', function (): void {
    expect(StringHelper::toSnakeCase('camelCase'))->toBe('camel_case');
    expect(StringHelper::toSnakeCase('transformationTreatments'))->toBe('transformation_treatments');
    expect(StringHelper::toSnakeCase('simple'))->toBe('simple');
    expect(StringHelper::toSnakeCase('HTMLParser'))->toBe('h_t_m_l_parser');
});

test('it pluralizes singular words', function (): void {
    expect(StringHelper::toPlural('integration'))->toBe('integrations');
    expect(StringHelper::toPlural('company'))->toBe('companies');
    expect(StringHelper::toPlural('address'))->toBe('addresses');
});

test('it singularizes plural words', function (): void {
    expect(StringHelper::toSingular('questionnaires'))->toBe('questionnaire');
    expect(StringHelper::toSingular('companies'))->toBe('company');
    expect(StringHelper::toSingular('user'))->toBe('user');
});
