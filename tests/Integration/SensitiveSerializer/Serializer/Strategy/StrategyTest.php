<?php

declare(strict_types=1);

namespace Tests\Integration\SensitiveSerializer\Serializer\Strategy;

use Adbar\Dot;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Aggregate\AggregateKeys;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Service\AggregateKeyManager;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Service\SensitiveDataManager;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Service\SensitiveTool;
use Matiux\Broadway\SensitiveSerializer\DataManager\Infrastructure\Domain\Aggregate\InMemoryAggregateKeys;
use Matiux\Broadway\SensitiveSerializer\DataManager\Infrastructure\Domain\Service\AES256SensitiveDataManager;
use Matiux\Broadway\SensitiveSerializer\DataManager\Infrastructure\Domain\Service\OpenSSLKeyGenerator;
use Matiux\Broadway\SensitiveSerializer\Example\Shared\Domain\Aggregate\UserId;
use Matiux\Broadway\SensitiveSerializer\Example\Shared\Domain\Event\UserCreated;
use Matiux\Broadway\SensitiveSerializer\Example\Shared\Key;
use Matiux\Broadway\SensitiveSerializer\Serializer\ValueSerializer\JsonValueSerializer;
use Matiux\Broadway\SensitiveSerializer\Serializer\ValueSerializer\ValueSerializer;
use Matiux\Broadway\SensitiveSerializer\Shared\Tools\Assert;
use PHPUnit\Framework\TestCase;
use Tests\Support\SensitiveSerializer\UserCreatedBuilder;

abstract class StrategyTest extends TestCase
{
    private UserId $userId;
    private array $ingoingPayload;

    private AES256SensitiveDataManager $sensitiveDataManager;
    private InMemoryAggregateKeys $aggregateKeys;
    private AggregateKeyManager $aggregateKeyManager;
    private JsonValueSerializer $valueSerializer;

    protected function setUp(): void
    {
        $this->userId = UserId::create();

        $event = UserCreatedBuilder::create($this->userId)->build()->serialize();
        ksort($event);

        $this->ingoingPayload = [
            'class' => UserCreated::class,
            'payload' => $event,
        ];

        $this->sensitiveDataManager = new AES256SensitiveDataManager();
        $this->aggregateKeys = new InMemoryAggregateKeys();

        $this->aggregateKeyManager = new AggregateKeyManager(
            new OpenSSLKeyGenerator(),
            $this->aggregateKeys,
            $this->sensitiveDataManager,
            Key::AGGREGATE_MASTER_KEY
        );

        $this->valueSerializer = new JsonValueSerializer();
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

    protected function getUserId(): UserId
    {
        return $this->userId;
    }

    protected function getAggregateKeys(): AggregateKeys
    {
        return $this->aggregateKeys;
    }

    /**
     * @param array    $sensitizedOutgoingPayload
     * @param string[] $toSensitizeKeys
     * @param string[] $toExcludeKeys
     */
    protected function assertObjectIsSensitized(array $sensitizedOutgoingPayload, array $toSensitizeKeys = [], array $toExcludeKeys = []): void
    {
        Assert::isSerializedObject($sensitizedOutgoingPayload);

        $sensitizedPayload = new Dot($sensitizedOutgoingPayload['payload']);

        if (!empty($toSensitizeKeys)) {
            foreach ($toSensitizeKeys as $key) {
                /** @var list<string>|string $sensitizedValue */
                $sensitizedValue = $sensitizedPayload->get($key);

                foreach ((array) $sensitizedValue as $item) {
                    self::assertTrue(SensitiveTool::isSensitized($item));
                }
            }
        } else {
            foreach ($sensitizedPayload->flatten() as $key => $value) {
                if (!in_array($key, $toExcludeKeys)) {
                    self::assertIsString($value);
                    self::assertTrue(SensitiveTool::isSensitized($value));
                }
            }
        }
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
