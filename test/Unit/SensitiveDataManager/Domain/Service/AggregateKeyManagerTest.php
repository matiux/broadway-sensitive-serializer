<?php

declare(strict_types=1);

namespace Test\Unit\SensitizedEventStore\Dbal\SensitiveDataManager\Domain\Service;

use Mockery;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use SensitizedEventStore\Dbal\SensitiveDataManager\Domain\Aggregate\AggregateKey;
use SensitizedEventStore\Dbal\SensitiveDataManager\Domain\Aggregate\AggregateKeys;
use SensitizedEventStore\Dbal\SensitiveDataManager\Domain\Exception\AggregateKeyException;
use SensitizedEventStore\Dbal\SensitiveDataManager\Domain\Service\AggregateKeyManager;
use SensitizedEventStore\Dbal\SensitiveDataManager\Domain\Service\KeyGenerator;
use SensitizedEventStore\Dbal\SensitiveDataManager\Domain\Service\SensitiveDataManager;
use Test\Util\Key;

class AggregateKeyManagerTest extends TestCase
{
    private AggregateKeyManager $aggregateKeyManager;
    private AggregateKeys  $aggregateKeys;

    protected function setUp(): void
    {
        $keyGenerator = Mockery::mock(KeyGenerator::class)
            ->shouldReceive('generate')->andReturn(Key::AGGREGATE_KEY)
            ->getMock();

        $this->aggregateKeys = Mockery::spy(AggregateKeys::class);

        $sensitiveDataManager = Mockery::mock(SensitiveDataManager::class)
            ->shouldReceive('encrypt')->with(Key::AGGREGATE_KEY, Key::AGGREGATE_MASTER_KEY)->andReturn(Key::ENCRYPTED_AGGREGATE_KEY)
            ->getMock();

        $this->aggregateKeyManager = new AggregateKeyManager(
            $keyGenerator,
            $this->aggregateKeys,
            $sensitiveDataManager,
            Key::AGGREGATE_MASTER_KEY
        );
    }

    /**
     * @psalm-suppress UndefinedInterfaceMethod, MixedMethodCall
     * @test
     */
    public function it_should_create_an_encrypted_aggregate_key(): void
    {
        $aggregateId = Uuid::uuid4();

        $aggregateKey = $this->aggregateKeyManager->createAggregateKey($aggregateId);

        $this->aggregateKeys->shouldHaveReceived('add', [AggregateKey::class])->once();
        self::assertTrue($aggregateKey->aggregateId()->equals($aggregateId));
        self::assertSame(Key::ENCRYPTED_AGGREGATE_KEY, (string) $aggregateKey);
    }

    /**
     * @test
     */
    public function it_should_throw_an_exception_trying_to_get_a_non_existing_aggregate_key(): void
    {
        $aggregateId = Uuid::uuid4();
        self::expectException(AggregateKeyException::class);
        self::expectExceptionMessage(sprintf('AggregateKey not found for aggregate %s', (string) $aggregateId));

        $this->aggregateKeyManager->revealAggregateKey($aggregateId);
    }
}
