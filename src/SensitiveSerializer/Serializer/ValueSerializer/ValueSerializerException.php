<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\Serializer\ValueSerializer;

class ValueSerializerException extends \InvalidArgumentException
{
    public static function createFrom(\Throwable $e): self
    {
        $message = sprintf('Error: %s - %s', $e->getCode(), $e->getMessage());

        return new self($message, 0, $e);
    }
}
