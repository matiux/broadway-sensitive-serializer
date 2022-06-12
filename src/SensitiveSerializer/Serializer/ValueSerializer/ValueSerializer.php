<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\Serializer\ValueSerializer;

use InvalidArgumentException;
use Matiux\Broadway\SensitiveSerializer\Shared\Tools\Util;

abstract class ValueSerializer
{
    /**
     * @param null|array<int, mixed>|scalar $value
     *
     * @throws InvalidArgumentException
     *
     * @return string
     */
    public function serialize($value): string
    {
        if (Util::isAssociativeArray($value)) {
            throw new InvalidArgumentException('You cannot serialize an associative array');
        }

        return $this->doSerialize($value);
    }

    /**
     * @param null|array<int, mixed>|scalar $value
     *
     * @throws InvalidArgumentException
     *
     * @return string
     */
    abstract protected function doSerialize($value): string;

    /**
     * @param string $value
     *
     * @throws InvalidArgumentException
     *
     * @return null|array<int, mixed>|scalar
     */
    abstract public function deserialize(string $value);
}
