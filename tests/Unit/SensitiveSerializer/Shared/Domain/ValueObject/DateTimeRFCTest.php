<?php

declare(strict_types=1);

namespace Tests\Unit\SensitiveSerializer\Shared\Domain\ValueObject;

use Matiux\Broadway\SensitiveSerializer\Example\Shared\Domain\ValueObject\DateTimeRFC;
use PHPUnit\Framework\TestCase;

class DateTimeRFCTest extends TestCase
{
    /**
     * @test
     */
    public function it_should_be_converted_in_string(): void
    {
        $date = new DateTimeRFC();
        $string = (string) $date;
        $dateAgain = DateTimeRFC::createFrom($string);

        self::assertStringEndsWith($string, (string) $dateAgain);
    }
}
