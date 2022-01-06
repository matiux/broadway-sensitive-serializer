<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\WholePayloadStrategy;

use Assert\Assertion as Assert;
use Assert\AssertionFailedException;
use BadMethodCallException;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Service\AggregateKeyManager;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Service\SensitiveDataManager;
use Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\PayloadSensitizer;

class WholePayloadSensitizer extends PayloadSensitizer
{
    private string $payloadIdKey;

    /** @var string[] */
    private array $excludedKeys;

    /**
     * @param SensitiveDataManager $sensitiveDataManager
     * @param AggregateKeyManager  $aggregateKeyManager
     * @param bool                 $automaticAggregateKeyCreation
     * @param string[]             $excludedKeys
     * @param string               $excludedIdKey
     */
    public function __construct(
        SensitiveDataManager $sensitiveDataManager,
        AggregateKeyManager $aggregateKeyManager,
        bool $automaticAggregateKeyCreation = true,
        array $excludedKeys = ['occurred_at'],
        string $excludedIdKey = 'id'
    ) {
        parent::__construct($sensitiveDataManager, $aggregateKeyManager, $automaticAggregateKeyCreation);

        $this->payloadIdKey = $excludedIdKey;
        $this->excludedKeys = $excludedKeys;
    }

    /**
     * @return string[]
     */
    public function excludedKeys(): array
    {
        return $this->excludedKeys;
    }

    /**
     * {@inheritDoc}
     *
     * @throws AssertionFailedException
     */
    protected function generateSensitizedPayload(string $decryptedAggregateKey): array
    {
        $toSensitize = $this->payload;

        $this->validatePayload($toSensitize);

        $this->removeNotSensitiveKeys($toSensitize);

        /** @var array<array-key, string> $toSensitize */
        foreach ($toSensitize as $key => $value) {
            $toSensitize[$key] = $this->sensitiveDataManager->encrypt($value, $decryptedAggregateKey);
        }

        $sensitizedPayload = $toSensitize + $this->payload;

        ksort($sensitizedPayload);

        return $sensitizedPayload;
    }

    /**
     * @param string $decryptedAggregateKey
     *
     * @throws AssertionFailedException
     *
     * @return array
     */
    protected function generateDesensitizedPayload(string $decryptedAggregateKey): array
    {
        $sensibleData = $this->payload;

        $this->validatePayload($sensibleData);

        $this->removeNotSensitiveKeys($sensibleData);

        /** @var array<array-key, string> $sensibleData */
        foreach ($sensibleData as $key => $value) {
            $sensibleData[$key] = $this->sensitiveDataManager->decrypt($value, $decryptedAggregateKey);
        }

        $desensitizedPayload = $sensibleData + $this->payload;

        ksort($desensitizedPayload);

        return $desensitizedPayload;
    }

    public function removeNotSensitiveKeys(array &$toSensitize): void
    {
        unset($toSensitize[$this->payloadIdKey]);

        foreach ($this->excludedKeys as $excludedKey) {
            if (array_key_exists($excludedKey, $toSensitize)) {
                unset($toSensitize[$excludedKey]);
            }
        }
    }

    public function supports($subject): bool
    {
        throw new BadMethodCallException();
    }

    /**
     * @psalm-assert array{id: string} $payload
     *
     * @throws AssertionFailedException
     */
    private function validatePayload(array $payload): void
    {
        Assert::keyExists(
            $payload,
            $this->payloadIdKey,
            sprintf("Key '%s' should be set in payload when using `WholePayloadSensitizer` strategy.", $this->payloadIdKey)
        );
    }
}
