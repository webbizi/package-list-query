<?php

declare(strict_types=1);

namespace Webbizi\ListQuery\Support;

final class StringHelper
{
    public static function toSnakeCase(string $camelCase): string
    {
        return strtolower((string) preg_replace('/[A-Z]/', '_$0', lcfirst($camelCase)));
    }

    public static function toPlural(string $singular): string
    {
        if (str_ends_with($singular, 'y')) {
            return substr($singular, 0, -1).'ies';
        }

        if (str_ends_with($singular, 's')) {
            return $singular.'es';
        }

        return $singular.'s';
    }

    public static function toSingular(string $plural): string
    {
        if (str_ends_with($plural, 'ies')) {
            return substr($plural, 0, -3).'y';
        }

        if (str_ends_with($plural, 's')) {
            return substr($plural, 0, -1);
        }

        return $plural;
    }
}
