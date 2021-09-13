<?php

declare(strict_types=1);

namespace Test\Util;

class EnvVarTool
{
    public static function get(string $name, string $default = ''): string
    {
        return getenv($name) ?: (string) ($_ENV[$name] ?? $default);
    }
}
