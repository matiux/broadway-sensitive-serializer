<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\Serializer\ValueSerializer;

use InvalidArgumentException;

class JsonDecodeValueSerializer extends ValueSerializer
{
    /**
     * {@inheritDoc}
     */
    public function deserialize(string $value)
    {
        /** @var null|array<int, mixed>|scalar $decodedValue */
        $decodedValue = json_decode($value, true);

        if (JSON_ERROR_NONE !== ($lastError = json_last_error())) {
            $msg = sprintf('Error: %s - %s', $lastError, json_last_error_msg());

            throw new InvalidArgumentException($msg);
        }

        return $decodedValue;
    }

    /**
     * {@inheritDoc}
     */
    protected function doSerialize($value): string
    {
        /**
         * @psalm-suppress DocblockTypeContradiction
         */
        if (is_object($value)) {
            throw new InvalidArgumentException('ValueSerializer::serialize() cannot accept objects');
        }

        $encodedValue = json_encode($value, JSON_PRESERVE_ZERO_FRACTION);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new InvalidArgumentException(json_last_error_msg());
        }

        return $encodedValue;
    }
}
