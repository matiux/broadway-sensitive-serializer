<?php

declare(strict_types=1);

namespace Tests\Util;

class EnvVarTool
{
    public static function get(string $name, string $default = ''): string
    {
        return getenv($name) ?: (string) ($_ENV[$name] ?? $default);
    }
}
