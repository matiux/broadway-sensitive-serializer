<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\Serializer;

use Assert\AssertionFailedException;
use Broadway\Serializer\Serializable;
use Broadway\Serializer\Serializer;
use Broadway\Serializer\SimpleInterfaceSerializer;
use Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\SensitizerStrategy;
use Webmozart\Assert\Assert;

class SensitiveSerializer implements Serializer
{
    private SimpleInterfaceSerializer $serializer;
    private SensitizerStrategy $sensitizer;

    public function __construct(
        SimpleInterfaceSerializer $serializer,
        SensitizerStrategy $sensitizer
    ) {
        $this->serializer = $serializer;
        $this->sensitizer = $sensitizer;
    }

    /**
     * {@inheritDoc}
     *
     * @throws AssertionFailedException
     */
    public function serialize($object): array
    {
        Assert::isInstanceOf($object, Serializable::class);

        $serialized = $this->serializer->serialize($object);

        Validator::validateSerializedObject($serialized);

        return $this->sensitizer->sensitize($serialized);
    }

    /**
     * {@inheritDoc}
     *
     * @throws AssertionFailedException
     */
    public function deserialize(array $serializedObject): Serializable
    {
        Validator::validateSerializedObject($serializedObject);

        $desensitezedSerializedObject = $this->sensitizer->desensitize($serializedObject);

        $deserialized = $this->serializer->deserialize($desensitezedSerializedObject);

        Assert::isInstanceOf($deserialized, Serializable::class);

        return $deserialized;
    }
}
