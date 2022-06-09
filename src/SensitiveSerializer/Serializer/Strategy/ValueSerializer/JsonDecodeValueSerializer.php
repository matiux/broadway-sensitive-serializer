<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\ValueSerializer;

use InvalidArgumentException;

class JsonDecodeValueSerializer implements ValueSerializer
{
    /**
     * {@inheritDoc}
     */
    public function serialize($value): string
    {
        if (is_object($value)) {
            throw new InvalidArgumentException('ValueSerializer::serialize() cannot accept objects');
        }

        $encodedValue = json_encode($value, JSON_PRESERVE_ZERO_FRACTION);

        if (false === $encodedValue) {
            throw new InvalidArgumentException('Serialization failed');
        }

        if (json_last_error() === JSON_ERROR_NONE) {
            throw new InvalidArgumentException(json_last_error_msg());
        }

        return $encodedValue;
    }

    /**
     * {@inheritDoc}
     */
    public function deserialize(string $value)
    {
        // TODO: Implement deserialize() method.
    }
}
