<?php

declare(strict_types=1);

namespace Tests\Unit\SensitiveSerializer\DataManager\Domain\Service;

use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Service\SensitiveTool;
use PHPUnit\Framework\TestCase;

class SensitiveToolTest extends TestCase
{
    /**
     * @return list<array{0: string, 1: bool}>
     */
    public function valuesProvider(): array
    {
        return [
            ['#-#foo', true],
            ['foo', false],
            ['', false],
        ];
    }

    /**
     * @test
     *
     * @dataProvider valuesProvider
     */
    public function it_should_return_true_if_value_is_sensitized(string $value, bool $isSensitizedCheck): void
    {
        $isSensitized = SensitiveTool::isSensitized($value);

        self::assertSame($isSensitized, $isSensitizedCheck);
    }
}
