<?php

declare(strict_types=1);

namespace Tests\Support;

use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Aggregate\AggregateKey;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Aggregate\AggregateKeys;
use Ramsey\Uuid\Nonstandard\Uuid;
use Ramsey\Uuid\UuidInterface;

class InMemoryAggregateKeys implements AggregateKeys
{
    /** @var array<string, AggregateKey> */
    private array $aggregateKeys = [];

    public function add(AggregateKey $aggregateKey): void
    {
        $this->aggregateKeys[(string) $aggregateKey->aggregateId()] = $aggregateKey;
    }

    public function withAggregateId(UuidInterface $aggregateId): ?AggregateKey
    {
        foreach ($this->aggregateKeys as $storedAggregateId => $aggregateKey) {
            if ($aggregateId->equals(Uuid::fromString($storedAggregateId))) {
                return $aggregateKey;
            }
        }

        return null;
    }

    public function update(AggregateKey $aggregateKey): void
    {
        $this->aggregateKeys[(string) $aggregateKey->aggregateId()] = $aggregateKey;
    }
}
