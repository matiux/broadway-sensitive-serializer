<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\Serializer\ValueSerializer;

use InvalidArgumentException;
use Throwable;

class ValueSerializerException extends InvalidArgumentException
{
    public static function createFrom(Throwable $e): self
    {
        $message = sprintf('Error: %s - %s', $e->getCode(), $e->getMessage());

        return new self($message, 0, $e);
    }
}
