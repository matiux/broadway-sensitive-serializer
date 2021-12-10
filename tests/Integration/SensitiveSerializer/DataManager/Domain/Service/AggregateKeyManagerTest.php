<?php

declare(strict_types=1);

namespace Tests\Integration\SensitiveSerializer\DataManager\Domain\Service;

use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Exception\DuplicatedAggregateKeyException;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Service\AggregateKeyManager;
use Matiux\Broadway\SensitiveSerializer\DataManager\Infrastructure\Domain\Aggregate\InMemoryAggregateKeys;
use Matiux\Broadway\SensitiveSerializer\DataManager\Infrastructure\Domain\Service\AES256SensitiveDataManager;
use Matiux\Broadway\SensitiveSerializer\DataManager\Infrastructure\Domain\Service\OpenSSLKeyGenerator;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Tests\Util\SensitiveSerializer\Key;

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

    /**
     * @test
     */
    public function it_should_throw_an_exception_trying_to_create_a_duplicated_key(): void
    {
        $aggregateId = Uuid::uuid4();

        self::expectException(DuplicatedAggregateKeyException::class);
        self::expectExceptionMessage(sprintf('Duplicated aggregateKey with id %s', (string) $aggregateId));

        $this->aggregateKeyManager->createAggregateKey($aggregateId);
        $this->aggregateKeyManager->createAggregateKey($aggregateId);
    }
}
