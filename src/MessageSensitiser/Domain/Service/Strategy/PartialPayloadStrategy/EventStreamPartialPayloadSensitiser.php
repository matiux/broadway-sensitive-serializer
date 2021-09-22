<?php

declare(strict_types=1);

namespace SensitizedEventStore\Dbal\MessageSensitiser\Domain\Service\Strategy\PartialPayloadStrategy;

use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessage;
use SensitizedEventStore\Dbal\MessageSensitiser\Domain\Service\Strategy\EventStreamSensitiserStrategy;
use SensitizedEventStore\Dbal\SensitiveDataManager\Domain\Exception\AggregateKeyException;

/**
 * @template P of mixed
 */
class EventStreamPartialPayloadSensitiser implements EventStreamSensitiserStrategy
{
    private PartialPayloadSensitiserRegistry $partialPayloadSensitiserRegistry;

    public function __construct(PartialPayloadSensitiserRegistry $partialPayloadSensitiserRegistry)
    {
        $this->partialPayloadSensitiserRegistry = $partialPayloadSensitiserRegistry;
    }

    /**
     * @param DomainEventStream $eventStream
     *
     * @throws AggregateKeyException
     *
     * @return DomainEventStream
     */
    public function sensitise(DomainEventStream $eventStream): DomainEventStream
    {
        $events = [];

        /** @var DomainMessage $event */
        foreach ($eventStream as $event) {
            $payload = $this->getEventBody($event);
            /** @var PartialPayloadSensitiser $sensitiser */
            if ($sensitiser = $this->partialPayloadSensitiserRegistry->resolveItemFor($payload)) {
                $event = $sensitiser->sensitise($event);
            }
            $events[] = $event;
        }

        return new DomainEventStream($events);
    }

    /**
     * @param DomainMessage $event
     *
     * @return P
     */
    private function getEventBody(DomainMessage $event)
    {
        /** @var P $payload */
        $payload = $event->getPayload();

        return $payload;
    }

    public function desensitise(DomainEventStream $eventStream): DomainEventStream
    {
        // TODO
        return $eventStream;
    }
}
