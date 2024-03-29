<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\Serializer\ValueSerializer;

use Matiux\Broadway\SensitiveSerializer\Shared\Tools\Util;
use phpDocumentor\Reflection\Types\Scalar;

abstract class ValueSerializer
{
    /**
     * @param null|array<int, mixed>|scalar $value
     *
     * @throws \InvalidArgumentException
     *
     * @return list<string>|string
     */
    public function serialize($value)
    {
        $this->validate($value);

        if (is_array($value)) {
            return $this->serializeArray($value);
        }

        return $this->doSerialize($value);
    }

    /**
     * @param array $value
     *
     * @return list<string>
     */
    private function serializeArray(array $value): array
    {
        $encodedValue = [];
        /** @var null|array<int, mixed>|scalar $item */
        foreach ($value as $item) {
            $encodedValue[] = $this->doSerialize($item);
        }

        return $encodedValue;
    }

    /**
     * @param null|array<int, mixed>|scalar $value
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    abstract protected function doSerialize($value): string;

    /**
     * @param list<string>|string $value
     *
     * @throws \InvalidArgumentException
     *
     * @return null|list<null|scalar>|scalar
     */
    public function deserialize($value)
    {
        $this->validate($value);

        if (is_array($value)) {
            return $this->deserializeArray($value);
        }

        return $this->doDeserialize($value);
    }

    /**
     * @param list<string> $value
     *
     * @return list<null|scalar>
     */
    private function deserializeArray(array $value): array
    {
        $decodedValue = [];

        foreach ($value as $item) {
            $decodedValue[] = $this->doDeserialize($item);
        }

        return $decodedValue;
    }

    /**
     * @param string $value
     *
     * @return null|scalar
     */
    abstract protected function doDeserialize(string $value);

    /**
     * @param mixed $value
     */
    private function validate($value): void
    {
        if (Util::isAssociativeArray($value)) {
            throw new \InvalidArgumentException('You cannot serialize an associative array');
        }

        /**
         * @psalm-suppress DocblockTypeContradiction
         */
        if (is_object($value)) {
            throw new \InvalidArgumentException('ValueSerializer::serialize() cannot accept objects');
        }
    }
}
