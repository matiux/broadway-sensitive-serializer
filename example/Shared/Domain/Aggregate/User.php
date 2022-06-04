<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\Example\Shared\Domain\Aggregate;

use Broadway\EventSourcing\EventSourcedAggregateRoot;
use Matiux\Broadway\SensitiveSerializer\Example\Shared\Domain\Event\UserRegistered;
use Matiux\Broadway\SensitiveSerializer\Example\Shared\Domain\ValueObject\DateTimeRFC;

class User extends EventSourcedAggregateRoot
{
    private UserId $userId;
    private string $name;
    private string $surname;
    private Email $email;
    private string $address;

    public static function create(UserId $userId, string $name, string $surname, Email $email, DateTimeRFC $registrationDate): self
    {
        $user = new self();
        $user->apply(new UserRegistered($userId, $name, $surname, $email, $registrationDate));

        return $user;
    }

    protected function applyUserRegistered(UserRegistered $event): void
    {
        $this->userId = $event->aggregateId();
        $this->name = $event->name();
        $this->surname = $event->surname();
        $this->email = $event->email();
    }

    public function getAggregateRootId(): string
    {
        return (string) $this->userId;
    }

    public function email(): Email
    {
        return $this->email;
    }
}
