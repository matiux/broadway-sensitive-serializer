<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Aggregate;

use Broadway\Domain\DateTime;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @psalm-type SerializedAKey = array{aggregate_uuid: string, encrypted_key: string, cancellation_date: ?string}
 */
final class AggregateKey
{
    private ?\DateTimeImmutable $cancellationDate = null;
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
        $this->cancellationDate = new \DateTimeImmutable();
    }

    public function cancellationDate(): ?\DateTimeImmutable
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

    /**
     * @return SerializedAKey
     */
    public function serialize(): array
    {
        $cancellationDate = is_null($this->cancellationDate) ? null :
            $this->cancellationDate->format(DateTime::FORMAT_STRING);

        return [
            'aggregate_uuid' => (string) $this->aggregateId,
            'encrypted_key' => $this->aggregateKey,
            'cancellation_date' => $cancellationDate,
        ];
    }

    /**
     * @param SerializedAKey $data
     *
     * @throws \Exception
     *
     * @return self
     */
    public static function deserialize(array $data): self
    {
        $aggregateKey = new self(
            Uuid::fromString($data['aggregate_uuid']),
            $data['encrypted_key'],
        );

        $aggregateKey->cancellationDate = is_null($data['cancellation_date'])
            ? null
            : new \DateTimeImmutable($data['cancellation_date']);

        return $aggregateKey;
    }
}
