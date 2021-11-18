<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\WholePayloadStrategy;

use Assert\Assertion as Assert;
use Assert\AssertionFailedException;
use BadMethodCallException;
use Matiux\Broadway\SensitiveSerializer\Serializer\PayloadSensitizer;

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

        $toSensitize = (string) json_encode($toSensitize);

        return [
            'id' => $this->payload['id'],
            'sensible_data' => $this->sensitiveDataManager->encrypt($toSensitize, $decryptedAggregateKey),
        ];
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
        $this->validateSensitizedPayload($this->payload);

        $sensibleData = $this->payload['sensible_data'];

        $desensitized = $this->sensitiveDataManager->decrypt($sensibleData, $decryptedAggregateKey);

        return [
            'id' => $this->payload['id'],
        ] + (array) json_decode($desensitized, true);
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

    /**
     * @psalm-assert array{id: string, sensible_data: string} $payload
     *
     * @throws AssertionFailedException
     */
    private function validateSensitizedPayload(array $payload): void
    {
        $this->validatePayload($payload);

        Assert::keyExists($this->payload, 'sensible_data', "Key 'sensible_data' should be set for desensitize payload with `WholePayloadSensitizer` strategy.");
    }
}
