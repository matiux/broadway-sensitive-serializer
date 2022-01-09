<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\Example\Shared\Domain\Event;

use Broadway\Serializer\Serializable;
use Matiux\Broadway\SensitiveSerializer\Example\Shared\Domain\ValueObject\DateTimeRFC;

/**
 * @template I of BasicEntityId
 */
abstract class BasicEvent implements Serializable, DomainEvent
{
    public const AGGREGATE_ID_KEY = 'id';

    /**
     * @var I
     */
    protected $aggregateId;
    protected DateTimeRFC $occurredAt;

    /**
     * @param I           $aggregateId
     * @param DateTimeRFC $occurredAt
     */
    public function __construct($aggregateId, DateTimeRFC $occurredAt)
    {
        $this->aggregateId = $aggregateId;
        $this->occurredAt = $occurredAt;
    }

    protected function basicSerialize(): array
    {
        return [
            self::AGGREGATE_ID_KEY => (string) $this->aggregateId,
            'occurred_at' => (string) $this->occurredAt,
        ];
    }

    public function occurredAt(): DateTimeRFC
    {
        return $this->occurredAt;
    }

    protected static function createOccurredAt(string $occurredAt): DateTimeRFC
    {
        return DateTimeRFC::createFrom($occurredAt);
    }
}
