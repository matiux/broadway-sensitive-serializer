<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\PartialStrategy;

use Assert\AssertionFailedException;
use Exception;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Exception\AggregateKeyEmptyException;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Exception\AggregateKeyNotFoundException;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Exception\DuplicatedAggregateKeyException;
use Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\SensitizerStrategy;
use Matiux\Broadway\SensitiveSerializer\Serializer\Validator;

final class PartialStrategy implements SensitizerStrategy
{
    private PartialPayloadSensitizerRegistry $partialPayloadSensitizerRegistry;
    private PartialPayloadSensitizer $partialPayloadSensitizer;

    public function __construct(
        PartialPayloadSensitizerRegistry $partialPayloadSensitizerRegistry,
        PartialPayloadSensitizer $partialPayloadSensitizer
    ) {
        $this->partialPayloadSensitizerRegistry = $partialPayloadSensitizerRegistry;
        $this->partialPayloadSensitizer = $partialPayloadSensitizer;
    }

    /**
     * @param array $serializedObject
     *
     * @throws AggregateKeyEmptyException
     * @throws AggregateKeyNotFoundException
     * @throws DuplicatedAggregateKeyException
     * @throws Exception
     * @throws AssertionFailedException
     *
     * @return array
     */
    public function sensitize(array $serializedObject): array
    {
        Validator::validateSerializedObject($serializedObject);

        if ($this->partialPayloadSensitizerRegistry->support($serializedObject['class'])) {
            $serializedObject = $this->partialPayloadSensitizer->sensitize($serializedObject);
            Validator::validateSerializedObject($serializedObject);
        }

        return $serializedObject;
    }

    /**
     * {@inheritDoc}
     *
     * @throws AssertionFailedException|Exception
     */
    public function desensitize(array $sensitiveSerializedObject): array
    {
        Validator::validateSerializedObject($sensitiveSerializedObject);

        if ($this->partialPayloadSensitizerRegistry->support($sensitiveSerializedObject['class'])) {
            $sensitiveSerializedObject = $this->partialPayloadSensitizer->desensitize($sensitiveSerializedObject);
            Validator::validateSerializedObject($sensitiveSerializedObject);
        }

        return $sensitiveSerializedObject;
    }
}
