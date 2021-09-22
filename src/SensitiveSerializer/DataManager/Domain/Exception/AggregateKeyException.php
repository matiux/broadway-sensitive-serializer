<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Exception;

use Matiux\Broadway\SensitiveSerializer\Common\Domain\Exception\DomainException;
use Ramsey\Uuid\UuidInterface;
use Throwable;

class AggregateKeyException extends DomainException
{
    public static function keyNotFound(UuidInterface $aggregateId, Throwable $previous = null): self
    {
        return new self(sprintf('AggregateKey not found for aggregate %s', (string) $aggregateId), 0, $previous);
    }
}
