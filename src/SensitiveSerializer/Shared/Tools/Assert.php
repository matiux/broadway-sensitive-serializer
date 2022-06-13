<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\Shared\Tools;

use InvalidArgumentException;
use function sprintf;

class Assert extends \Webmozart\Assert\Assert
{
    /**
     * @psalm-assert array<int, mixed>|scalar|null $value
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function isSerializable($value, string $message = ''): void
    {
        if (is_object($value) || Util::isAssociativeArray($value)) {
            static::reportInvalidArgument(sprintf(
                $message ?: 'Expected a array<int, mixed>|scalar|null. Got: %s',
                static::typeToString($value)
            ));
        }
    }

    /**
     * @psalm-type SerializedObject = array{class: class-string, payload: array{id: string}}
     * @psalm-assert SerializedObject $value
     *
     * @param array $value
     *
     * @throws InvalidArgumentException
     */
    public static function isSerializedObject(array $value): void
    {
        self::keyExists($value, 'class', "Key 'class' should be set.");
        self::keyExists($value, 'payload', "Key 'payload' should be set.");
        self::isArray($value['payload'], 'Payload must be an array');
        self::keyExists($value['payload'], 'id', "Key 'id' should be set in payload.");
    }
}
