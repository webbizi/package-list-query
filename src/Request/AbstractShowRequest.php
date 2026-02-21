<?php

declare(strict_types=1);

namespace Webbizi\ListQuery\Request;

use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Webbizi\ListQuery\Config\QueryConfigurable;
use Webbizi\ListQuery\Dto\FindQueryDto;
use Webbizi\ListQuery\RawQueryApplier;

abstract class AbstractShowRequest extends FormRequest
{
    /**
     * @return class-string<QueryConfigurable>
     */
    abstract protected function repositoryClass(): string;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $with = $this->input('with');

        if (is_string($with)) {
            $this->merge([
                'with' => array_filter(explode(',', $with)),
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $config = $this->allowedConfig();

        return [
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
        ];
    }

    public function toDto(): FindQueryDto
    {
        return new FindQueryDto(
            relations: $this->parseRelations(),
        );
    }

    /**
     * @return array{allowed_relations: array<string>}
     */
    public function meta(): array
    {
        $config = $this->allowedConfig();

        return [
            'allowed_relations' => $config['allowedRelations'],
        ];
    }

    /**
     * @return array{allowedFilters: array<string>, allowedSorts: array<string>, allowedRelations: array<string>}
     */
    protected function allowedConfig(): array
    {
        return RawQueryApplier::allowedConfig($this->repositoryClass());
    }

    /**
     * @return array<string>
     */
    protected function parseRelations(): array
    {
        /** @var array<string> $relations */
        $relations = $this->input('with', []);

        return $relations;
    }
}
