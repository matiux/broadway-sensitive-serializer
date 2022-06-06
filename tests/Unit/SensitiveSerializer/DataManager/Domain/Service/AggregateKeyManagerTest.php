<?php

declare(strict_types=1);

namespace Tests\Unit\SensitiveSerializer\DataManager\Domain\Service;

use Exception;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Aggregate\AggregateKey;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Aggregate\AggregateKeys;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Exception\AggregateKeyNotFoundException;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Service\AggregateKeyManager;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Service\KeyGenerator;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Service\SensitiveDataManager;
use Matiux\Broadway\SensitiveSerializer\Example\Shared\Key;
use Mockery;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class AggregateKeyManagerTest extends TestCase
{
    private UuidInterface $aggregateId;
    private KeyGenerator $keyGenerator;
    private AggregateKeys  $aggregateKeys;
    private SensitiveDataManager $sensitiveDataManager;
    private AggregateKeyManager $aggregateKeyManager;

    protected function setUp(): void
    {
        $this->aggregateId = Uuid::uuid4();

        $this->keyGenerator = Mockery::mock(KeyGenerator::class)
            ->shouldReceive('generate')->andReturn(Key::AGGREGATE_KEY)
            ->getMock();

        $this->aggregateKeys = Mockery::spy(AggregateKeys::class);

        $this->sensitiveDataManager = Mockery::mock(SensitiveDataManager::class)
            ->shouldReceive('encrypt')->with(Key::AGGREGATE_KEY, Key::AGGREGATE_MASTER_KEY)->andReturn(Key::ENCRYPTED_AGGREGATE_KEY)
            ->getMock();

        $this->aggregateKeyManager = new AggregateKeyManager(
            $this->keyGenerator,
            $this->aggregateKeys,
            $this->sensitiveDataManager,
            Key::AGGREGATE_MASTER_KEY
        );
    }

    /**
     * @psalm-suppress UndefinedInterfaceMethod, MixedMethodCall
     * @test
     */
    public function it_should_create_an_encrypted_aggregate_key(): void
    {
        $aggregateKey = $this->aggregateKeyManager->createAggregateKey($this->aggregateId);

        $this->aggregateKeys->shouldHaveReceived('add', [AggregateKey::class])->once();
        self::assertTrue($aggregateKey->aggregateId()->equals($this->aggregateId));
        self::assertSame(Key::ENCRYPTED_AGGREGATE_KEY, (string) $aggregateKey);
    }

    /**
     * @test
     */
    public function it_should_throw_an_exception_trying_to_get_a_non_existing_aggregate_key(): void
    {
        self::expectException(AggregateKeyNotFoundException::class);
        self::expectExceptionMessage(sprintf('AggregateKey not found for aggregate %s', (string) $this->aggregateId));

        $this->aggregateKeyManager->revealAggregateKey($this->aggregateId);
    }

    /**
     * @psalm-suppress MixedMethodCall, MixedOperand, MixedAssignment
     * @test
     */
    public function it_should_forget_a_key(): void
    {
        $aggregateKey = AggregateKey::create($this->aggregateId, 's3cr3tK31');

        $aggregateKeys = Mockery::spy(AggregateKeys::class)
            ->shouldReceive('withAggregateId')->with($this->aggregateId)->andReturnUsing(function () use ($aggregateKey) {
                static $counter = 0;
                ++$counter;

                switch ($counter) {
                    case 1: throw new AggregateKeyNotFoundException();
                    case 2: case 3: case 4: return $aggregateKey;
                    default: throw new Exception('Should never reach this.');
                }
            })
            ->getMock();

        $aggregateKeyManager = new AggregateKeyManager(
            $this->keyGenerator,
            $aggregateKeys,
            $this->sensitiveDataManager,
            Key::AGGREGATE_MASTER_KEY
        );

        $aggregateKeyManager->createAggregateKey($this->aggregateId);
        $aggregateKey = $aggregateKeyManager->obtainAggregateKeyOrFail($this->aggregateId);

        self::assertTrue($aggregateKey->exists());

        $aggregateKeyManager->forget($this->aggregateId);

        $aggregateKeys->shouldHaveReceived('update', [$aggregateKey])->once();

        $aggregateKey = $aggregateKeyManager->obtainAggregateKeyOrFail($this->aggregateId);
        self::assertFalse($aggregateKey->exists());
    }
}
