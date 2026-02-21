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

### 2. Create a FormRequest

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

### 3. Use in a controller

```php
final class UserController
{
    public function __construct(
        private RawQueryApplier $queryApplier,
    ) {}

    public function index(ListUsersRequest $request): JsonResponse
    {
        $query = $this->queryApplier->list(
            UserRepository::class,
            $request->toDto(),
        );

        return response()->json([
            'data' => $query->paginate(),
            'meta' => $request->meta(),
        ]);
    }

    public function show(ShowUserRequest $request, int $id): JsonResponse
    {
        $user = $this->queryApplier->find(
            UserRepository::class,
            $id,
            $request->toDto(),
        );

        return response()->json(['data' => $user]);
    }
}
```

### 4. Query parameters

```
GET /users?filters[name]=contains:John&filters[created_at]=gte:2024-01-01&sort=created_at&direction=desc&with=posts,role
```

### Available filter operators

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

## Architecture

```
src/
├── Config/              # Query configuration
│   ├── QueryConfig.php
│   └── QueryConfigurable.php
├── Dto/                 # Immutable query parameter objects
│   ├── ListQueryDto.php
│   ├── FindQueryDto.php
│   └── ExistsQueryDto.php
├── Relation/            # Relation definitions
│   ├── BelongsTo.php
│   ├── HasMany.php
│   └── NestedHasMany.php
├── Request/             # Abstract Laravel FormRequests
│   ├── AbstractListRequest.php
│   └── AbstractShowRequest.php
├── FilterOperator.php   # Filter operator enum
├── FilterSortApplier.php
├── InvalidFilterException.php
├── ListFilter.php       # Filter value object
├── ListSort.php         # Sort value object
├── RawQueryApplier.php  # Main orchestrator
├── RelationJoiner.php   # JOIN + JSON aggregation
└── SqlHelper.php        # SQL utilities
```
