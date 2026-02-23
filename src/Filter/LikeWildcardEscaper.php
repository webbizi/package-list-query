<?php

declare(strict_types=1);

namespace Webbizi\ListQuery\Filter;

final readonly class LikeWildcardEscaper
{
    public static function escape(string $value): string
    {
        return str_replace(
            ['\\', '%', '_'],
            ['\\\\', '\\%', '\\_'],
            $value,
        );
    }
}
