<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Exception;

use Matiux\Broadway\SensitiveSerializer\Shared\Domain\Exception\DomainException;
use Ramsey\Uuid\UuidInterface;
use Throwable;

class DuplicatedAggregateKeyException extends DomainException
{
    public static function create(UuidInterface $aggregateId, Throwable $previous = null): self
    {
        return new self(sprintf('Duplicated aggregateKey with id %s', (string) $aggregateId), 0, $previous);
    }
}
