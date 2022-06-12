<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\Example\Shared\Domain\Aggregate;

use Broadway\EventSourcing\EventSourcedAggregateRoot;
use Matiux\Broadway\SensitiveSerializer\Example\Shared\Domain\Event\UserCreated;
use Matiux\Broadway\SensitiveSerializer\Example\Shared\Domain\Event\UserInfo;
use Matiux\Broadway\SensitiveSerializer\Example\Shared\Domain\ValueObject\DateTimeRFC;

class User extends EventSourcedAggregateRoot
{
    private UserId $userId;
    private string $name;
    private string $surname;
    private Email $email;
    private UserInfo $userInfo;

    private string $address;

    public static function create(
        UserId $userId,
        string $name,
        string $surname,
        Email $email,
        UserInfo $userInfo,
        DateTimeRFC $registrationDate
    ): self {
        $user = new self();
        $user->apply(new UserCreated($userId, $name, $surname, $email, $userInfo, $registrationDate));

        return $user;
    }

    protected function applyUserCreated(UserCreated $event): void
    {
        $this->userId = $event->aggregateId();
        $this->name = $event->name();
        $this->surname = $event->surname();
        $this->email = $event->email();
        $this->userInfo = $event->userInfo();
    }

    public function getAggregateRootId(): string
    {
        return (string) $this->userId;
    }

    public function email(): Email
    {
        return $this->email;
    }

    public function userInfo(): UserInfo
    {
        return $this->userInfo;
    }

    public function surname(): string
    {
        return $this->surname;
    }
}
