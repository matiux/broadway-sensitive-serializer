<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Service;

final class SensitiveTool
{
    /**
     * Determines if a given string is sensitized checking if IS_SENSITIZED_INDICATOR constant is present in string.
     * It is useful in some validation check, for example, inside a value objects.
     *
     * @param mixed $data
     *
     * @return bool
     */
    public static function isSensitized($data): bool
    {
        return is_string($data) && str_starts_with($data, SensitiveDataManager::IS_SENSITIZED_INDICATOR);
    }
}
