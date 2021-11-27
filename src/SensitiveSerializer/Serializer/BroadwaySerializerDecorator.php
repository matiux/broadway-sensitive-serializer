<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\Serializer;

use Broadway\Serializer\Serializer;

abstract class BroadwaySerializerDecorator implements Serializer
{
    private Serializer $serializer;

    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * {@inheritDoc}
     */
    public function serialize($object): array
    {
        return $this->serializer->serialize($object);
    }

    /**
     * {@inheritDoc}
     */
    public function deserialize(array $serializedObject)
    {
        return $this->serializer->deserialize($serializedObject);
    }
}
