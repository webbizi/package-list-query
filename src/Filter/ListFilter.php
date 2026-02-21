<?php

declare(strict_types=1);

namespace Webbizi\ListQuery\Filter;

final readonly class ListFilter
{
    public function __construct(
        public string $field,
        public FilterOperator $operator,
        public ?string $value,
    ) {}

    /**
     * @throws InvalidFilterException
     */
    public static function fromString(string $field, string $expression): self
    {
        $parts = explode(':', $expression, 2);
        $operatorString = $parts[0];
        $value = $parts[1] ?? null;

        $operator = FilterOperator::tryFrom($operatorString);

        if ($operator === null) {
            throw new InvalidFilterException("Invalid operator: {$operatorString}");
        }

        if (! $operator->isValueless() && $value === null) {
            throw new InvalidFilterException("Operator {$operatorString} requires a value");
        }

        return new self($field, $operator, $value);
    }
}
