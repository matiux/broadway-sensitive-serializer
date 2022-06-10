<?php

declare(strict_types=1);

namespace Tests\Integration\SensitiveSerializer\Serializer\Strategy;

use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Aggregate\AggregateKeys;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Exception\AggregateKeyNotFoundException;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Service\AggregateKeyManager;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Service\SensitiveDataManager;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Service\SensitiveTool;
use Matiux\Broadway\SensitiveSerializer\DataManager\Infrastructure\Domain\Aggregate\InMemoryAggregateKeys;
use Matiux\Broadway\SensitiveSerializer\DataManager\Infrastructure\Domain\Service\AES256SensitiveDataManager;
use Matiux\Broadway\SensitiveSerializer\DataManager\Infrastructure\Domain\Service\OpenSSLKeyGenerator;
use Matiux\Broadway\SensitiveSerializer\Example\Shared\Key;
use Matiux\Broadway\SensitiveSerializer\Serializer\ValueSerializer\JsonDecodeValueSerializer;
use Matiux\Broadway\SensitiveSerializer\Serializer\ValueSerializer\ValueSerializer;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Tests\Support\SensitiveSerializer\MyEvent;
use Tests\Support\SensitiveSerializer\MyEventBuilder;

abstract class StrategyTest extends TestCase
{
    private UuidInterface $aggregateId;
    private array $ingoingPayload;

    private AES256SensitiveDataManager $sensitiveDataManager;
    private InMemoryAggregateKeys $aggregateKeys;
    private AggregateKeyManager $aggregateKeyManager;
    private JsonDecodeValueSerializer $valueSerializer;

    protected function setUp(): void
    {
        $this->aggregateId = Uuid::uuid4();

        $this->ingoingPayload = [
            'class' => MyEvent::class,
            'payload' => MyEventBuilder::create((string) $this->aggregateId)->build()->serialize(),
        ];

        $this->sensitiveDataManager = new AES256SensitiveDataManager();
        $this->aggregateKeys = new InMemoryAggregateKeys();

        $this->aggregateKeyManager = new AggregateKeyManager(
            new OpenSSLKeyGenerator(),
            $this->aggregateKeys,
            $this->sensitiveDataManager,
            Key::AGGREGATE_MASTER_KEY
        );

        $this->valueSerializer = new JsonDecodeValueSerializer();
    }

    protected function getSensitiveDataManager(): SensitiveDataManager
    {
        return $this->sensitiveDataManager;
    }

    protected function getAggregateKeyManager(): AggregateKeyManager
    {
        return $this->aggregateKeyManager;
    }

    protected function getValueSerializer(): ValueSerializer
    {
        return $this->valueSerializer;
    }

    protected function getIngoingPayload(): array
    {
        return $this->ingoingPayload;
    }

    protected function getAggregateId(): UuidInterface
    {
        return $this->aggregateId;
    }

    protected function getAggregateKeys(): AggregateKeys
    {
        return $this->aggregateKeys;
    }

    /**
     * @param array{class: class-string, payload: array{id: string}} $sensitizedOutgoingPayload
     * @param string[]                                               $excludedKeys
     */
    protected function assertObjectIsSensitized(array $sensitizedOutgoingPayload, array $excludedKeys = ['id']): void
    {
        self::assertArrayHasKey('class', $sensitizedOutgoingPayload);
        self::assertArrayHasKey('payload', $sensitizedOutgoingPayload);
        self::assertArrayHasKey('id', $sensitizedOutgoingPayload['payload']);

        $sensitizedData = $sensitizedOutgoingPayload['payload'];

        foreach ($excludedKeys as $excludedKey) {
            unset($sensitizedData[$excludedKey]);
        }

        foreach ($sensitizedData as $sensitizedValue) {
            self::assertTrue(SensitiveTool::isSensitized($sensitizedValue));
        }
    }

    /**
     * @param array $sensitizedOutgoingPayload
     * @param array $excludedKeys
     *
     * @throws AggregateKeyNotFoundException
     */
    protected function assertSensitizedPayloadEqualToExpected(array $sensitizedOutgoingPayload, array $excludedKeys = ['id']): void
    {
        $decryptedAggregateKey = $this->aggregateKeyManager->revealAggregateKey($this->aggregateId);

        self::assertNotNull($decryptedAggregateKey);

        $expectedPayload = $this->buildExpectedPayload($excludedKeys, $decryptedAggregateKey, $sensitizedOutgoingPayload);

        self::assertSame($expectedPayload, $sensitizedOutgoingPayload);
    }

    protected function assertSensitizedValueSame(string $expected, string $sensitizedValue, string $decryptedAggregateKey): void
    {
        self::assertSame(
            $expected,
            $this->getValueSerializer()->deserialize(
                $this->getSensitiveDataManager()->decrypt(
                    $sensitizedValue,
                    $decryptedAggregateKey
                )
            )
        );
    }

    private function buildExpectedPayload(array $excludedKeys, string $decryptedAggregateKey, array $sensitizedOutgoingPayload): array
    {
        /** @var string[] $expectedPayload */
        $expectedPayload = (array) $this->ingoingPayload['payload'];

        foreach ($expectedPayload as $key => $value) {
            if (!in_array($key, $excludedKeys)) {
                $expectedPayload[$key] = $this->getSensitiveDataManager()->encrypt(
                    $this->getValueSerializer()->serialize($value),
                    $decryptedAggregateKey
                );
            }
        }

        return [
            'class' => $sensitizedOutgoingPayload['class'],
            'payload' => $expectedPayload + (array) $sensitizedOutgoingPayload['payload'],
        ];
    }
}
