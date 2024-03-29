<?php

declare(strict_types=1);

namespace Tests\Unit\SensitiveSerializer\Shared\Tools;

use Matiux\Broadway\SensitiveSerializer\Shared\Tools\Assert;
use PHPUnit\Framework\TestCase;

class AssertTest extends TestCase
{
    /**
     * @psalm-suppress all
     *
     * @test
     */
    public function it_should_throw_exception_if_value_invalid(): void
    {
        self::expectException(\InvalidArgumentException::class);

        $a = new \stdClass();
        Assert::isSerializable($a);
    }
}
