<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\PartialStrategy;

use Exception;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Exception\AggregateKeyEmptyException;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Exception\AggregateKeyNotFoundException;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Exception\DuplicatedAggregateKeyException;
use Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\SensitizerStrategy;
use Matiux\Broadway\SensitiveSerializer\Shared\Tools\Assert;

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
     *
     * @return array
     */
    public function sensitize(array $serializedObject): array
    {
        Assert::isSerializedObject($serializedObject);

        if ($this->partialPayloadSensitizerRegistry->support($serializedObject['class'])) {
            $serializedObject = $this->partialPayloadSensitizer->sensitize($serializedObject);
            Assert::isSerializedObject($serializedObject);
        }

        return $serializedObject;
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function desensitize(array $sensitiveSerializedObject): array
    {
        Assert::isSerializedObject($sensitiveSerializedObject);

        if ($this->partialPayloadSensitizerRegistry->support($sensitiveSerializedObject['class'])) {
            $sensitiveSerializedObject = $this->partialPayloadSensitizer->desensitize($sensitiveSerializedObject);
            Assert::isSerializedObject($sensitiveSerializedObject);
        }

        return $sensitiveSerializedObject;
    }
}
