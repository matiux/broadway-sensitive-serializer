<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\Example\Shared\Infrastructure\Domain\Broadway;

use Broadway\EventHandling\EventBus;
use Broadway\EventSourcing\AggregateFactory\PublicConstructorAggregateFactory;
use Broadway\EventSourcing\EventSourcingRepository;
use Broadway\EventStore\EventStore;
use Matiux\Broadway\SensitiveSerializer\Example\Shared\Domain\Aggregate\User;
use Matiux\Broadway\SensitiveSerializer\Example\Shared\Domain\Aggregate\UserId;
use Matiux\Broadway\SensitiveSerializer\Example\Shared\Domain\Aggregate\Users;

class BroadwayUsers extends EventSourcingRepository implements Users
{
    public function __construct(EventStore $eventStore, EventBus $eventBus)
    {
        parent::__construct(
            $eventStore,
            $eventBus,
            User::class,
            new PublicConstructorAggregateFactory()
        );
    }

    public function byId(UserId $id): User
    {
        // TODO: Implement byId() method.
    }

    public function add(User $user): void
    {
        parent::save($user);
    }

    public function update(User $user): void
    {
        // TODO: Implement update() method.
    }
}
