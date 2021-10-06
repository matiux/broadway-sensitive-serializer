<?php

declare(strict_types=1);

namespace Tests\Integration\SensitiveSerializer\Serializer\Strategy\WholePayloadStrategy;

use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Exception\AggregateKeyException;

trait WholePayloadSensitizerTestUtil
{
    private static function assertObjectIsSensitized(array $sensitizedOutgoingPayload): void
    {
        self::assertArrayHasKey('class', $sensitizedOutgoingPayload);
        self::assertArrayHasKey('payload', $sensitizedOutgoingPayload);
        self::assertIsArray($sensitizedOutgoingPayload['payload']);
        self::assertArrayHasKey('id', $sensitizedOutgoingPayload['payload']);
        self::assertArrayHasKey('sensible_data', $sensitizedOutgoingPayload['payload']);
    }

    /**
     * @param array{class: class-string, payload: array{id: string, sensible_data: string}} $sensitizedOutgoingPayload
     *
     * @throws AggregateKeyException
     */
    private function assertSensitizedEqualToExpected(array $sensitizedOutgoingPayload): void
    {
        $decryptedAggregateKey = $this->aggregateKeyManager->revealAggregateKey($this->aggregateId);

        $expectedPayload = (array) $this->ingoingPayload['payload'];
        unset($expectedPayload['id']);

        $decodedPayload = json_decode(
            $this->sensitiveDataManager->decrypt($sensitizedOutgoingPayload['payload']['sensible_data'], $decryptedAggregateKey),
            true
        );

        self::assertSame($expectedPayload, $decodedPayload);
    }
}
