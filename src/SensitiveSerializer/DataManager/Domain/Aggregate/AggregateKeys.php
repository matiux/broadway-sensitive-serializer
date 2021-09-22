<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Aggregate;

use Ramsey\Uuid\UuidInterface;

interface AggregateKeys
{
    public function add(AggregateKey $aggregateKey): void;

    /**
     * @param UuidInterface $aggregateId
     *
     * @return null|AggregateKey
     */
    public function withAggregateId(UuidInterface $aggregateId): ?AggregateKey;

    public function update(AggregateKey $aggregateKey): void;
}
