<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\WholePayloadStrategy;

use Assert\AssertionFailedException;
use Exception;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Exception\AggregateKeyException;
use Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\SensitizerStrategy;
use Matiux\Broadway\SensitiveSerializer\Serializer\Validator;

class WholePayloadSensitizerManager implements SensitizerStrategy
{
    private WholePayloadSensitizerRegistry $wholePayloadSensitizerRegistry;
    private WholePayloadSensitizer $wholePayloadSensitizer;

    public function __construct(
        WholePayloadSensitizerRegistry $wholePayloadSensitizerRegistry,
        WholePayloadSensitizer $wholePayloadSensitizer
    ) {
        $this->wholePayloadSensitizerRegistry = $wholePayloadSensitizerRegistry;
        $this->wholePayloadSensitizer = $wholePayloadSensitizer;
    }

    /**
     * {@inheritDoc}
     *
     * @throws AggregateKeyException|AssertionFailedException|Exception
     */
    public function sensitize(array $serializedObject): array
    {
        Validator::validateSerializedObject($serializedObject);

        if ($this->wholePayloadSensitizerRegistry->supports($serializedObject['class'])) {
            $serializedObject = $this->wholePayloadSensitizer->sensitize($serializedObject);
            Validator::validateSerializedObject($serializedObject);
        }

        return $serializedObject;
    }

    /**
     * {@inheritDoc}
     *
     * @throws AggregateKeyException|AssertionFailedException|Exception
     */
    public function desensitize(array $sensitiveSerializedObject): array
    {
        Validator::validateSerializedObject($sensitiveSerializedObject);

        if ($this->wholePayloadSensitizerRegistry->supports($sensitiveSerializedObject['class'])) {
            $sensitiveSerializedObject = $this->wholePayloadSensitizer->desensitize($sensitiveSerializedObject);
            Validator::validateSerializedObject($sensitiveSerializedObject);
        }

        return $sensitiveSerializedObject;
    }
}
