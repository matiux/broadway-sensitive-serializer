<?php

declare(strict_types=1);

namespace SensitizedEventStore\Dbal\SensitiveDataManager\Infrastructure\Domain\Service;

use SensitizedEventStore\Dbal\SensitiveDataManager\Domain\Service\KeyGenerator;

class OpenSSLKeyGenerator implements KeyGenerator
{
    public function generate(): string
    {
        // Generate a 256-bit (32 bytes) encryption key.
        return openssl_random_pseudo_bytes(32);
    }
}
