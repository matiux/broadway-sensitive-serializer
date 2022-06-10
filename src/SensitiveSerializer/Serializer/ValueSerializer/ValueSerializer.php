<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\Serializer\ValueSerializer;

use InvalidArgumentException;

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
        if (is_array($value) && $this->isAssoc($value)) {
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

    private function isAssoc(array $array): bool
    {
        $keys = array_keys($array);

        return $keys !== array_keys($keys);
    }
}
