<?php

declare(strict_types=1);

namespace Tests\Integration\SensitiveSerializer\Serializer\Strategy\WholePayloadStrategy;

use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Exception\AggregateKeyException;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Service\SensitiveTool;

trait WholePayloadSensitizerTestUtil
{
    /**
     * @param array{class: class-string, payload: array<string, string>} $sensitizedOutgoingPayload
     */
    private static function assertObjectIsSensitized(array $sensitizedOutgoingPayload): void
    {
        self::assertArrayHasKey('class', $sensitizedOutgoingPayload);
        self::assertArrayHasKey('payload', $sensitizedOutgoingPayload);
        self::assertArrayHasKey('id', $sensitizedOutgoingPayload['payload']);

        $sensitizedData = $sensitizedOutgoingPayload['payload'];
        unset($sensitizedData['id']);

        foreach ($sensitizedData as $sensitizedValue) {
            self::assertTrue(SensitiveTool::isSensitized($sensitizedValue));
        }
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

        $sensitizedData = $sensitizedOutgoingPayload['payload'];
        unset($sensitizedData['id']);

        $desensitizedValues = [];
        foreach ($sensitizedData as $key => $sensitizedValue) {
            $desensitizedValues[$key] = $this->sensitiveDataManager->decrypt($sensitizedValue, $decryptedAggregateKey);
        }

        self::assertSame($expectedPayload, $desensitizedValues);
    }
}
