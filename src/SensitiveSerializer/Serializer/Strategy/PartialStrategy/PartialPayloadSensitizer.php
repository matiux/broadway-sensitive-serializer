<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\PartialStrategy;

use BadMethodCallException;
use Exception;
use LogicException;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Service\AggregateKeyManager;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Service\SensitiveDataManager;
use Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\PayloadSensitizer;

final class PartialPayloadSensitizer extends PayloadSensitizer
{
    private PartialPayloadSensitizerRegistry $partialPayloadSensitizerRegistry;

    public function __construct(
        SensitiveDataManager $sensitiveDataManager,
        AggregateKeyManager $aggregateKeyManager,
        PartialPayloadSensitizerRegistry $partialPayloadSensitizerRegistry,
        bool $automaticAggregateKeyCreation = true
    ) {
        parent::__construct($sensitiveDataManager, $aggregateKeyManager, $automaticAggregateKeyCreation);
        $this->partialPayloadSensitizerRegistry = $partialPayloadSensitizerRegistry;
    }

    /**
     * @param string $decryptedAggregateKey
     *
     * @throws Exception|LogicException
     *
     * @return array
     */
    protected function generateSensitizedPayload(string $decryptedAggregateKey): array
    {
        $toSensitizeKeys = $this->obtainToSensitizeKeysOrFail();

        $sensitizedKeys = [];
        $payload = $this->getPayload();

        foreach ($toSensitizeKeys as $key) {
            if (array_key_exists($key, $payload)) {
                $sensitizedKeys[$key] = $this->getSensitiveDataManager()->encrypt((string) $payload[$key], $decryptedAggregateKey);
            }
        }

        $sensitizedPayload = $sensitizedKeys + $payload;

        ksort($sensitizedPayload);

        return $sensitizedPayload;
    }

    /**
     * @throws Exception|LogicException
     *
     * @return string[]
     */
    private function obtainToSensitizeKeysOrFail(): array
    {
        if (!$toSensitizeKeys = $this->partialPayloadSensitizerRegistry->resolveItemFor($this->getType())) {
            throw new LogicException(
                sprintf('If you are here, the strategy should have identified correct event in registry: %s', $this->getType())
            );
        }

        return $toSensitizeKeys;
    }

    /**
     * @param string $decryptedAggregateKey
     *
     * @throws Exception|LogicException
     *
     * @return array
     */
    protected function generateDesensitizedPayload(string $decryptedAggregateKey): array
    {
        $toSensitizeKeys = $this->obtainToSensitizeKeysOrFail();

        $desensitizedKeys = [];
        $payload = $this->getPayload();

        foreach ($toSensitizeKeys as $key) {
            if (array_key_exists($key, $payload)) {
                $desensitizedKeys[$key] = $this->getSensitiveDataManager()->decrypt((string) $payload[$key], $decryptedAggregateKey);
            }
        }

        $desensitizedPayload = $desensitizedKeys + $payload;
        ksort($desensitizedPayload);

        return $desensitizedPayload;
    }

    public function supports($subject): bool
    {
        throw new BadMethodCallException();
    }
}