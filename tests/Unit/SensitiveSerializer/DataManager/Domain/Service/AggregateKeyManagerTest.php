<?php

declare(strict_types=1);

namespace Tests\Unit\SensitiveSerializer\DataManager\Domain\Service;

use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Aggregate\AggregateKey;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Aggregate\AggregateKeys;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Exception\AggregateKeyNotFoundException;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Service\AggregateKeyManager;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Service\KeyGenerator;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Service\SensitiveDataManager;
use Mockery;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Tests\Util\Key;

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
        self::expectException(AggregateKeyNotFoundException::class);
        self::expectExceptionMessage(sprintf('AggregateKey not found for aggregate %s', (string) $aggregateId));

        $this->aggregateKeyManager->revealAggregateKey($aggregateId);
    }
}
