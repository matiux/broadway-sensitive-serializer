<?php

declare(strict_types=1);

namespace Tests\Unit\SensitiveSerializer\Serializer\ValueSerializer;

use InvalidArgumentException;
use Matiux\Broadway\SensitiveSerializer\Serializer\ValueSerializer\JsonValueSerializer;
use PHPUnit\Framework\TestCase;
use stdClass;

class JsonValueSerializerTest extends TestCase
{
    /**
     * @return array<array{0: mixed, 1: string}>
     */
    public function invalidValuesProvider(): array
    {
        return [
            [new stdClass(), 'ValueSerializer::serialize() cannot accept objects'],
            ["\xB1\x31", 'Malformed UTF-8 characters, possibly incorrectly encoded'],
            [['a' => 'a', 'b' => 12, 'c' => 1.0], 'You cannot serialize an associative array'],
            // [['foo' => new stdClass()]], TODO
        ];
    }

    /**
     * @test
     * @dataProvider invalidValuesProvider
     *
     * @param mixed  $value
     * @param string $errorMessage
     */
    public function it_should_throw_exception_if_value_to_serialize_is_invalid($value, string $errorMessage): void
    {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessageMatches(sprintf('/%s/', preg_quote($errorMessage)));

        /** @psalm-suppress MixedArgument */
        (new JsonValueSerializer())->serialize($value);
    }

    /**
     * @test
     */
    public function it_should_throw_exception_if_value_to_deserialize_is_invalid(): void
    {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage(sprintf('Error: %s - Syntax error', JSON_ERROR_SYNTAX));

        (new JsonValueSerializer())->deserialize("{'foo': 'bar'}");
    }

    /**
     * @return array<array{0: null|array<int, mixed>|scalar, 1: string|string[]}>
     */
    public function validValuesProvider(): array
    {
        return [
            ['foo', '"foo"'],
            [1, '1'],
            [0, '0'],
            [0.5, '0.5'],
            [1.0, '1.0'],
            [null, 'null'],
            [['foo', 1, 12.0, 12.5], ['"foo"', '1', '12.0', '12.5']],
            [false, 'false'],
        ];
    }

    /**
     * @test
     * @dataProvider validValuesProvider
     * @testdox It should serialize $nonSerializedValue that results in $expectedSerializedValue
     *
     * @param null|array<int, mixed>|scalar $nonSerializedValue
     * @param array|string                  $expectedSerializedValue
     */
    public function it_should_serialize_valid_values($nonSerializedValue, $expectedSerializedValue): void
    {
        $serialized = (new JsonValueSerializer())->serialize($nonSerializedValue);

        self::assertSame($expectedSerializedValue, $serialized);

        $deserialized = (new JsonValueSerializer())->deserialize($serialized);

        self::assertSame($nonSerializedValue, $deserialized);
    }
}
