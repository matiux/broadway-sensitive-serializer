<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\Serializer;

use Broadway\Serializer\Serializable;
use Broadway\Serializer\Serializer;
use Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\SensitizerStrategy;
use Webmozart\Assert\Assert;

class SensitiveSerializer extends BroadwaySerializerDecorator
{
    private SensitizerStrategy $sensitizer;

    public function __construct(
        Serializer $serializer,
        SensitizerStrategy $sensitizer
    ) {
        parent::__construct($serializer);

        $this->sensitizer = $sensitizer;
    }

    /**
     * {@inheritDoc}
     */
    public function serialize($object): array
    {
        Assert::isInstanceOf($object, Serializable::class);

        $serialized = parent::serialize($object);

        return $this->sensitizer->sensitize($serialized);
    }

    /**
     * {@inheritDoc}
     */
    public function deserialize(array $serializedObject): Serializable
    {
        $desensitezedSerializedObject = $this->sensitizer->desensitize($serializedObject);

        $deserialized = parent::deserialize($desensitezedSerializedObject);

        Assert::isInstanceOf($deserialized, Serializable::class);

        return $deserialized;
    }
}
