<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\Example\Shared\Domain\Event;

use Broadway\Serializer\Serializable;
use Exception;
use Matiux\Broadway\SensitiveSerializer\Example\Shared\Domain\Aggregate\Email;
use Matiux\Broadway\SensitiveSerializer\Example\Shared\Domain\Aggregate\UserId;
use Matiux\Broadway\SensitiveSerializer\Example\Shared\Domain\ValueObject\DateTimeRFC;
use Matiux\Broadway\SensitiveSerializer\Shared\Tools\Assert;

class UserCreated implements Serializable
{
    private UserId $id;
    private string $name;
    private string $surname;
    private Email $email;
    private UserInfo $userInfo;
    private DateTimeRFC $occurredAt;

    public function __construct(
        UserId $id,
        string $name,
        string $surname,
        Email $email,
        UserInfo $userInfo,
        DateTimeRFC $occurredAt
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->surname = $surname;
        $this->email = $email;
        $this->userInfo = $userInfo;
        $this->occurredAt = $occurredAt;
    }

    /**
     * @psalm-suppress MixedArgumentTypeCoercion
     *
     * @param array $data
     *
     * @throws Exception
     *
     * @return self
     */
    public static function deserialize(array $data): self
    {
        Assert::isArray($data['user_info']);

        return new self(
            UserId::createFrom($data['id']),
            (string) $data['name'],
            (string) $data['surname'],
            Email::createFromString($data['email']),
            UserInfo::create(
                $data['user_info']['age'],
                $data['user_info']['height'],
                (array) $data['user_info']['characteristics'],
            ),
            DateTimeRFC::createFrom((string) $data['occurred_at']),
        );
    }

    /**
     * @return array{id: string, name: string, surname: string, email: string}
     */
    public function serialize(): array
    {
        return [
            'email' => (string) $this->email,
            'id' => (string) $this->id,
            'name' => $this->name,
            'surname' => $this->surname,
            'user_info' => [
                'age' => $this->userInfo->age(),
                'height' => $this->userInfo->height(),
                'characteristics' => $this->userInfo->characteristics(),
            ],
            'occurred_at' => (string) $this->occurredAt,
        ];
    }

    public function aggregateId(): UserId
    {
        return $this->id;
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

    public function userInfo(): UserInfo
    {
        return $this->userInfo;
    }
}
