<?php

declare(strict_types=1);

namespace SensitizedEventStore\Dbal\MessageSensitiser\Domain\Service\Strategy;

use Broadway\Domain\DomainEventStream;

interface EventStreamSensitiserStrategy
{
    public function sensitise(DomainEventStream $eventStream): DomainEventStream;

    public function desensitise(DomainEventStream $eventStream): DomainEventStream;
}
