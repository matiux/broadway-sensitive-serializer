<?php

declare(strict_types=1);

namespace Tests\Unit\SensitiveSerializer\DataManager\Infrastructure\Domain\Service;

use Matiux\Broadway\SensitiveSerializer\DataManager\Infrastructure\Domain\Service\OpenSSLKeyGenerator;
use PHPUnit\Framework\TestCase;

class OpenSSLKeyGeneratorTest extends TestCase
{
    /**
     * @test
     */
    public function it_should_create_256_bit_key(): void
    {
        $generator = new OpenSSLKeyGenerator();
        $key = $generator->generate();

        // forces count in bytes
        $size = mb_strlen($key, '8bit');

        self::assertSame(32, $size);
    }
}
