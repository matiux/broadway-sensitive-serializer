<?php

declare(strict_types=1);

namespace Tests\Unit\SensitiveSerializer\DataManager\Domain\Aggregate;

use DateTimeImmutable;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Aggregate\AggregateKey;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

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
}
