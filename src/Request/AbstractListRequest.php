<?php

declare(strict_types=1);

namespace Webbizi\ListQuery\Request;

use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Webbizi\ListQuery\Dto\ListQueryDto;
use Webbizi\ListQuery\Pagination\PaginationConfig;
use Webbizi\ListQuery\Request\Concerns\ParsesListQueryInput;
use Webbizi\ListQuery\Sort\ListSort;

abstract class AbstractListRequest extends FormRequest
{
    use ParsesListQueryInput;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $config = $this->allowedConfig();

        return [
            'filters' => [
                'sometimes',
                'array',
                function (string $attribute, mixed $value, Closure $fail) use ($config): void {
                    /** @var array<string, string> $filters */
                    $filters = $value;

                    collect($filters)
                        ->keys()
                        ->reject(fn (string $field): bool => in_array($field, $config['allowedFilters'], true))
                        ->each(fn (string $field) => $fail("The filter field [{$field}] is not allowed."));
                },
            ],
            'filters.*' => ['string'],

            'sort' => ['sometimes', 'string', Rule::in($config['allowedSorts'])],
            'direction' => ['sometimes', Rule::in([ListSort::DIRECTION_ASC, ListSort::DIRECTION_DESC])],

            'with' => [
                'sometimes',
                'array',
                function (string $attribute, mixed $value, Closure $fail) use ($config): void {
                    /** @var array<string> $relations */
                    $relations = $value;

                    collect($relations)
                        ->reject(fn (string $relation): bool => in_array($relation, $config['allowedRelations'], true))
                        ->each(fn (string $relation) => $fail("The relation [{$relation}] is not allowed."));
                },
            ],
            'with.*' => ['string'],

            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:'.PaginationConfig::MAX_PER_PAGE],
        ];
    }

    public function toDto(): ListQueryDto
    {
        return new ListQueryDto(
            filters: $this->parseFilters(),
            sort: $this->parseSort(),
            relations: $this->parseRelations(),
            pagination: $this->parsePagination(),
        );
    }

    /**
     * @return array{allowed_filters: array<string>, allowed_sorts: array<string>, allowed_relations: array<string>, default_per_page: int, max_per_page: int}
     */
    public function meta(): array
    {
        $config = $this->allowedConfig();

        return [
            'allowed_filters' => $config['allowedFilters'],
            'allowed_sorts' => $config['allowedSorts'],
            'allowed_relations' => $config['allowedRelations'],
            'default_per_page' => PaginationConfig::DEFAULT_PER_PAGE,
            'max_per_page' => PaginationConfig::MAX_PER_PAGE,
        ];
    }
}
