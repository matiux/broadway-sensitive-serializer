<?php

declare(strict_types=1);

namespace Tests\Integration\SensitiveSerializer\DataManager\Domain\Service;

use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Service\AggregateKeyManager;
use Matiux\Broadway\SensitiveSerializer\DataManager\Infrastructure\Domain\Service\AES256SensitiveDataManager;
use Matiux\Broadway\SensitiveSerializer\DataManager\Infrastructure\Domain\Service\OpenSSLKeyGenerator;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Tests\Support\InMemoryAggregateKeys;
use Tests\Util\Key;

class AggregateKeyManagerTest extends TestCase
{
    private AggregateKeyManager $aggregateKeyManager;
    private InMemoryAggregateKeys $aggregateKeys;

    protected function setUp(): void
    {
        $this->aggregateKeys = new InMemoryAggregateKeys();

        $this->aggregateKeyManager = new AggregateKeyManager(
            new OpenSSLKeyGenerator(),
            $this->aggregateKeys,
            new AES256SensitiveDataManager(),
            Key::AGGREGATE_MASTER_KEY
        );
    }

    /**
     * @test
     */
    public function it_should_return_null_if_aggregate_does_not_have_the_key(): void
    {
        $aggregateId = Uuid::uuid4();
        $aggregateKey = $this->aggregateKeyManager->createAggregateKey($aggregateId);
        $aggregateKey->delete();

        $aggregateKey = $this->aggregateKeyManager->revealAggregateKey($aggregateId);

        self::assertNull($aggregateKey);
    }
}
