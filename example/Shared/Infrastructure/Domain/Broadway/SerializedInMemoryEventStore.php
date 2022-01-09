<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\Example\Shared\Infrastructure\Domain\Broadway;

use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessage;
use Broadway\EventStore\EventStore;
use Broadway\EventStore\EventVisitor;
use Broadway\EventStore\Management\Criteria;
use Broadway\EventStore\Management\EventStoreManagement;
use Broadway\Serializer\Serializer;

/**
 * This is only a workaround since in a real implementation like broadway/event-store-dbal
 * object serialization is needing during persistence.
 */
class SerializedInMemoryEventStore implements EventStore, EventStoreManagement
{
    private EventStore $eventStore;
    private Serializer $payloadSerializer;

    public function __construct(EventStore $eventStore, Serializer $payloadSerializer)
    {
        $this->eventStore = $eventStore;
        $this->payloadSerializer = $payloadSerializer;
    }

    public function load($id): DomainEventStream
    {
        $eventStream = $this->eventStore->load($id);

        /** @var DomainMessage $event */
        foreach ($eventStream as $event) {
            $payload = [
                'class' => get_class($event->getPayload()),
                'payload' => $event->getPayload()->serialize(),
            ];

            $desensitizedEvent = $this->payloadSerializer->deserialize($payload);

            $domainMessages[] = DomainMessage::recordNow(
                $event->getId(),
                $event->getPlayhead(),
                $event->getMetadata(),
                $desensitizedEvent
            );
        }

        return new DomainEventStream($domainMessages);
    }

    public function loadFromPlayhead($id, int $playhead): DomainEventStream
    {
        // TODO: Implement loadFromPlayhead() method.
    }

    public function append($id, DomainEventStream $eventStream): void
    {
        $domainMessages = [];

        /** @var DomainMessage $event */
        foreach ($eventStream as $event) {
            $payload = $this->payloadSerializer->serialize($event->getPayload())['payload'];

            $sensitizedEvent = get_class($event->getPayload())::deserialize($payload);

            $domainMessages[] = DomainMessage::recordNow(
                $event->getId(),
                $event->getPlayhead(),
                $event->getMetadata(),
                $sensitizedEvent
            );
        }

        $this->eventStore->append($id, new DomainEventStream($domainMessages));
    }

    public function visitEvents(Criteria $criteria, EventVisitor $eventVisitor): void
    {
        // TODO: Implement visitEvents() method.
    }
}
