<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Service;

interface SensitiveDataManager
{
    public const IS_SENSITISED_INDICATOR = '#-#';

    public function encrypt(string $sensitiveData, string $secretKey = null): string;

    public function decrypt(string $encryptedSensitiveData, string $secretKey = null): string;
}
