<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\Serializer\ValueSerializer;

class JsonValueSerializer extends ValueSerializer
{
    /**
     * {@inheritDoc}
     */
    protected function doSerialize($value): string
    {
        try {
            return json_encode($value, JSON_PRESERVE_ZERO_FRACTION | JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw ValueSerializerException::createFrom($e);
        }
    }

    protected function doDeserialize(string $value)
    {
        try {
            /** @var null|scalar $decodedValue */
            $decodedValue = json_decode($value, true, 512, JSON_THROW_ON_ERROR);

            return $decodedValue;
        } catch (\JsonException $e) {
            throw ValueSerializerException::createFrom($e);
        }
    }
}
