<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\WholeStrategy;

use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Exception\AggregateKeyEmptyException;
use Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\SensitizerStrategy;
use Matiux\Broadway\SensitiveSerializer\Shared\Tools\Assert;

final class WholeStrategy implements SensitizerStrategy
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
     * @throws AggregateKeyEmptyException|\Exception
     */
    public function sensitize(array $serializedObject): array
    {
        Assert::isSerializedObject($serializedObject);

        if ($this->wholePayloadSensitizerRegistry->supports($serializedObject['class'])) {
            $serializedObject = $this->wholePayloadSensitizer->sensitize($serializedObject);
            Assert::isSerializedObject($serializedObject);
        }

        return $serializedObject;
    }

    /**
     * {@inheritDoc}
     *
     * @throws \Exception
     */
    public function desensitize(array $sensitiveSerializedObject): array
    {
        Assert::isSerializedObject($sensitiveSerializedObject);

        if ($this->wholePayloadSensitizerRegistry->supports($sensitiveSerializedObject['class'])) {
            $sensitiveSerializedObject = $this->wholePayloadSensitizer->desensitize($sensitiveSerializedObject);
            Assert::isSerializedObject($sensitiveSerializedObject);
        }

        return $sensitiveSerializedObject;
    }
}
