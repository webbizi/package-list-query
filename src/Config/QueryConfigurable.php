<?php

declare(strict_types=1);

namespace Webbizi\ListQuery\Config;

interface QueryConfigurable
{
    public static function queryConfig(): QueryConfig;
}
