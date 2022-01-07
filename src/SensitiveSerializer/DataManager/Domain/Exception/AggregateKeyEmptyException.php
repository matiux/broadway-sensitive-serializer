<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Exception;

use Matiux\Broadway\SensitiveSerializer\Shared\Domain\Exception\DomainException;
use Ramsey\Uuid\UuidInterface;
use Throwable;

class AggregateKeyEmptyException extends DomainException
{
    public static function create(UuidInterface $aggregateId, Throwable $previous = null): self
    {
        return new self(sprintf('Aggregate key is empty but it is required to encrypt data for aggregate %s', (string) $aggregateId), 0, $previous);
    }
}
