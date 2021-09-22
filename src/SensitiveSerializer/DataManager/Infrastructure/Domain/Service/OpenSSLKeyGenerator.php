<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\DataManager\Infrastructure\Domain\Service;

use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Service\KeyGenerator;

class OpenSSLKeyGenerator implements KeyGenerator
{
    public function generate(): string
    {
        // Generate a 256-bit (32 bytes) encryption key.
        return openssl_random_pseudo_bytes(32);
    }
}
