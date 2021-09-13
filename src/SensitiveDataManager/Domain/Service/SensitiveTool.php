<?php

declare(strict_types=1);

namespace SensitizedEventStore\Dbal\SensitiveDataManager\Domain\Service;

trait SensitiveTool
{
    public static function isSensitised(string $data): bool
    {
        return str_starts_with($data, SensitiveDataManager::IS_SENSITISED_INDICATOR);
    }
}
