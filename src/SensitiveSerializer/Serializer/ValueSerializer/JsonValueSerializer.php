<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\Serializer\ValueSerializer;

use InvalidArgumentException;

class JsonValueSerializer extends ValueSerializer
{
    /**
     * {@inheritDoc}
     */
    protected function doSerialize($value): string
    {
        $encodedValue = json_encode($value, JSON_PRESERVE_ZERO_FRACTION);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new InvalidArgumentException(json_last_error_msg());
        }

        return $encodedValue;
    }

    protected function doDeserialize(string $value)
    {
        /** @var null|scalar $decodedValue */
        $decodedValue = json_decode($value, true);

        if (JSON_ERROR_NONE !== ($lastError = json_last_error())) {
            $msg = sprintf('Error: %s - %s', $lastError, json_last_error_msg());

            throw new InvalidArgumentException($msg);
        }

        return $decodedValue;
    }
}
