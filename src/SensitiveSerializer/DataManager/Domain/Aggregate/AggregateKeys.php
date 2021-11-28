<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Aggregate;

use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Exception\DuplicatedAggregateKeyException;
use Ramsey\Uuid\UuidInterface;

interface AggregateKeys
{
    /**
     * @param AggregateKey $aggregateKey
     *
     * @throws DuplicatedAggregateKeyException
     */
    public function add(AggregateKey $aggregateKey): void;

    /**
     * @param UuidInterface $aggregateId
     *
     * @return null|AggregateKey
     */
    public function withAggregateId(UuidInterface $aggregateId): ?AggregateKey;

    public function update(AggregateKey $aggregateKey): void;
}
