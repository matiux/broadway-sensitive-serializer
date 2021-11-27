<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\PartialPayloadStrategy;

use Assert\AssertionFailedException;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Exception\AggregateKeyException;
use Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\PayloadSensitizer;
use Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\SensitizerStrategy;
use Matiux\Broadway\SensitiveSerializer\Serializer\Validator;

class PartialPayloadSensitizerStrategy implements SensitizerStrategy
{
    private PartialPayloadSensitizerRegistry $partialPayloadSensitizerRegistry;

    public function __construct(PartialPayloadSensitizerRegistry $partialPayloadSensitizerRegistry)
    {
        $this->partialPayloadSensitizerRegistry = $partialPayloadSensitizerRegistry;
    }

    /**
     * {@inheritDoc}
     *
     * @throws AggregateKeyException|AssertionFailedException
     */
    public function sensitize(array $serializedObject): array
    {
        Validator::validateSerializedObject($serializedObject);

        /** @var PayloadSensitizer $sensitizer */
        if ($sensitizer = $this->partialPayloadSensitizerRegistry->resolveItemFor($serializedObject)) {
            $serializedObject = $sensitizer->sensitize($serializedObject);
            Validator::validateSerializedObject($serializedObject);
        }

        return $serializedObject;
    }

    /**
     * {@inheritDoc}
     *
     * @throws AggregateKeyException|AssertionFailedException
     */
    public function desensitize(array $sensitiveSerializedObject): array
    {
        Validator::validateSerializedObject($sensitiveSerializedObject);

        /** @var PayloadSensitizer $sensitizer */
        if ($sensitizer = $this->partialPayloadSensitizerRegistry->resolveItemFor($sensitiveSerializedObject)) {
            $sensitiveSerializedObject = $sensitizer->desensitize($sensitiveSerializedObject);
            Validator::validateSerializedObject($sensitiveSerializedObject);
        }

        return $sensitiveSerializedObject;
    }
}
