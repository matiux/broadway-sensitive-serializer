<?php

declare(strict_types=1);

namespace Tests\Unit\SensitiveSerializer\Serializer\Strategy\PartialStrategy;

use Exception;
use Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\PartialStrategy\PartialPayloadSensitizerRegistry;
use PHPUnit\Framework\TestCase;

class PartialPayloadSensitizerRegistryTest extends TestCase
{
    /**
     * @return array<array-key, array{0:array<string, list<string>>}>
     */
    public function invalidClassStringProvider(): array
    {
        return [
            [['Foo' => []]],
            [['Foo.Bar' => []]],
        ];
    }

    /**
     * @test
     * @psalm-suppress MixedArgumentTypeCoercion
     *
     * @param array $toSensitizeKeysList
     * @dataProvider invalidClassStringProvider
     */
    public function it_should_throw_exception_over_an_invalid_class_string(array $toSensitizeKeysList): void
    {
        self::expectException(Exception::class);
        self::expectExceptionMessage(sprintf('Invalid class string: %s', (string) array_key_first($toSensitizeKeysList)));

        new PartialPayloadSensitizerRegistry($toSensitizeKeysList);
    }

    /**
     * @test
     */
    public function it_should_throw_exception_when_list_of_keys_to_sensitize_is_empty(): void
    {
        self::expectException(Exception::class);
        self::expectExceptionMessage('List of keys to sensitize cannot be empty');

        $toSensitizeKeysList = [
            EventOne::class => [],
        ];

        new PartialPayloadSensitizerRegistry($toSensitizeKeysList);
    }

    /**
     * @return array<array-key, array{0:array<string, list<mixed>>, 1: string}>
     */
    public function invalidListOfKeys(): array
    {
        return [
            [
                [EventOne::class => [1]],
                'PartialPayloadSensitizer needs a list of string keys. `1` is not a string',
            ],
            [
                [EventTwo::class => ['foo bar']],
                'PartialPayloadSensitizer needs a list of string keys. `foo bar` is not a valid string',
            ],
        ];
    }

    /**
     * @test
     * @psalm-suppress MixedArgumentTypeCoercion
     * @dataProvider invalidListOfKeys
     *
     * @param array  $toSensitize
     * @param string $errorMessage
     */
    public function it_should_throw_exception_when_list_of_keys_to_sensitize_is_invalid(array $toSensitize, string $errorMessage): void
    {
        self::expectException(Exception::class);
        self::expectExceptionMessage($errorMessage);

        new PartialPayloadSensitizerRegistry($toSensitize);
    }

    /**
     * @test
     */
    public function it_should_resolve_event_name_if_exists(): void
    {
        $toSensitize = [
            EventOne::class => ['surname', 'email'],
        ];

        $registry = new PartialPayloadSensitizerRegistry($toSensitize);

        $keys = $registry->resolveItemFor(EventOne::class);

        self::assertSame(['surname', 'email'], $keys);
    }

    /**
     * @test
     */
    public function it_should_resolve_null_if_event_name_not_exists(): void
    {
        $registry = new PartialPayloadSensitizerRegistry([]);

        $keys = $registry->resolveItemFor(EventOne::class);

        self::assertNull($keys);
    }
}

class EventOne
{
}
class EventTwo
{
}
