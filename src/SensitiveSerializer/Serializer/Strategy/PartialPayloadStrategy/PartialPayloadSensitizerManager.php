<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\PartialPayloadStrategy;

use Assert\AssertionFailedException;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Exception\AggregateKeyException;
use Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\SensitizerStrategy;
use Matiux\Broadway\SensitiveSerializer\Serializer\Validator;

class PartialPayloadSensitizerManager implements SensitizerStrategy
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
        /** @var PartialPayloadSensitizer $sensitizer */
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
    public function desensitize(array $serializedObject): array
    {
        /** @var PartialPayloadSensitizer $sensitizer */
        if ($sensitizer = $this->partialPayloadSensitizerRegistry->resolveItemFor($serializedObject)) {
            $serializedObject = $sensitizer->desensitize($serializedObject);
            Validator::validateSerializedObject($serializedObject);
        }

        return $serializedObject;
    }
}
