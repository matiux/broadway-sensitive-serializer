<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\Serializer\ValueSerializer;

use InvalidArgumentException;

interface ValueSerializer
{
    /**
     * @param null|array|scalar $value
     *
     * @throws InvalidArgumentException
     *
     * @return string
     */
    public function serialize($value): string;

    /**
     * @param string $value
     *
     * @throws InvalidArgumentException
     *
     * @return null|array|scalar
     */
    public function deserialize(string $value);
}
