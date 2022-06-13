<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\CustomStrategy;

use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Exception\AggregateKeyEmptyException;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Exception\AggregateKeyNotFoundException;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Exception\DuplicatedAggregateKeyException;
use Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\PayloadSensitizer;
use Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\SensitizerStrategy;
use Matiux\Broadway\SensitiveSerializer\Shared\Tools\Assert;

final class CustomStrategy implements SensitizerStrategy
{
    private CustomPayloadSensitizerRegistry $customPayloadSensitizerRegistry;

    public function __construct(CustomPayloadSensitizerRegistry $customPayloadSensitizerRegistry)
    {
        $this->customPayloadSensitizerRegistry = $customPayloadSensitizerRegistry;
    }

    /**
     * {@inheritDoc}
     *
     * @param array $serializedObject
     *
     * @throws AggregateKeyEmptyException|AggregateKeyNotFoundException|DuplicatedAggregateKeyException
     *
     * @return array
     */
    public function sensitize(array $serializedObject): array
    {
        Assert::isSerializedObject($serializedObject);

        /** @var PayloadSensitizer $sensitizer */
        if ($sensitizer = $this->customPayloadSensitizerRegistry->resolveItemFor($serializedObject)) {
            $serializedObject = $sensitizer->sensitize($serializedObject);
            Assert::isSerializedObject($serializedObject);
        }

        return $serializedObject;
    }

    /**
     * {@inheritDoc}
     */
    public function desensitize(array $sensitiveSerializedObject): array
    {
        Assert::isSerializedObject($sensitiveSerializedObject);

        /** @var PayloadSensitizer $sensitizer */
        if ($sensitizer = $this->customPayloadSensitizerRegistry->resolveItemFor($sensitiveSerializedObject)) {
            $sensitiveSerializedObject = $sensitizer->desensitize($sensitiveSerializedObject);
            Assert::isSerializedObject($sensitiveSerializedObject);
        }

        return $sensitiveSerializedObject;
    }
}
