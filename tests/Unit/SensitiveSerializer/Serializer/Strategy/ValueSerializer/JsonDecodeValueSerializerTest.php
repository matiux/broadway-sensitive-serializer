<?php

declare(strict_types=1);

namespace Tests\Unit\SensitiveSerializer\Serializer\Strategy\ValueSerializer;

use InvalidArgumentException;
use Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\ValueSerializer\JsonDecodeValueSerializer;
use PHPUnit\Framework\TestCase;
use stdClass;

class JsonDecodeValueSerializerTest extends TestCase
{
    public function invalidValuesProvider(): array
    {
        return [
            [new stdClass(), 'ValueSerializer::serialize() cannot accept objects'],
            ["\xB1\x31", 'Malformed UTF-8 characters, possibly incorrectly encoded'],
            // [['foo' => new stdClass()]],
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

        (new JsonDecodeValueSerializer())->serialize($value);
    }

    /**
     * @test
     */
    public function it_should_throw_exception_if_value_to_deserialize_is_invalid(): void
    {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage(sprintf('Error: %s - Syntax error', JSON_ERROR_SYNTAX));

        (new JsonDecodeValueSerializer())->deserialize("{'foo': 'bar'}");
    }

    public function validValuesProvider(): array
    {
        return [
            ['foo', '"foo"'],
            [1, '1'],
            [0, '0'],
            [0.5, '0.5'],
            [1.0, '1.0'],
            [null, 'null'],
            [['foo' => 'bar'], '{"foo":"bar"}'],
            [['foo' => ['bar' => 12.3]], '{"foo":{"bar":12.3}}'],
            [false, 'false']
        ];
    }

    /**
     * @test
     * @dataProvider validValuesProvider
     *
     * @param mixed  $nonSerializedValue
     * @param string $expectedSerializedValue
     */
    public function it_should_serialize_valid_values($nonSerializedValue, string $expectedSerializedValue): void
    {
        $serialized = (new JsonDecodeValueSerializer())->serialize($nonSerializedValue);

        self::assertSame($expectedSerializedValue, $serialized);

        $deserialized = (new JsonDecodeValueSerializer())->deserialize($serialized);

        self::assertSame($nonSerializedValue, $deserialized);
    }
}
