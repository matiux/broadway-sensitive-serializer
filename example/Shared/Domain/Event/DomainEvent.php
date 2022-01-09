<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\Example\Shared\Domain\Event;

use DateTimeImmutable;

/**
 * Take a look at the matiux/ddd-starter-pack:v3 library for more info
 */
interface DomainEvent
{
    public function occurredAt(): DateTimeImmutable;
}
