<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\Example\Shared\Domain\Event;

use Matiux\Broadway\SensitiveSerializer\Example\Shared\Domain\Aggregate\Email;
use Matiux\Broadway\SensitiveSerializer\Example\Shared\Domain\Aggregate\UserId;
use Matiux\Broadway\SensitiveSerializer\Example\Shared\Domain\ValueObject\DateTimeRFC;

/**
 * @extends BasicEvent<UserId>
 */
class UserRegistered extends BasicEvent
{
    private string $name;
    private string $surname;
    private Email $email;

    public function __construct(
        UserId $userId,
        string $name,
        string $surname,
        Email $email,
        DateTimeRFC $occurredAt
    ) {
        parent::__construct($userId, $occurredAt);
        $this->name = $name;
        $this->surname = $surname;
        $this->email = $email;
    }

    public function serialize(): array
    {
        $serialized = [
            'name' => $this->name,
            'surname' => $this->surname,
            'email' => (string) $this->email,
        ];

        return $this->basicSerialize() + $serialized;
    }

    public static function deserialize(array $data): UserRegistered
    {
        return new self(
            UserId::createFrom((string) $data[self::AGGREGATE_ID_KEY]),
            (string) $data['name'],
            (string) $data['surname'],
            Email::createFromString((string) $data['email']),
            self::createOccurredAt((string) $data['occurred_at'])
        );
    }

    public function aggregateId(): UserId
    {
        return $this->aggregateId;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function surname(): string
    {
        return $this->surname;
    }

    public function email(): Email
    {
        return $this->email;
    }
}
