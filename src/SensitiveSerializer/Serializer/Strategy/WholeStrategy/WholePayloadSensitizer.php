<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\WholeStrategy;

use Assert\Assertion as Assert;
use Assert\AssertionFailedException;
use BadMethodCallException;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Service\AggregateKeyManager;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Service\SensitiveDataManager;
use Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\PayloadSensitizer;
use Matiux\Broadway\SensitiveSerializer\Serializer\ValueSerializer\ValueSerializer;

final class WholePayloadSensitizer extends PayloadSensitizer
{
    private string $payloadIdKey;

    /** @var string[] */
    private array $excludedKeys;

    /**
     * @param SensitiveDataManager $sensitiveDataManager
     * @param AggregateKeyManager  $aggregateKeyManager
     * @param ValueSerializer      $valueSerializer
     * @param bool                 $automaticAggregateKeyCreation
     * @param string[]             $excludedKeys
     * @param string               $excludedIdKey
     */
    public function __construct(
        SensitiveDataManager $sensitiveDataManager,
        AggregateKeyManager $aggregateKeyManager,
        ValueSerializer $valueSerializer,
        bool $automaticAggregateKeyCreation = true,
        array $excludedKeys = ['occurred_at'],
        string $excludedIdKey = 'id'
    ) {
        parent::__construct($sensitiveDataManager, $aggregateKeyManager, $valueSerializer, $automaticAggregateKeyCreation);

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
     * @throws AssertionFailedException
     */
    protected function generateSensitizedPayload(): array
    {
        $toSensitize = $this->getPayload();

        $this->validatePayload($toSensitize);
        $this->removeNotSensitiveKeys($toSensitize);

        /** @var array<array-key, string> $toSensitize */
        foreach ($toSensitize as $key => $value) {
            $toSensitize[$key] = $this->encryptValue($value);
        }

        $sensitizedPayload = $toSensitize + $this->getPayload();

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
        $sensibleData = $this->getPayload();

        $this->validatePayload($sensibleData);

        $this->removeNotSensitiveKeys($sensibleData);

        /** @var array<array-key, string> $sensibleData */
        foreach ($sensibleData as $key => $value) {
            $sensibleData[$key] = $this->decryptValue($value);
        }

        $desensitizedPayload = $sensibleData + $this->getPayload();

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
