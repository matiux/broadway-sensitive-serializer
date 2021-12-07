<?php

declare(strict_types=1);

namespace Tests\Unit\SensitiveSerializer\Serializer\Strategy\WholePayloadStrategy;

use Exception;
use Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\WholePayloadStrategy\WholePayloadSensitizerRegistry;
use PHPUnit\Framework\TestCase;
use stdClass;
use Tests\Support\SensitiveSerializer\MyEvent;

class WholePayloadSensitizerRegistryTest extends TestCase
{
    /**
     * @return string[][]
     */
    public function invalidClassStringProvider(): array
    {
        return [
            ['Foo'],
            ['Foo.Bar'],
        ];
    }

    /**
     * @test
     *
     * @param string $classString
     * @dataProvider invalidClassStringProvider
     */
    public function it_should_throw_exception_over_an_invalid_class_string(string $classString): void
    {
        self::expectException(Exception::class);
        self::expectExceptionMessage(sprintf('Invalid class string: %s', $classString));

        /** @var class-string[] $eventToSensitize */
        $eventToSensitize = [$classString];

        new WholePayloadSensitizerRegistry($eventToSensitize);
    }

    /**
     * @test
     */
    public function it_should_tell_us_if_an_event_is_to_be_sensitized_or_not(): void
    {
        $registry = new WholePayloadSensitizerRegistry([MyEvent::class]);

        self::assertTrue($registry->supports(MyEvent::class));
        self::assertFalse($registry->supports(stdClass::class));
    }
}
