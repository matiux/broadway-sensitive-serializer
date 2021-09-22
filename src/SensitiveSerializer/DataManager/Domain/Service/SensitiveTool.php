<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Service;

trait SensitiveTool
{
    public static function isSensitized(string $data): bool
    {
        return str_starts_with($data, SensitiveDataManager::IS_SENSITISED_INDICATOR);
    }
}
