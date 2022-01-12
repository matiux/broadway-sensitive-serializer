<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\CustomPayloadStrategy;

use Assert\AssertionFailedException;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Exception\AggregateKeyEmptyException;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Exception\AggregateKeyNotFoundException;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Exception\DuplicatedAggregateKeyException;
use Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\PayloadSensitizer;
use Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\SensitizerStrategy;
use Matiux\Broadway\SensitiveSerializer\Serializer\Validator;

final class CustomPayloadSensitizerStrategy implements SensitizerStrategy
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
     * @throws AggregateKeyEmptyException|AggregateKeyNotFoundException|AssertionFailedException|DuplicatedAggregateKeyException
     *
     * @return array
     */
    public function sensitize(array $serializedObject): array
    {
        Validator::validateSerializedObject($serializedObject);

        /** @var PayloadSensitizer $sensitizer */
        if ($sensitizer = $this->customPayloadSensitizerRegistry->resolveItemFor($serializedObject)) {
            $serializedObject = $sensitizer->sensitize($serializedObject);
            Validator::validateSerializedObject($serializedObject);
        }

        return $serializedObject;
    }

    /**
     * {@inheritDoc}
     *
     * @throws AggregateKeyNotFoundException|AssertionFailedException
     */
    public function desensitize(array $sensitiveSerializedObject): array
    {
        Validator::validateSerializedObject($sensitiveSerializedObject);

        /** @var PayloadSensitizer $sensitizer */
        if ($sensitizer = $this->customPayloadSensitizerRegistry->resolveItemFor($sensitiveSerializedObject)) {
            $sensitiveSerializedObject = $sensitizer->desensitize($sensitiveSerializedObject);
            Validator::validateSerializedObject($sensitiveSerializedObject);
        }

        return $sensitiveSerializedObject;
    }
}
