<?php

declare(strict_types=1);

namespace SensitizedEventStore\Dbal\SensitiveDataManager\Domain\Exception;

use Ramsey\Uuid\UuidInterface;
use SensitizedEventStore\Dbal\Common\Domain\Exception\DomainException;
use Throwable;

class AggregateKeyException extends DomainException
{
    public static function keyNotFound(UuidInterface $aggregateId, Throwable $previous = null): self
    {
        return new self(sprintf('AggregateKey not found for aggregate %s', (string) $aggregateId), 0, $previous);
    }
}
