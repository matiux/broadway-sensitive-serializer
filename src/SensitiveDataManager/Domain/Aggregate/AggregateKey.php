<?php

declare(strict_types=1);

namespace SensitizedEventStore\Dbal\SensitiveDataManager\Domain\Aggregate;

use DateTimeImmutable;
use Ramsey\Uuid\UuidInterface;

class AggregateKey
{
    private ?DateTimeImmutable  $cancellationDate = null;
    private UuidInterface $aggregateId;
    private string $aggregateKey;

    private function __construct(
        UuidInterface $aggregateId,
        string $aggregateKey // Aggregate key encrypted with the AGGREGATE_MASTER_KEY
    ) {
        $this->aggregateId = $aggregateId;
        $this->aggregateKey = $aggregateKey;
    }

    public static function create(UuidInterface $aggregateId, string $aggregateKey): self
    {
        return new self($aggregateId, $aggregateKey);
    }

    public function exists(): bool
    {
        return is_null($this->cancellationDate);
    }

    public function delete(): void
    {
        $this->aggregateKey = '';
        $this->cancellationDate = new DateTimeImmutable();
    }

    public function cancellationDate(): ?DateTimeImmutable
    {
        return $this->cancellationDate;
    }

    public function aggregateId(): UuidInterface
    {
        return $this->aggregateId;
    }

    public function __toString()
    {
        return $this->aggregateKey;
    }
}
