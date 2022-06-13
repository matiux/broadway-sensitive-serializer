<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\Shared\Tools;

class Util
{
    /**
     * @psalm-assert array<int, mixed>|scalar|null $value
     *
     * @param mixed $value
     *
     * @return bool
     */
    public static function isAssociativeArray($value): bool
    {
        if (!is_array($value)) {
            return false;
        }

        $keys = array_keys($value);

        return $keys !== array_keys($keys);
    }
}
