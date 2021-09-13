<?php

declare(strict_types=1);

namespace SensitizedEventStore\Dbal\MessageSensitiser\Domain\Aggregate;

use Broadway\Domain\DomainEventStream;
use Broadway\EventStore\EventStore;
use Broadway\EventStore\EventVisitor;
use Broadway\EventStore\Management\Criteria;
use Broadway\EventStore\Management\EventStoreManagement;
use SensitizedEventStore\Dbal\MessageSensitiser\Domain\Service\Strategy\EventStreamSensitiserStrategy;

class SensitiserEventStoreDecorator implements EventStore, EventStoreManagement
{
    private EventStore $eventStore;
    private EventStreamSensitiserStrategy $eventStreamSensitiser;

    public function __construct(
        EventStore $eventStore,
        EventStreamSensitiserStrategy $eventStreamSensitiser
    ) {
        $this->eventStore = $eventStore;
        $this->eventStreamSensitiser = $eventStreamSensitiser;
    }

    public function load($id): DomainEventStream
    {
        $domainEventStream = $this->eventStore->load($id);

        return $this->eventStreamSensitiser->desensitise($domainEventStream);
    }

    public function loadFromPlayhead($id, int $playhead): DomainEventStream
    {
        return $this->eventStore->loadFromPlayhead($id, $playhead);
    }

    public function append($id, DomainEventStream $eventStream): void
    {
        $eventStream = $this->eventStreamSensitiser->sensitise($eventStream);

        $this->eventStore->append($id, $eventStream);
    }

    /**
     * @psalm-suppress UndefinedInterfaceMethod
     *
     * @param Criteria     $criteria
     * @param EventVisitor $eventVisitor
     */
    public function visitEvents(Criteria $criteria, EventVisitor $eventVisitor): void
    {
        $this->eventStore->visitEvents($criteria, $eventVisitor);
    }
}
