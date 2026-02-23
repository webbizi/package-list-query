# Webbizi List Query

A type-safe list query system for Laravel that provides declarative filtering, sorting and relation loading using raw SQL queries with JSON aggregation.

[![Check push](https://github.com/webbizi/package-list-query/actions/workflows/check-push.yml/badge.svg)](https://github.com/webbizi/package-list-query/actions/workflows/check-push.yml)

## Features

- Type-safe query configuration with `QueryConfig`
- Declarative filtering with 11 operators (eq, neq, gt, contains, null...)
- Sorting with allowed field validation
- Relation loading via LEFT JOIN + JSON aggregation (HasMany, BelongsTo, nested)
- Abstract FormRequests for automatic validation of filters, sorts and relations
- Immutable DTOs for query parameters
- Usable from controllers, repositories, jobs, or any service

## Installation

```bash
composer require webbizi/list-query
```

## Usage

### 1. Define a query configuration

Implement `QueryConfigurable` on your repository class:

```php
use Webbizi\ListQuery\Config\QueryConfig;
use Webbizi\ListQuery\Config\QueryConfigurable;
use Webbizi\ListQuery\Relation\BelongsTo;
use Webbizi\ListQuery\Relation\HasMany;

final class UserRepository implements QueryConfigurable
{
    public static function queryConfig(): QueryConfig
    {
        return new QueryConfig(
            table: 'users',
            columns: ['id', 'name', 'email', 'created_at'],
            allowedFilters: ['name', 'email', 'created_at'],
            allowedSorts: ['name', 'created_at'],
            hasMany: [
                new HasMany(
                    name: 'posts',
                    columns: ['id', 'title', 'published_at'],
                ),
            ],
            belongsTo: [
                new BelongsTo(
                    name: 'role',
                    columns: ['id', 'name'],
                ),
            ],
        );
    }
}
```

### 2. Use via HTTP (FormRequest)

Create a FormRequest that extends `AbstractListRequest`:

```php
use Webbizi\ListQuery\Request\AbstractListRequest;

final class ListUsersRequest extends AbstractListRequest
{
    protected function repositoryClass(): string
    {
        return UserRepository::class;
    }
}
```

Then use it in a controller:

```php
final class UserController
{
    public function __construct(
        private ListQueryBuilder $queryBuilder,
    ) {}

    public function index(ListUsersRequest $request): JsonResponse
    {
        $query = $this->queryBuilder->list(
            UserRepository::class,
            $request->toDto(),
        );

        return response()->json([
            'data' => $query->paginate(),
            'meta' => $request->meta(),
        ]);
    }
}
```

Query parameters:

```
GET /users?filters[name]=contains:John&filters[created_at]=gte:2024-01-01&sort=created_at&direction=desc&with=posts,role
```

### 3. Use programmatically (without FormRequest)

`ListQueryBuilder` can be used directly from any service, repository, job, or command:

```php
use Webbizi\ListQuery\Dto\ListQueryDto;
use Webbizi\ListQuery\Dto\FindQueryDto;
use Webbizi\ListQuery\Dto\ExistsQueryDto;
use Webbizi\ListQuery\Filter\ListFilter;
use Webbizi\ListQuery\Filter\FilterOperator;
use Webbizi\ListQuery\Sort\ListSort;

// List with filters and sorting
$query = $this->queryBuilder->list(
    UserRepository::class,
    new ListQueryDto(
        filters: [
            new ListFilter('email', FilterOperator::CONTAINS, 'example.com'),
            new ListFilter('created_at', FilterOperator::GTE, '2024-01-01'),
        ],
        sort: new ListSort('created_at', ListSort::DIRECTION_DESC),
        relations: ['posts', 'role'],
    ),
);
$users = $query->get();

// Find a single record with relations
$user = $this->queryBuilder->find(
    UserRepository::class,
    $id,
    new FindQueryDto(relations: ['posts', 'role']),
);

// Check existence
$exists = $this->queryBuilder->exists(
    UserRepository::class,
    new ExistsQueryDto(filters: ['email' => 'john@example.com']),
);
```

## Available filter operators

| Operator      | Example                          | SQL                            |
|---------------|----------------------------------|--------------------------------|
| `eq`          | `filters[status]=eq:active`      | `WHERE status = 'active'`      |
| `neq`         | `filters[status]=neq:archived`   | `WHERE status != 'archived'`   |
| `gt`          | `filters[age]=gt:18`             | `WHERE age > 18`               |
| `gte`         | `filters[age]=gte:18`            | `WHERE age >= 18`              |
| `lt`          | `filters[price]=lt:100`          | `WHERE price < 100`            |
| `lte`         | `filters[price]=lte:100`         | `WHERE price <= 100`           |
| `starts_with` | `filters[name]=starts_with:Jo`   | `WHERE name LIKE 'Jo%'`        |
| `ends_with`   | `filters[email]=ends_with:.com`  | `WHERE email LIKE '%.com'`     |
| `contains`    | `filters[name]=contains:ohn`     | `WHERE name LIKE '%ohn%'`      |
| `null`        | `filters[deleted_at]=null`       | `WHERE deleted_at IS NULL`     |
| `not_null`    | `filters[verified_at]=not_null`  | `WHERE verified_at IS NOT NULL`|
