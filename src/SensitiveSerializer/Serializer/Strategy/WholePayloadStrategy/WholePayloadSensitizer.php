<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\WholePayloadStrategy;

use Assert\Assertion as Assert;
use Assert\AssertionFailedException;
use BadMethodCallException;
use Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\PayloadSensitizer;

class WholePayloadSensitizer extends PayloadSensitizer
{
    /**
     * @param string $decryptedAggregateKey
     *
     * @throws AssertionFailedException
     *
     * @return array
     */
    protected function generateSensitizedPayload(string $decryptedAggregateKey): array
    {
        $toSensitize = $this->payload;

        $this->validatePayload($toSensitize);
        unset($toSensitize['id']);

        /** @var array<array-key, string> $toSensitize */
        foreach ($toSensitize as $key => $value) {
            $toSensitize[$key] = $this->sensitiveDataManager->encrypt($value, $decryptedAggregateKey);
        }

        return ['id' => $this->payload['id']] + $toSensitize;
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
        unset($sensibleData['id']);

        /** @var array<array-key, string> $sensibleData */
        foreach ($sensibleData as $key => $value) {
            $sensibleData[$key] = $this->sensitiveDataManager->decrypt($value, $decryptedAggregateKey);
        }

        return ['id' => $this->payload['id']] + $sensibleData;
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
        Assert::keyExists($payload, 'id', "Key 'id' should be set in payload when using `WholePayloadSensitizer` strategy.");
    }
}
