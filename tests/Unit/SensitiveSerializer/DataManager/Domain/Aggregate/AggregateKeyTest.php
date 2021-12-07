<?php

declare(strict_types=1);

namespace Tests\Unit\SensitiveSerializer\DataManager\Domain\Aggregate;

use DateTimeImmutable;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Aggregate\AggregateKey;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Tests\Util\SensitiveSerializer\Key;

class AggregateKeyTest extends TestCase
{
    /**
     * @test
     */
    public function aggregate_key_exists(): void
    {
        $aggregateKey = AggregateKey::create(Uuid::uuid4(), 's3cr3tK31');
        self::assertTrue($aggregateKey->exists());
        self::assertNull($aggregateKey->cancellationDate());
    }

    /**
     * @test
     */
    public function should_delete_key(): void
    {
        $aggregateKey = AggregateKey::create(Uuid::uuid4(), 's3cr3tK31');
        self::assertTrue($aggregateKey->exists());

        $aggregateKey->delete();
        self::assertFalse($aggregateKey->exists());
        self::assertNotNull($aggregateKey->cancellationDate());
        self::assertGreaterThan($aggregateKey->cancellationDate(), new DateTimeImmutable());
    }

    /**
     * @test
     */
    public function should_serialize_aggregate_key(): void
    {
        $id = Uuid::uuid4();

        $aggregateKey = AggregateKey::create($id, 's3cr3tK31');

        $expectedSerializedKey = [
            'aggregate_uuid' => (string) $id,
            'encrypted_key' => 's3cr3tK31',
            'cancellation_date' => null,
        ];

        self::assertSame($expectedSerializedKey, $aggregateKey->serialize());
    }

    /**
     * @test
     */
    public function should_deserialize_not_canceled_aggregate_key(): void
    {
        $id = Uuid::uuid4();

        $serializedAggregateKey = AggregateKey::create($id, Key::ENCRYPTED_AGGREGATE_KEY)->serialize();

        $aggregateKey = AggregateKey::deserialize($serializedAggregateKey);

        self::assertNull($aggregateKey->cancellationDate());
        self::assertSame(Key::ENCRYPTED_AGGREGATE_KEY, (string) $aggregateKey);
        self::assertTrue($aggregateKey->aggregateId()->equals($id));
    }

    /**
     * @test
     */
    public function should_deserialize_canceled_aggregate_key(): void
    {
        $id = Uuid::uuid4();

        $aggregateKey = AggregateKey::create($id, Key::ENCRYPTED_AGGREGATE_KEY);
        $aggregateKey->delete();
        $serializedAggregateKey = $aggregateKey->serialize();

        $aggregateKey = AggregateKey::deserialize($serializedAggregateKey);

        self::assertNotNull($aggregateKey->cancellationDate());
        self::assertSame('', (string) $aggregateKey);
        self::assertTrue($aggregateKey->aggregateId()->equals($id));
    }
}
